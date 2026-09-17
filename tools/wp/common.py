r"""Общее для скриптов установки WordPress: доступы, SFTP, запросы к сайту.

Доступы лежат вне проекта, в %LOCALAPPDATA%\CrazyAssistant\creds:
  card-199.env      — SFTP тестового сервера
  card-199-db.env   — MySQL под WordPress
  card-199-cms.env  — админ WordPress (создаётся при установке)

⛔ Пароли не печатаем в вывод и не пишем в файлы проекта.
"""

import os
import posixpath
import subprocess
import sys

import paramiko

CREDS_DIR = os.path.expandvars(r'%LOCALAPPDATA%\CrazyAssistant\creds')

# Куда ставим WordPress: подпапка тестового сервера, рядом со статикой /4/
SITE_SUBDIR = 'wp'
BASE_URL = 'http://korovai.crazytest.ru/tts-kazan.ru'
SITE_URL = f'{BASE_URL}/{SITE_SUBDIR}'


def read_env(name):
    path = os.path.join(CREDS_DIR, name)
    env = {}
    with open(path, encoding='utf-8-sig') as f:
        for line in f:
            line = line.strip()
            if not line or line.startswith('#') or '=' not in line:
                continue
            key, value = line.split('=', 1)
            env[key.strip()] = value.strip().strip('"').strip("'")
    return env


def write_env(name, values, header):
    """Записать доступы в папку creds (вне проекта, вне git)."""
    path = os.path.join(CREDS_DIR, name)
    with open(path, 'w', encoding='utf-8') as f:
        f.write(f'# {header}\n')
        for key, value in values.items():
            f.write(f'{key}={value}\n')
    return path


class Remote:
    """SFTP-подключение к тестовому серверу с путями от папки сайта."""

    def __init__(self):
        env = read_env('card-199.env')
        self.root = env['SFTP_DIR'].rstrip('/')
        self.transport = paramiko.Transport((env['SFTP_HOST'], int(env.get('SFTP_PORT') or 22)))
        self.transport.connect(username=env['SFTP_USER'], password=env['SFTP_PASSWORD'])
        self.sftp = paramiko.SFTPClient.from_transport(self.transport)

    def path(self, *parts):
        return posixpath.join(self.root, *parts)

    def exists(self, *parts):
        try:
            self.sftp.stat(self.path(*parts))
            return True
        except FileNotFoundError:
            return False

    def put(self, local, *parts):
        self.sftp.put(local, self.path(*parts))

    def put_text(self, text, *parts):
        with self.sftp.open(self.path(*parts), 'w') as f:
            f.write(text)

    def remove(self, *parts):
        self.sftp.remove(self.path(*parts))

    def mkdir(self, *parts):
        if not self.exists(*parts):
            self.sftp.mkdir(self.path(*parts))

    def close(self):
        self.sftp.close()
        self.transport.close()


def fetch(url, data=None):
    """Запрос к тестовому сайту. Печенька beget обязательна, иначе хостинг
    отдаёт свою страницу-заставку вместо сайта."""
    cmd = ['curl', '-s', '-L', '-b', 'beget=begetok']
    if data:
        for key, value in data.items():
            cmd += ['--data-urlencode', f'{key}={value}']
    cmd.append(url)
    done = subprocess.run(cmd, capture_output=True, timeout=180)
    return done.stdout.decode('utf-8', 'replace')


def say(text):
    sys.stdout.reconfigure(encoding='utf-8')
    print(text, flush=True)


class Admin:
    """Вход в админку под администратором — для проверок после правок.

    Пароль читаем из creds и никуда не печатаем.
    """

    def __init__(self):
        import http.cookiejar
        import urllib.parse
        import urllib.request

        env = read_env('card-199-cms.env')
        self.base = SITE_URL
        jar = http.cookiejar.CookieJar()
        # Печеньку хостинга кладём в тот же контейнер: если задать её отдельным
        # заголовком, она затрёт печеньки входа WordPress — уже наступали.
        jar.set_cookie(http.cookiejar.Cookie(
            0, 'beget', 'begetok', None, False, 'korovai.crazytest.ru', False, False,
            '/', True, False, None, False, None, None, {}))
        self.opener = urllib.request.build_opener(urllib.request.HTTPCookieProcessor(jar))
        self.opener.addheaders = [('User-Agent', 'tts-check')]
        data = urllib.parse.urlencode({
            'log': env['CMS_USER'],
            'pwd': env['CMS_PASSWORD'],
            'rememberme': 'forever',
            'redirect_to': f'{SITE_URL}/wp-admin/',
            'testcookie': '1',
        }).encode()
        # Первый запрос ставит тестовую печеньку, без неё WordPress не пускает.
        self.opener.open(f'{SITE_URL}/wp-login.php', timeout=60).read()
        page = self.opener.open(f'{SITE_URL}/wp-login.php', data, timeout=60).read().decode('utf-8', 'replace')
        self.logged_in = 'adminmenu' in page or 'wp-admin-bar' in page

    def get(self, path):
        return self.opener.open(self.base + path, timeout=90).read().decode('utf-8', 'replace')
