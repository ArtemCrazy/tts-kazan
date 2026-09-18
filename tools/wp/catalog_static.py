"""Каталог и матрица подбора из статической версии сайта.

Позиции каталога и матрица квиза описаны в site/assets/js/catalog.js и quiz.js —
это тот самый единственный источник, о котором говорит комментарий в самих
скриптах. Перед переносом в WordPress читаем их отсюда, а не переписываем
руками: иначе тексты разъедутся.

Запуск для проверки:  python tools/wp/catalog_static.py
"""

import io
import json
import os
import re
import sys

ROOT = os.path.dirname(os.path.dirname(os.path.dirname(os.path.abspath(__file__))))
CATALOG_JS = os.path.join(ROOT, 'site', 'assets', 'js', 'catalog.js')
QUIZ_JS = os.path.join(ROOT, 'site', 'assets', 'js', 'quiz.js')

# Направление в каталоге -> термин и страница в WordPress
DIRECTIONS = {
    'zsss': ('smartdrymix', 'Заводы сухих смесей'),
    'beton': ('smartbeton', 'Бетонные заводы'),
    'vpi': ('vpi', 'Заводы ВПИ'),
    'terminal': ('smartstock', 'Цементные терминалы'),
    'pkn': ('pkn', 'Пневмокамерные насосы'),
}

# Назначение в вёрстке -> значение поля «Назначение» в админке
PURPOSE = {
    'drymix': 'dry-mix',
    'ready': 'concrete',
    'vpi': 'vpi',
    'storage': 'cement-storage',
    'transport': 'pneumo',
}

# Подпись статуса -> значение поля «Статус»
STATUS = {
    'Готовая конфигурация': 'ready',
    'Типовая модель': 'typical',
    'Пример конфигурации': 'example',
    'Проектная конфигурация': 'project',
    'Модифицированный': 'modified',
    'Пневмотранспорт': 'transport',
}

# Тип объекта в квизе -> значение поля в матрице рекомендаций
QUIZ_OBJECT = {'zsss': 'dry-mix', 'beton': 'concrete', 'terminal': 'terminal'}


def js_value(text):
    """Литерал JavaScript -> данные Python.

    В скриптах это обычные объекты и массивы, но с одинарными кавычками и
    без кавычек у ключей, поэтому JSON их не читает. Приводим к JSON.
    """
    out = []
    i = 0
    while i < len(text):
        char = text[i]
        if char in '"\'':
            quote = char
            i += 1
            chunk = []
            while i < len(text) and text[i] != quote:
                if text[i] == '\\':
                    chunk.append(text[i:i + 2])
                    i += 2
                    continue
                chunk.append('\\"' if text[i] == '"' else text[i])
                i += 1
            i += 1
            out.append('"' + ''.join(chunk) + '"')
            continue
        out.append(char)
        i += 1
    body = ''.join(out)
    body = re.sub(r'([{,]\s*)([A-Za-z_$][\w$]*)(\s*:)', r'\1"\2"\3', body)   # ключи в кавычки
    body = re.sub(r',(\s*[}\]])', r'\1', body)                               # висящие запятые
    return json.loads(body)


def _chunk(path, start, end):
    text = io.open(path, encoding='utf-8').read()
    begin = text.index(start) + len(start)
    depth = 0
    for pos in range(begin, len(text)):
        if text[pos] in '[{':
            depth += 1
        elif text[pos] in ']}':
            depth -= 1
            if depth == 0:
                return text[begin - len(end):pos + 1].lstrip(end).strip() or text[begin:pos + 1]
    raise ValueError(f'не нашёл конец {start!r} в {path}')


def catalog():
    """23 позиции каталога в том виде, в котором они лежат в вёрстке."""
    text = io.open(CATALOG_JS, encoding='utf-8').read()
    start = text.index('var CATALOG = [') + len('var CATALOG = ')
    depth, end = 0, None
    for pos in range(start, len(text)):
        if text[pos] == '[':
            depth += 1
        elif text[pos] == ']':
            depth -= 1
            if depth == 0:
                end = pos + 1
                break
    items = js_value(text[start:end])
    for item in items:
        item['purpose_field'] = PURPOSE[item['purpose']]
        # Неизвестный статус — ошибка переноса: молча подставлять другой нельзя,
        # так уже потерялись «Проектная конфигурация» и «Пневмотранспорт».
        if item['status'] not in STATUS:
            raise ValueError('неизвестный статус «%s» у %s' % (item['status'], item['name']))
        item['status_field'] = STATUS[item['status']]
        item['direction'] = DIRECTIONS[item['cat']][0]
    return items


def quiz_matrix():
    """Матрица рекомендаций: тип объекта, ответ, основная и альтернативная модель."""
    text = io.open(QUIZ_JS, encoding='utf-8').read()
    start = text.index('var DIRECTIONS = {') + len('var DIRECTIONS = ')
    depth, end = 0, None
    for pos in range(start, len(text)):
        if text[pos] == '{':
            depth += 1
        elif text[pos] == '}':
            depth -= 1
            if depth == 0:
                end = pos + 1
                break
    data = js_value(text[start:end])

    rows = []
    for key, block in data.items():
        if key not in QUIZ_OBJECT:
            continue
        for value, label in block['capacities']:
            cards = block.get('cards', {}).get(value, [])
            rows.append({
                'object': QUIZ_OBJECT[key],
                'answer': label,
                'recommendation': block['recommend'][value]['text'],
                'primary': cards[0]['name'] if cards else block['recommend'][value]['title'],
                'alt': cards[1]['name'] if len(cards) > 1 else '',
                # В статике у карточки квиза свой короткий текст, и он зависит от
                # строки: одна модель описана по-разному как основная и как альтернатива.
                'primary_text': cards[0]['text'] if cards else '',
                'primary_capacity': cards[0]['capacity'] if cards else '',
                'alt_text': cards[1]['text'] if len(cards) > 1 else '',
                'alt_capacity': cards[1]['capacity'] if len(cards) > 1 else '',
            })
    return rows


if __name__ == '__main__':
    sys.stdout.reconfigure(encoding='utf-8')
    items = catalog()
    print('позиций каталога:', len(items))
    by_dir = {}
    for item in items:
        by_dir[item['direction']] = by_dir.get(item['direction'], 0) + 1
    print('по направлениям:', by_dir)
    print('с картинкой:', sum(1 for i in items if i.get('img')))
    print('пример:', json.dumps(items[0], ensure_ascii=False, indent=1))
    rows = quiz_matrix()
    print('строк матрицы подбора:', len(rows))
    for row in rows:
        print(f"  {row['object']:9} {row['answer']:16} -> {row['primary']}"
              + (f" / {row['alt']}" if row['alt'] else ''))
