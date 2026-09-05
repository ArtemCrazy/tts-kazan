"""Проверка навигации: каждая рабочая ссылка должна вести в существующий файл.

Ссылки с data-stage пропускаются — это осознанные заглушки на ещё не собранные
разделы (юридические страницы и внешний квиз Marquiz).

Запуск:  python tools/check-links.py
"""

import io
import os
import re
import sys

sys.stdout.reconfigure(encoding='utf-8')

ROOT = os.path.join(os.path.dirname(os.path.dirname(os.path.abspath(__file__))), 'site', '4')

pages = []
for dirpath, _dirs, files in os.walk(ROOT):
    if 'index.html' in files:
        pages.append(os.path.join(dirpath, 'index.html'))
pages.sort()

problems = []
staged = 0
checked = 0

for page in pages:
    text = io.open(page, encoding='utf-8').read()
    base = os.path.dirname(page)
    for m in re.finditer(r'<a\b([^>]*)>', text):
        attrs = m.group(1)
        href = re.search(r'href="([^"]+)"', attrs)
        if not href:
            continue
        href = href.group(1)
        if href.startswith(('#', 'mailto:', 'tel:', 'http')):
            continue
        if 'data-stage' in attrs:
            staged += 1
            continue
        checked += 1
        path = href.split('#')[0].split('?')[0]
        target = os.path.normpath(os.path.join(base, path))
        if os.path.isdir(target):
            target = os.path.join(target, 'index.html')
        if not os.path.isfile(target):
            problems.append('%s -> %s' % (os.path.relpath(page, ROOT), href))

print('страниц: %d' % len(pages))
for p in pages:
    print('   %s' % os.path.relpath(p, ROOT).replace(os.sep, '/'))
print('\nрабочих ссылок проверено: %d, заглушек пропущено: %d' % (checked, staged))
if problems:
    print('БИТЫЕ ССЫЛКИ (%d):' % len(problems))
    for p in problems:
        print('   %s' % p)
    raise SystemExit(1)
print('битых ссылок нет')
