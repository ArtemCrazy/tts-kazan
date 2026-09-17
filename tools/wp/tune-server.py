"""Сжатие и кэш статики на сервере (п. 15.1 ТЗ).

Дописывает в .htaccess сайта два блока: сжатие текстовых файлов и длительное
кэширование картинок, шрифтов, стилей и скриптов. Правила обёрнуты в IfModule,
поэтому на хостинге без нужного модуля просто ничего не произойдёт.

Блок WordPress в .htaccess не трогаем — он выше и остаётся как есть.

Запуск:  python tools/wp/tune-server.py
"""

import os
import posixpath
import subprocess
import sys

sys.path.insert(0, os.path.dirname(os.path.abspath(__file__)))
from common import Remote, say, SITE_SUBDIR, SITE_URL  # noqa: E402

MARK = '# BEGIN TTS static'

RULES = MARK + '''
# Сжатие и кэш статики. Добавлено скриптом tools/wp/tune-server.py.

<IfModule mod_deflate.c>
  AddOutputFilterByType DEFLATE text/html text/plain text/xml text/css
  AddOutputFilterByType DEFLATE application/javascript application/json
  AddOutputFilterByType DEFLATE image/svg+xml application/rss+xml
</IfModule>

<IfModule mod_brotli.c>
  AddOutputFilterByType BROTLI_COMPRESS text/html text/css
  AddOutputFilterByType BROTLI_COMPRESS application/javascript application/json
  AddOutputFilterByType BROTLI_COMPRESS image/svg+xml
</IfModule>

<IfModule mod_expires.c>
  ExpiresActive On
  # Картинки, шрифты и медиа меняются вместе с именем файла
  ExpiresByType image/png "access plus 1 year"
  ExpiresByType image/jpeg "access plus 1 year"
  ExpiresByType image/webp "access plus 1 year"
  ExpiresByType image/svg+xml "access plus 1 year"
  ExpiresByType font/woff2 "access plus 1 year"
  ExpiresByType video/mp4 "access plus 1 year"
  # У стилей и скриптов в адресе есть метка версии, поэтому тоже надолго
  ExpiresByType text/css "access plus 1 month"
  ExpiresByType application/javascript "access plus 1 month"
  # HTML не кэшируем: контент правит редактор
  ExpiresByType text/html "access plus 0 seconds"
</IfModule>
# END TTS static
'''


def main():
    r = Remote()
    path = posixpath.join(r.root, SITE_SUBDIR, '.htaccess')

    try:
        with r.sftp.open(path) as f:
            current = f.read().decode('utf-8', 'replace')
    except FileNotFoundError:
        current = ''
        say('.htaccess не было — создаю')

    if MARK in current:
        say('правила уже на месте — ничего не меняю')
    else:
        r.put_text(current.rstrip() + '\n\n' + RULES, SITE_SUBDIR, '.htaccess')
        say('правила сжатия и кэша добавлены')

    r.close()

    # Проверяем, что сервер действительно отдаёт сжатие и срок кэша
    url = SITE_URL + '/wp-content/themes/tts/assets/css/style.css'
    head = subprocess.run(
        ['curl', '-s', '-D', '-', '-o', os.devnull, '-H', 'Accept-Encoding: gzip, br',
         '-b', 'beget=begetok', url],
        capture_output=True, timeout=120
    ).stdout.decode('utf-8', 'replace')

    for line in head.splitlines():
        if line.lower().startswith(('content-encoding', 'cache-control', 'expires', 'http/')):
            say('  ' + line.strip())


if __name__ == '__main__':
    main()
