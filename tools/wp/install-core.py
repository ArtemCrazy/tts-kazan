"""Ядро WordPress на тестовый сервер: скачать, залить архив, распаковать.

Файлов в ядре больше двух тысяч. Заливать их по одному через SFTP — это
десятки минут, поэтому отправляем архив и распаковываем его на сервере
одноразовым PHP-скриптом, который сразу же удаляем.

Запуск:  python tools/wp/install-core.py
"""

import os
import secrets
import sys
import urllib.request

sys.path.insert(0, os.path.dirname(os.path.abspath(__file__)))
from common import Remote, fetch, say, BASE_URL, SITE_SUBDIR  # noqa: E402

# Русская сборка: админка сразу на русском, как требует п. 12.3 ТЗ
CORE_URL = 'https://ru.wordpress.org/latest-ru_RU.zip'
SCRATCH = os.environ.get('TEMP', '.')
ARCHIVE = os.path.join(SCRATCH, 'wordpress-ru.zip')

UNPACK = '''<?php
// Одноразовый распаковщик ядра. Удаляется сразу после работы.
header('Content-Type: text/plain');
if (($_GET['token'] ?? '') !== '%(token)s') { http_response_code(403); exit('нет токена'); }
$zip = new ZipArchive();
if ($zip->open(__DIR__ . '/%(archive)s') !== true) exit('архив не открылся');
if (!$zip->extractTo(__DIR__ . '/%(tmp)s')) exit('не распаковалось');
$zip->close();
if (!rename(__DIR__ . '/%(tmp)s/wordpress', __DIR__ . '/%(target)s')) exit('не переехало');
@rmdir(__DIR__ . '/%(tmp)s');
echo 'ок ', count(scandir(__DIR__ . '/%(target)s')) - 2, ' файлов в корне';
'''


def main():
    if not os.path.exists(ARCHIVE):
        say('качаю ядро WordPress...')
        urllib.request.urlretrieve(CORE_URL, ARCHIVE)
    say(f'архив: {os.path.getsize(ARCHIVE) // 1024 // 1024} МБ')

    r = Remote()
    if r.exists(SITE_SUBDIR):
        say(f'подпапка {SITE_SUBDIR} уже существует — ничего не делаю')
        r.close()
        return

    token = secrets.token_hex(8)
    unpacker = f'unpack-{token}.php'
    say('заливаю архив...')
    r.put(ARCHIVE, 'wp-core.zip')
    r.put_text(UNPACK % {'token': token, 'archive': 'wp-core.zip',
                         'tmp': f'wp-unpack-{token}', 'target': SITE_SUBDIR}, unpacker)
    say('распаковываю на сервере...')
    answer = fetch(f'{BASE_URL}/{unpacker}?token={token}').strip()
    say(f'сервер ответил: {answer}')
    for junk in (unpacker, 'wp-core.zip'):
        try:
            r.remove(junk)
        except FileNotFoundError:
            pass
    say('служебные файлы удалены')
    say('ядро на месте: ' + ('да' if r.exists(SITE_SUBDIR, 'wp-includes', 'version.php') else 'нет'))
    r.close()


if __name__ == '__main__':
    main()
