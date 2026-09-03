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
    """Метка версии = хеш содержимого CSS: меняется сама, когда меняются стили."""
    token = hashlib.md5(open(CSS, 'rb').read()).hexdigest()[:8]
    html = open(PAGE, encoding='utf-8').read()
    current = re.search(r'style\.css\?v=([a-z0-9]+)', html)
    if not current:
        sys.exit('В 4/index.html не нашёл ссылку на style.css с меткой версии.')
    if current.group(1) == token:
        print(f'метка версии актуальна: ?v={token}')
        return
    open(PAGE, 'w', encoding='utf-8').write(
        html.replace(f'style.css?v={current.group(1)}', f'style.css?v={token}'))
    print(f'метка версии обновлена: ?v={current.group(1)} -> ?v={token}')


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

    try:
        for local, remote in [(PAGE, '4/index.html'), (CSS, 'assets/css/style.css')]:
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
