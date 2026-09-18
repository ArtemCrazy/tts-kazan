"""Включение плагина Rank Math без регистрации аккаунта.

Rank Math ничего не выводит на страницы (заголовки, описания, Open Graph,
карту сайта), пока его не подключили к аккаунту или не нажали «Пропустить»
в мастере настройки. Аккаунт для работы не нужен — ставим тот же флаг,
что ставит кнопка «Пропустить».

Запуск:  python tools/wp/setup-seo-plugin.py
"""

import os
import secrets
import sys

sys.path.insert(0, os.path.dirname(os.path.abspath(__file__)))
from common import Remote, fetch, say, SITE_SUBDIR, SITE_URL  # noqa: E402

RUNNER = r'''<?php
header('Content-Type: text/plain; charset=utf-8');
if (($_GET['token'] ?? '') !== '%(token)s') { http_response_code(403); exit('нет токена'); }
require __DIR__ . '/wp-load.php';
update_option('rank_math_registration_skip', true);
echo 'регистрация пропущена: ', get_option('rank_math_registration_skip') ? 'да' : 'нет', "\n";
'''


def main():
    token = secrets.token_hex(8)
    runner = f'rm-{token}.php'
    r = Remote()
    r.put_text(RUNNER % {'token': token}, SITE_SUBDIR, runner)
    say(fetch(f'{SITE_URL}/{runner}?token={token}').strip())
    r.remove(SITE_SUBDIR, runner)
    r.close()


if __name__ == '__main__':
    main()
