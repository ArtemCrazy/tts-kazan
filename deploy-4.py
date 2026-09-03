"""Точечный деплой: заливает ТОЛЬКО site/4/ на тестовый сервер.
Ничего больше не трогает — /1, /2, /3, assets, корневой index.html
сделаны коллегой, их не задеваем."""
import os
import posixpath
import sys

import paramiko

CREDS = os.path.expandvars(r'%LOCALAPPDATA%\CrazyAssistant\creds\card-199.env')


def read_env(path):
    env = {}
    with open(path, encoding='utf-8-sig') as f:
        for line in f:
            line = line.strip()
            if not line or line.startswith('#') or '=' not in line:
                continue
            k, v = line.split('=', 1)
            env[k.strip()] = v.strip().strip('"').strip("'")
    return env


def ensure_dir(sftp, path):
    parts = path.strip('/').split('/')
    current = ''
    for part in parts:
        current = f'{current}/{part}' if current else part
        try:
            sftp.stat(current)
        except IOError:
            sftp.mkdir(current)


env = read_env(CREDS)
host, port = env['SFTP_HOST'], int(env.get('SFTP_PORT') or 22)
root = env['SFTP_DIR'].rstrip('/')

local = 'site/4/index.html'
remote = posixpath.join(root, '4', 'index.html')

print(f'Подключаюсь к {host}:{port} как {env["SFTP_USER"]}')
transport = paramiko.Transport((host, port))
transport.connect(username=env['SFTP_USER'], password=env['SFTP_PASSWORD'])
sftp = paramiko.SFTPClient.from_transport(transport)

ensure_dir(sftp, posixpath.dirname(remote))
sftp.put(local, remote)

sftp.close()
transport.close()

print(f'Залито: {local} -> {remote}')
print('Открыть: http://korovai.crazytest.ru/tts-kazan.ru/4/')
