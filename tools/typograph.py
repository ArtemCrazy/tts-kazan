"""Типограф: не даёт коротким словам болтаться в конце строки.

Предлоги, союзы и частицы склеиваются со следующим словом неразрывным пробелом,
тире — с предыдущим. Браузер после этого сам переносит строки так, что строка
никогда не заканчивается на «и», «от» или «для».

Работает по готовым страницам, а не по исходникам: одинаково обрабатывает и то,
что собрал build-pages.py, и рукописные главную с каталогом. Повторный запуск
ничего не портит — уже склеенное пропускается.

Запуск:  python tools/typograph.py
"""

import io
import os
import re
import sys

sys.stdout.reconfigure(encoding='utf-8')

ROOT = os.path.join(os.path.dirname(os.path.dirname(os.path.abspath(__file__))), 'site', '4')

NBSP = ' '

# Служебные слова, которые нельзя оставлять в конце строки. Список явный:
# по длине отбирать нельзя — «ток», «газ» и «под» одинаково короткие,
# но склеивать нужно только последнее.
SHORT = {
    'а', 'и', 'но', 'да', 'же', 'ли', 'бы', 'не', 'ни', 'то',
    'в', 'во', 'на', 'за', 'к', 'ко', 'о', 'об', 'от', 'по', 'до',
    'из', 'с', 'со', 'у',
}

# Внутрь этих тегов лезть нельзя: там код, а не текст
SKIP_TAGS = ('script', 'style')

WORD = r'[^\s<>&]+'


def typo(text):
    """Склеить служебные слова со следующим, тире — с предыдущим."""
    if not text.strip():
        return text

    # тире не отрывается от предшествующего слова
    text = re.sub(r'(\S)[ ]+(—|–)(?=\s)', r'\1' + NBSP + r'\2', text)

    def glue(m):
        word = m.group(1)
        if word.lower().strip('«"(') not in SHORT:
            return m.group(0)
        return word + NBSP

    return re.sub(r'(?<![^\s>(«"])(' + WORD + r')[ ]+(?=' + WORD + r')', glue, text)


def process(html):
    """Пройти по текстовым узлам, не трогая теги и содержимое script/style."""
    out = []
    pos = 0
    skip_until = None

    for m in re.finditer(r'<[^>]+>', html):
        chunk = html[pos:m.start()]
        tag = m.group(0)
        name = re.match(r'</?\s*([a-zA-Z0-9-]+)', tag)
        name = name.group(1).lower() if name else ''

        out.append(chunk if skip_until else typo(chunk))
        out.append(tag)
        pos = m.end()

        if skip_until:
            if name == skip_until and tag.startswith('</'):
                skip_until = None
        elif name in SKIP_TAGS and not tag.startswith('</'):
            skip_until = name

    out.append(html[pos:] if skip_until else typo(html[pos:]))
    return ''.join(out)


def main():
    total = 0
    for dirpath, _dirs, files in os.walk(ROOT):
        if 'index.html' not in files:
            continue
        path = os.path.join(dirpath, 'index.html')
        before = io.open(path, encoding='utf-8').read()
        after = process(before)
        added = after.count(NBSP) - before.count(NBSP)
        if after != before:
            io.open(path, 'w', encoding='utf-8').write(after)
        total += added
        print('   %-34s склеек добавлено: %d' % (
            os.path.relpath(path, ROOT).replace(os.sep, '/'), added))
    print('всего новых склеек: %d' % total)


if __name__ == '__main__':
    main()
