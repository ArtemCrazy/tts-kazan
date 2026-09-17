"""Контент из статической вёрстки site/4 в виде обычных структур Python.

Источник правды по текстам — сама вёрстка, а не копипаста: так в WordPress
уезжает то же самое, что клиент уже видел и согласовал на статике.

Разбор через html.parser: разметка вложенная (article > dl > dt/dd), регулярками
её надёжно не взять.

Запуск для проверки глазами:  python tools/wp/content_static.py
"""

import os
import re
import sys
from html.parser import HTMLParser

# Пути считаем от файла модуля: tools/wp/ -> корень проекта -> site/4
ROOT = os.path.dirname(os.path.dirname(os.path.dirname(os.path.abspath(__file__))))
SITE = os.path.join(ROOT, 'site', '4')
INDEX_HTML = os.path.join(SITE, 'index.html')
SERVICE_HTML = os.path.join(SITE, 'service', 'index.html')

# Теги без закрывающей пары — их нельзя класть в стек парсера
VOID_TAGS = {
    'area', 'base', 'br', 'col', 'embed', 'hr', 'img', 'input',
    'link', 'meta', 'param', 'source', 'track', 'wbr',
}


# --- разбор HTML в простое дерево -------------------------------------------

class Node:
    """Элемент дерева: тег, атрибуты и дети (узлы или строки текста)."""

    __slots__ = ('tag', 'attrs', 'children')

    def __init__(self, tag, attrs=None):
        self.tag = tag
        self.attrs = attrs or {}
        self.children = []

    @property
    def classes(self):
        return self.attrs.get('class', '').split()

    def get(self, name, default=None):
        return self.attrs.get(name, default)

    def __repr__(self):
        cls = self.attrs.get('class', '')
        return '<%s%s>' % (self.tag, ' .' + cls if cls else '')


class _DomParser(HTMLParser):
    """Строит дерево, терпимо относясь к незакрытым тегам."""

    def __init__(self):
        super().__init__(convert_charrefs=True)
        self.root = Node('#document')
        self._stack = [self.root]

    def handle_starttag(self, tag, attrs):
        node = Node(tag, dict(attrs))
        self._stack[-1].children.append(node)
        if tag not in VOID_TAGS:
            self._stack.append(node)

    def handle_startendtag(self, tag, attrs):
        self._stack[-1].children.append(Node(tag, dict(attrs)))

    def handle_endtag(self, tag):
        if tag in VOID_TAGS:
            return
        # Закрываем ближайший одноимённый тег; лишние закрывашки игнорируем
        for i in range(len(self._stack) - 1, 0, -1):
            if self._stack[i].tag == tag:
                del self._stack[i:]
                return

    def handle_data(self, data):
        self._stack[-1].children.append(data)


def parse_file(path):
    parser = _DomParser()
    with open(path, encoding='utf-8') as f:
        parser.feed(f.read())
    parser.close()
    return parser.root


# --- поиск по дереву --------------------------------------------------------

def walk(node):
    """Все элементы поддерева, включая сам узел."""
    yield node
    for child in node.children:
        if isinstance(child, Node):
            yield from walk(child)


def _matches(node, tag, cls, attrs):
    if tag and node.tag != tag:
        return False
    if cls and cls not in node.classes:
        return False
    for name, value in (attrs or {}).items():
        if node.get(name) != value:
            return False
    return True


def find_all(node, tag=None, cls=None, **attrs):
    return [n for n in walk(node) if _matches(n, tag, cls, attrs)]


def find(node, tag=None, cls=None, **attrs):
    for n in walk(node):
        if _matches(n, tag, cls, attrs):
            return n
    return None


# --- чистка текста ----------------------------------------------------------

def clean(value):
    """Убирает следы типографа, чтобы в базу не уехали невидимые символы.

    NBSP -> обычный пробел, мягкий перенос -> удалить,
    неразрывный дефис -> обычный дефис.
    """
    if not value:
        return ''
    value = value.replace(' ', ' ').replace(' ', ' ')
    value = value.replace('­', '')
    value = value.replace('‑', '-')
    return re.sub(r'\s+', ' ', value).strip()


def text_of(node):
    """Плоский текст элемента; <br> считаем пробелом."""
    if node is None:
        return ''
    parts = []
    for child in node.children:
        if isinstance(child, str):
            parts.append(child)
        elif child.tag == 'br':
            parts.append(' ')
        elif child.tag in ('script', 'style'):
            continue
        else:
            parts.append(text_of(child))
    return clean(''.join(parts))


def _text_by_class(node, cls, tag=None):
    return text_of(find(node, tag=tag, cls=cls))


# --- контент ----------------------------------------------------------------

