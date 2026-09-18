"""Выполнить кусок PHP внутри WordPress на тестовом сервере.

Для разовых проверок и правок данных: скрипт заливает файл с токеном,
вызывает его и сразу удаляет. WordPress уже загружен, можно звать
get_field(), wp_update_nav_menu_item() и всё остальное.

Запуск:  python tools/wp/run-php.py путь/к/файлу.php
         (в файле — код без <?php, он добавляется сам)
"""

import io
import os
import secrets
import sys

sys.path.insert(0, os.path.dirname(os.path.abspath(__file__)))
from common import Remote, fetch, say, SITE_SUBDIR, SITE_URL  # noqa: E402

HEAD = r'''<?php
header('Content-Type: text/plain; charset=utf-8');
if (($_GET['token'] ?? '') !== '%s') { http_response_code(403); exit('нет токена'); }
require __DIR__ . '/wp-load.php';
'''


def run(code):
    token = secrets.token_hex(8)
    name = f'run-{token}.php'
    r = Remote()
    r.put_text(HEAD % token + code, SITE_SUBDIR, name)
    try:
        return fetch(f'{SITE_URL}/{name}?token={token}')
    finally:
        try:
            r.remove(SITE_SUBDIR, name)
        except FileNotFoundError:
            pass
        r.close()


def main():
    code = io.open(sys.argv[1], encoding='utf-8').read()
    if code.lstrip().startswith('<?php'):
        code = code.lstrip()[5:]
    say(run(code))


if __name__ == '__main__':
    main()
