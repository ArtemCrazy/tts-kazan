"""Проверка ссылок WordPress-версии: битых быть не должно (п. 20 ТЗ).

Обходит публичные страницы, собирает ссылки внутри сайта и проверяет, что
каждая отвечает. Заодно смотрит, что нет ссылок на статическую версию /4/
и на localhost — такие остатки после переноса встречаются чаще всего.

Запуск:  python tools/wp/check-links.py
"""

import os
import re
import subprocess
import sys
from urllib.parse import urljoin, urlparse

sys.path.insert(0, os.path.dirname(os.path.abspath(__file__)))
from common import fetch, say, SITE_URL  # noqa: E402

PAGES = ['/', '/catalog/', '/catalog/smartdrymix/', '/catalog/smartbeton/', '/catalog/vpi/',
         '/catalog/smartstock/', '/catalog/pkn/', '/service/', '/parts/',
         '/privacy-policy/', '/personal-data/', '/cookie/']


def status(url):
    done = subprocess.run(
        ['curl', '-s', '-o', os.devnull, '-w', '%{http_code}', '-b', 'beget=begetok', url],
        capture_output=True, timeout=120
    )
    return done.stdout.decode().strip()


def main():
    found = {}
    suspicious = []

    for path in PAGES:
        page = fetch(SITE_URL + path)
        for href in re.findall(r'href="([^"#]+)', page):
            url = urljoin(SITE_URL + path, href)
            if urlparse(url).netloc != urlparse(SITE_URL).netloc:
                continue
            if '/tts-kazan.ru/4/' in url or 'localhost' in url:
                suspicious.append((path, url))
            found.setdefault(url, set()).add(path)

    say(f'страниц проверено: {len(PAGES)}, уникальных ссылок внутри сайта: {len(found)}')

    broken = []
    for url in sorted(found):
        code = status(url)
        if code not in ('200', '301', '302'):
            broken.append((url, code, sorted(found[url])))

    if broken:
        say('битые ссылки:')
        for url, code, pages in broken:
            say(f'  {code}  {url}  (со страниц: {", ".join(pages)})')
    else:
        say('битых ссылок нет')

    if suspicious:
        say('ссылки на старую версию или localhost:')
        for path, url in suspicious:
            say(f'  {path} -> {url}')
    else:
        say('ссылок на статическую версию и localhost нет')


if __name__ == '__main__':
    main()