def projects():
    """Три проекта из секции <section class="projects" id="projects">."""
    root = parse_file(INDEX_HTML)
    section = find(root, tag='section', cls='projects', id='projects')
    if section is None:
        return []

    items = []
    for article in find_all(section, tag='article', cls='project'):
        photo = find(article, cls='project__photo')

        # dt и dd — сиблинги внутри <dl>, поэтому идём по детям по порядку
        # и запоминаем последний заголовок
        brief = {}
        term = None
        dl = find(article, tag='dl', cls='project__brief')
        for child in (dl.children if dl else []):
            if not isinstance(child, Node):
                continue
            if child.tag == 'dt':
                term = text_of(child).rstrip(':').lower()
            elif child.tag == 'dd' and term:
                brief[term] = text_of(child)
                term = None

        figures = []
        box = find(article, cls='project__figures')
        for cell in (box.children if box else []):
            if not isinstance(cell, Node):
                continue
            value = _text_by_class(cell, 'project__value')
            label = _text_by_class(cell, 'project__label')
            if value or label:
                figures.append((value, label))

        items.append({
            'photo': (photo.get('data-photo', '') if photo else ''),
            'alt': clean(photo.get('aria-label', '') if photo else ''),
            'city': _text_by_class(article, 'project__meta'),
            'title': _text_by_class(article, 'project__title'),
            'task': brief.get('задача', ''),
            'solution': brief.get('решение', ''),
            'figures': figures,
        })
    return items


def faq():
    """Вопросы и ответы из секции FAQ на главной."""
    root = parse_file(INDEX_HTML)
    section = find(root, tag='section', id='faq') or find(root, tag='section', cls='faq')
    if section is None:
        return []

    items = []
    for details in find_all(section, tag='details', cls='faq__item'):
        question = text_of(find(details, tag='summary', cls='faq__question'))
        panel = find(details, cls='faq__panel')
        paragraphs = [text_of(p) for p in find_all(panel or details, tag='p')]
        paragraphs = [p for p in paragraphs if p]
        if not paragraphs and panel is not None:
            # ответ без обёртки <p> — берём текст панели целиком
            whole = text_of(panel)
            paragraphs = [whole] if whole else []
        items.append({'question': question, 'answer': paragraphs})
    return items


def service_items():
    """Услуги инженерного сервиса: секция #scope на site/4/service/index.html.

    В текущей вёрстке карточка услуги — это только заголовок и описание:
    этапы (#process) и прочее лежат отдельными секциями страницы, не внутри
    услуг. Поэтому steps/benefits почти всегда пустые, но ключи отдаём всегда,
    чтобы вызывающий код не проверял их наличие.
    """
    root = parse_file(SERVICE_HTML)
    section = find(root, tag='section', id='scope')
    if section is None:
        return []
    cards = find(section, cls='cards') or section

    items = []
    for card in find_all(cards, tag='article', cls='card'):
        # этапы внутри услуги, если когда-нибудь появятся в разметке
        steps = []
        for step in find_all(card, cls='step'):
            title = _text_by_class(step, 'step__title') or text_of(find(step, tag='h4'))
            body = _text_by_class(step, 'step__text') or text_of(find(step, tag='p'))
            if title or body:
                steps.append((title, body))

        benefits = [t for t in (text_of(li) for li in find_all(card, tag='li')) if t]

        items.append({
            'title': _text_by_class(card, 'card__title'),
            'text': _text_by_class(card, 'card__text'),
            'steps': steps,
            'benefits': benefits,
        })
    return items


# --- проверка глазами -------------------------------------------------------

if __name__ == '__main__':
    sys.stdout.reconfigure(encoding='utf-8')

    projects_data = projects()
    faq_data = faq()
    service_data = service_items()

    print('Проектов: %d, вопросов: %d, услуг: %d'
          % (len(projects_data), len(faq_data), len(service_data)))

    if projects_data:
        first = projects_data[0]
        print('\n--- проект 1 ---')
        print('photo:', first['photo'])
        print('alt:', first['alt'])
        print('city:', first['city'])
        print('title:', first['title'])
        print('task:', first['task'])
        print('solution:', first['solution'])
        for value, label in first['figures']:
            print('  figure:', value, '|', label)

    if faq_data:
        first = faq_data[0]
        print('\n--- вопрос 1 ---')
        print('question:', first['question'])
        for para in first['answer']:
            print('  answer:', para)

    if service_data:
        first = service_data[0]
        print('\n--- услуга 1 ---')
        print('title:', first['title'])
        print('text:', first['text'])
        print('steps:', first['steps'])
        print('benefits:', first['benefits'])

    print('\nВсе заголовки:')
    for item in projects_data:
        print('  проект:', item['title'])
    for item in faq_data:
        print('  вопрос:', item['question'])
    for item in service_data:
        print('  услуга:', item['title'])

    # Невидимых символов типографа в выдаче быть не должно
    bad = []

    def _check(label, value):
        if isinstance(value, str):
            for ch, name in ((' ', 'NBSP'), ('­', 'SHY'), ('‑', 'NB-HYPHEN')):
                if ch in value:
                    bad.append('%s: %s' % (label, name))
        elif isinstance(value, dict):
            for key, sub in value.items():
                _check('%s.%s' % (label, key), sub)
        elif isinstance(value, (list, tuple)):
            for i, sub in enumerate(value):
                _check('%s[%d]' % (label, i), sub)

    _check('projects', projects_data)
    _check('faq', faq_data)
    _check('service_items', service_data)
    print('\nПроверка символов:', 'чисто' if not bad else 'НАЙДЕНО ' + '; '.join(bad))
