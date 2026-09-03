"""Заливка статики на тестовый сервер студии.

Доступы берутся из файла вне проекта (путь указан в .claude/CLAUDE.md),
в репозиторий и в вывод они не попадают.

Запуск:  python deploy.py
Адрес:   http://korovai.crazytest.ru/tts-kazan.ru/
"""

import os
import posixpath
import sys

import paramiko

CREDS = os.path.expandvars(r'%LOCALAPPDATA%\CrazyAssistant\creds\card-199.env')
LOCAL_ROOT = 'site'

# служебное на боевой сервер не уходит
SKIP_DIRS = {'.claude', '.git', '.renders', '__pycache__', 'node_modules'}
SKIP_FILES = {'.DS_Store', 'Thumbs.db'}


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


def collect(root):
    for dirpath, dirnames, filenames in os.walk(root):
        dirnames[:] = [d for d in dirnames if d not in SKIP_DIRS]
        for name in filenames:
            if name in SKIP_FILES:
                continue
            full = os.path.join(dirpath, name)
            yield full, os.path.relpath(full, root).replace(os.sep, '/')


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
    env = read_env(CREDS)
    required = ('SFTP_HOST', 'SFTP_USER', 'SFTP_PASSWORD', 'SFTP_DIR')
    missing = [k for k in required if not env.get(k)]
    if missing:
        sys.exit('В файле доступов не хватает: ' + ', '.join(missing))

    host = env['SFTP_HOST']
    port = int(env.get('SFTP_PORT') or 22)
    remote_root = env['SFTP_DIR'].rstrip('/')

    files = list(collect(LOCAL_ROOT))
    if not files:
        sys.exit(f'В папке {LOCAL_ROOT} нечего заливать.')

    print(f'Подключаюсь к {host}:{port} как {env["SFTP_USER"]}')
    transport = paramiko.Transport((host, port))
    transport.connect(username=env['SFTP_USER'], password=env['SFTP_PASSWORD'])
    sftp = paramiko.SFTPClient.from_transport(transport)

    try:
        ensure_dir(sftp, remote_root)
        made = set()
        for local, rel in files:
            remote = posixpath.join(remote_root, rel)
            parent = posixpath.dirname(remote)
            if parent not in made:
                ensure_dir(sftp, parent)
                made.add(parent)
            sftp.put(local, remote)
            print(f'  + {rel}  ({os.path.getsize(local) // 1024} KB)')
    finally:
        sftp.close()
        transport.close()

    print(f'\nГотово, залито файлов: {len(files)}')
    print('Открыть: http://korovai.crazytest.ru/tts-kazan.ru/')


if __name__ == '__main__':
    main()
