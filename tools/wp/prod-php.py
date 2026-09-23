"""Выполнить кусок PHP внутри WordPress на боевом сайте (hoster.kz).

То же, что run-php.py для тестового сервера, только файл заливается по FTP
на хостинг клиента и вызывается по https://tts-engineering.kz. После работы
файл удаляется.

Доступы — creds/card-199-hosting.env.

Запуск:  python tools/wp/prod-php.py путь/к/файлу.php
"""

import io
import os
import secrets
import sys
import urllib.error
import urllib.request

sys.path.insert(0, os.path.dirname(os.path.abspath(__file__)))
from common import read_env, say  # noqa: E402

HEAD = """<?php
set_time_limit(0);
header('Content-Type: text/plain; charset=utf-8');
if (($_GET['token'] ?? '') !== '%s') { http_response_code(403); exit('нет токена'); }
require __DIR__ . '/wp-load.php';
"""


def run(code):
    import ftplib

    env = read_env('card-199-hosting.env')
    token = secrets.token_hex(8)
    name = 'run-%s.php' % token
    ftp = ftplib.FTP()
    ftp.connect(env['FTP_HOST'], 21, timeout=60)
    ftp.login(env['FTP_USER'], env['FTP_PASSWORD'])
    ftp.storbinary('STOR ' + name, io.BytesIO((HEAD % token + code).encode('utf-8')))
    url = 'https://%s/%s?token=%s' % (env['SITE_DOMAIN'], name, token)
    try:
        with urllib.request.urlopen(url, timeout=900) as response:
            return response.read().decode('utf-8', 'replace')
    except urllib.error.HTTPError as error:
        return 'ошибка %s: %s' % (error.code, error.read().decode('utf-8', 'replace')[:2000])
    finally:
        try:
            ftp.delete(name)
        except Exception:
            pass
        ftp.quit()


def main():
    code = io.open(sys.argv[1], encoding='utf-8').read()
    if code.lstrip().startswith('<?php'):
        code = code.lstrip()[5:]
    say(run(code))


if __name__ == '__main__':
    main()
