"""Точечный деплой концепции 4 на тестовый сервер.

Заливает ТОЛЬКО site/4/ и общий style.css. Страницы /1, /2, /3 и корневой
index.html делал коллега — их не трогаем.

Перед заливкой пересчитывает метку версии у style.css в 4/index.html.
Без этого браузер отдаёт посетителю старую сохранённую копию стилей,
и правки «не появляются» — уже наступали на это.
"""
import hashlib
import os
import posixpath
import re
import sys

import paramiko

CREDS = os.path.expandvars(r'%LOCALAPPDATA%\CrazyAssistant\creds\card-199.env')
PAGE = 'site/4/index.html'
CSS = 'site/assets/css/style.css'

# файлы, чью метку версии в ссылке нужно держать в актуальном состоянии
VERSIONED = [
    ('style.css', CSS),
    ('app.js', 'site/assets/js/app.js'),
    ('quiz.js', 'site/assets/js/quiz.js'),
    ('form.js', 'site/assets/js/form.js'),
]


def read_env(path):
    if not os.path.exists(path):
        sys.exit(f'Файл доступов не найден: {path}')
    env = {}
    with open(path, encoding='utf-8-sig') as f:
        for line in f:
            line = line.strip()
            if not line or line.startswith('#') or '=' not in line:
                continue
            key, value = line.split('=', 1)
            env[key.strip()] = value.strip().strip('"').strip("'")
    return env


def refresh_cache_token():
    """Метка версии = хеш содержимого файла: меняется сама, когда меняется файл.

    Без этого браузер отдаёт посетителю старую копию из кэша, и правки
    «не появляются» — уже наступали на это и со стилями, и со скриптами.
    """
    html = open(PAGE, encoding='utf-8').read()
    changed = []

    for name, path in VERSIONED:
        if not os.path.exists(path):
            continue
        token = hashlib.md5(open(path, 'rb').read()).hexdigest()[:8]
        pattern = re.escape(name) + r'\?v=([a-z0-9]+)'
        current = re.search(pattern, html)
        if not current:
            # файл подключён без метки — добавляем
            html = html.replace(name + '"', f'{name}?v={token}"')
            changed.append(f'{name}: метка добавлена ({token})')
            continue
        if current.group(1) == token:
            continue
        html = html.replace(f'{name}?v={current.group(1)}', f'{name}?v={token}')
        changed.append(f'{name}: {current.group(1)} -> {token}')

    if changed:
        open(PAGE, 'w', encoding='utf-8').write(html)
        for line in changed:
            print('  версия ' + line)
    else:
        print('метки версий актуальны')


def ensure_dir(sftp, path):
    parts = path.strip('/').split('/')
    current = ''
    for part in parts:
        current = f'{current}/{part}' if current else part
        try:
            sftp.stat(current)
        except IOError:
            sftp.mkdir(current)


def main():
    refresh_cache_token()

    env = read_env(CREDS)
    root = env['SFTP_DIR'].rstrip('/')

    print(f'Подключаюсь к {env["SFTP_HOST"]} как {env["SFTP_USER"]}')
    transport = paramiko.Transport((env['SFTP_HOST'], int(env.get('SFTP_PORT') or 22)))
    transport.connect(username=env['SFTP_USER'], password=env['SFTP_PASSWORD'])
    sftp = paramiko.SFTPClient.from_transport(transport)

    uploads = [(PAGE, '4/index.html'), (CSS, 'assets/css/style.css')]
    for name, path in VERSIONED:
        if name.endswith('.js') and os.path.exists(path):
            uploads.append((path, 'assets/js/' + name))

    try:
        for local, remote in uploads:
            target = posixpath.join(root, remote)
            ensure_dir(sftp, posixpath.dirname(target))
            sftp.put(local, target)
            print(f'  + {remote}  ({os.path.getsize(local) // 1024} KB)')
    finally:
        sftp.close()
        transport.close()

    print('\nОткрыть: http://korovai.crazytest.ru/tts-kazan.ru/4/')


if __name__ == '__main__':
    main()
