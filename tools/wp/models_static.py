"""Комплектации со страниц направлений (site/4/catalog/*/index.html).

Карточки на страницах направлений — это НЕ позиции общего каталога, а
комплектации: своё название («SmartBeton 30 S + QS 1000–1200»), своё
описание, два показателя вместо одного и своя подпись кнопки. Списка
особенностей в карточке нет — он остаётся в модальном окне каталога.

Источник правды по этим текстам — сама вёрстка, поэтому читаем её, а не
переписываем руками (тот же приём, что в content_static.py).

Разбор и чистку текста (NBSP, мягкий перенос, неразрывный дефис) берём из
content_static.py — второй копии парсера не держим.

Запуск для проверки глазами:  python tools/wp/models_static.py
"""

import os
import sys

sys.path.insert(0, os.path.dirname(os.path.abspath(__file__)))
from content_static import (  # noqa: E402
    Node, clean, find, find_all, parse_file, text_of,
)

# Пути считаем от файла модуля: tools/wp/ -> корень проекта -> site/4/catalog
ROOT = os.path.dirname(os.path.dirname(os.path.dirname(os.path.abspath(__file__))))
CATALOG = os.path.join(ROOT, 'site', '4', 'catalog')

# Папка страницы направления совпадает со слагом направления в WordPress
# (см. DIRECTIONS в catalog_static.py), поэтому отдельной таблицы не нужно.
PAGES = ('smartdrymix', 'smartbeton', 'vpi', 'smartstock', 'pkn')


def _text_by_class(node, cls, tag=None):
    return text_of(find(node, tag=tag, cls=cls))


def page_models(slug):
    """Комплектации одной страницы направления в порядке вёрстки."""
    path = os.path.join(CATALOG, slug, 'index.html')
    if not os.path.exists(path):
        return []

    root = parse_file(path)
    grid = find(root, tag='div', cls='models')
    if grid is None:
        return []

    items = []
    for article in find_all(grid, tag='article', cls='model'):
        # Пары «значение / подпись» — прямые дети model__specs, по порядку
        specs = []
        box = find(article, cls='model__specs')
        for cell in (box.children if box else []):
            if not isinstance(cell, Node):
                continue
            value = _text_by_class(cell, 'model__value')
            label = _text_by_class(cell, 'model__label')
            if value or label:
                specs.append((value, label))

        link = find(article, cls='model__link')
        media = find(article, cls='model__media')

        items.append({
            'code': _text_by_class(article, 'model__code'),
            'title': _text_by_class(article, 'model__title'),
            'text': _text_by_class(article, 'model__text'),
            'specs': specs,
            # В кнопке рядом с текстом лежит <svg> стрелки — текста он не даёт
            'cta': text_of(link),
            'tag': _text_by_class(article, 'model__tag'),
            # Пригодится для сверки: рендер и модель для формы заявки
            'img': clean(media.get('data-render', '') if media else ''),
            'value': clean(link.get('data-model', '') if link else ''),
        })
    return items


def models():
    """Все страницы направлений: {слаг направления: [комплектации]}."""
    return {slug: page_models(slug) for slug in PAGES}


def by_code():
    """Плоский указатель для сопоставления с записями «Оборудование».

    Ключ — (слаг направления, код модели): код уникален только внутри
    направления, «60» есть и у бетонного завода, и у линии ВПИ («60 S»).
    """
    return {
        (slug, item['code']): item
        for slug, items in models().items()
        for item in items
        if item['code']
    }


# --- проверка глазами -------------------------------------------------------

if __name__ == '__main__':
    sys.stdout.reconfigure(encoding='utf-8')

    data = models()
    total = sum(len(items) for items in data.values())
    print('Комплектаций всего: %d' % total)
    for slug in PAGES:
        print('  %-12s %d' % (slug, len(data[slug])))

    print('\n--- комплектация 1 (vpi) целиком ---')
    for key, value in (data['vpi'][0] if data['vpi'] else {}).items():
        print('%-7s %s' % (key + ':', value))

    print('\nПодписи первой пары показателей по страницам:')
    for slug in PAGES:
        labels = sorted({item['specs'][0][1] for item in data[slug] if item['specs']})
        print('  %-12s %s' % (slug, ' | '.join(labels)))

    print('\nВсе комплектации:')
    for slug in PAGES:
        for item in data[slug]:
            print('  %-12s %-8s %s' % (slug, item['code'], item['title']))

    # Невидимых символов типографа в выдаче быть не должно
    bad = []

    def _check(label, value):
        if isinstance(value, str):
            for ch, name in (('\u00a0', 'NBSP'), ('\u00ad', 'SHY'), ('\u2011', 'NB-HYPHEN')):
                if ch in value:
                    bad.append('%s: %s' % (label, name))
        elif isinstance(value, dict):
            for key, sub in value.items():
                _check('%s.%s' % (label, key), sub)
        elif isinstance(value, (list, tuple)):
            for i, sub in enumerate(value):
                _check('%s[%d]' % (label, i), sub)

    _check('models', data)
    print('\nПроверка символов:', 'чисто' if not bad else 'НАЙДЕНО ' + '; '.join(bad))
