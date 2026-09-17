"""Заливка темы на тестовый сервер.

Тема лежит в site/wp-theme/tts, а стили, скрипты, шрифты и картинки — в
site/assets: это те же файлы, что у статической версии, и второй копии
в репозитории мы не держим. Скрипт кладёт их в тему как assets/.

Чтобы не перезаливать сотни файлов каждый раз, рядом хранится список
контрольных сумм: уходит только то, что изменилось.

Запуск:  python tools/wp/deploy-theme.py            — залить изменённое
         python tools/wp/deploy-theme.py --all      — залить всё заново
         python tools/wp/deploy-theme.py --activate — залить и включить тему
"""

import hashlib
import json
import os
import posixpath
import secrets
import sys

sys.path.insert(0, os.path.dirname(os.path.abspath(__file__)))
from common import Remote, fetch, say, SITE_SUBDIR, SITE_URL  # noqa: E402

ROOT = os.path.dirname(os.path.dirname(os.path.dirname(os.path.abspath(__file__))))
THEME_SRC = os.path.join(ROOT, 'site', 'wp-theme', 'tts')
ASSETS_SRC = os.path.join(ROOT, 'site', 'assets')
THEME_REMOTE = f'{SITE_SUBDIR}/wp-content/themes/tts'
MANIFEST = os.path.join(os.environ.get('TEMP', '.'), 'tts-wp-theme-manifest.json')

# Видео в тему не тащим: файл тяжёлый, а на страницах WordPress он не нужен.
SKIP_DIRS = {'video', '__pycache__'}

ACTIVATE = '''<?php
// Одноразовое включение темы. Удаляется сразу после работы.
header('Content-Type: text/plain');
if (($_GET['token'] ?? '') !== '%(token)s') { http_response_code(403); exit('нет токена'); }
require __DIR__ . '/wp-load.php';
$theme = wp_get_theme('tts');
if (!$theme->exists()) exit('темы нет на сервере');
if (get_stylesheet() !== 'tts') switch_theme('tts');
echo 'активная тема: ', get_stylesheet(), ' («', wp_get_theme()->get('Name'), '»)', "\\n";
$errors = $theme->errors();
echo 'ошибки темы: ', ($errors ? implode('; ', $errors->get_error_messages()) : 'нет'), "\\n";
'''


def files_to_send():
    """Пары (локальный файл, путь внутри темы)."""
    out = []
    for base, prefix in ((THEME_SRC, ''), (ASSETS_SRC, 'assets')):
        for dirpath, dirs, names in os.walk(base):
            dirs[:] = [d for d in dirs if d not in SKIP_DIRS]
            for name in names:
                if name.endswith(('.pyc', '.map')) or name.startswith('.'):
                    continue
                local = os.path.join(dirpath, name)
                rel = os.path.relpath(local, base).replace(os.sep, '/')
                out.append((local, posixpath.join(prefix, rel) if prefix else rel))
    return sorted(out, key=lambda pair: pair[1])


def digest(path):
    return hashlib.md5(open(path, 'rb').read()).hexdigest()


def main():
    full = '--all' in sys.argv
    activate = '--activate' in sys.argv

    known = {}
    if os.path.exists(MANIFEST) and not full:
        known = json.load(open(MANIFEST, encoding='utf-8'))

    r = Remote()
    made = set()

    def ensure(remote_rel):
        """Создать папки под файл (SFTP сам этого не делает)."""
        parts = remote_rel.split('/')[:-1]
        path = THEME_REMOTE
        r.mkdir(*path.split('/'))
        for part in parts:
            path = f'{path}/{part}'
            if path not in made:
                r.mkdir(*path.split('/'))
                made.add(path)

    sent = 0
    fresh = dict(known)
    for local, rel in files_to_send():
        token = digest(local)
        if known.get(rel) == token and r.exists(THEME_REMOTE, *rel.split('/')):
            continue
        ensure(rel)
        r.put(local, THEME_REMOTE, *rel.split('/'))
        fresh[rel] = token
        sent += 1
        say(f'  + {rel}  ({os.path.getsize(local) // 1024} КБ)')

    json.dump(fresh, open(MANIFEST, 'w', encoding='utf-8'))
    say(f'файлов отправлено: {sent} (всего в теме {len(fresh)})')

    if activate:
        token = secrets.token_hex(8)
        runner = f'theme-{token}.php'
        r.put_text(ACTIVATE % {'token': token}, SITE_SUBDIR, runner)
        say(fetch(f'{SITE_URL}/{runner}?token={token}').strip())
        try:
            r.remove(SITE_SUBDIR, runner)
        except FileNotFoundError:
            pass

    r.close()


if __name__ == '__main__':
    main()
