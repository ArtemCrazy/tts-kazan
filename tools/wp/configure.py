"""wp-config.php и установка WordPress. Пароль админа не печатаем.

Логин и пароль администратора скрипт создаёт сам и кладёт в папку creds
рядом с остальными доступами — вне проекта и вне git.

Запуск:  python tools/wp/configure.py
"""

import os
import re
import secrets
import string
import sys
import urllib.request

sys.path.insert(0, os.path.dirname(os.path.abspath(__file__)))
from common import (Remote, fetch, read_env, write_env, say,  # noqa: E402
                    SITE_SUBDIR, SITE_URL)

SALT_URL = 'https://api.wordpress.org/secret-key/1.1/salt/'

# Служебный адрес тестового контура. Настоящую почту заказчика поставим,
# когда он передаст ящик и SMTP (п. 11.2 ТЗ) — до этого письма никуда не уйдут.
ADMIN_EMAIL = 'admin@korovai.crazytest.ru'
ADMIN_LOGIN = 'tts-admin'
SITE_TITLE = 'ТТС Инжиниринг Казахстан'

CONFIG = '''<?php
/**
 * Настройки WordPress для тестового контура ТТС.
 * Собрано скриптом tools/wp/configure.py — доступы лежат вне проекта.
 */

define( 'DB_NAME', '%(db_name)s' );
define( 'DB_USER', '%(db_user)s' );
define( 'DB_PASSWORD', '%(db_password)s' );
define( 'DB_HOST', '%(db_host)s' );
define( 'DB_CHARSET', 'utf8mb4' );
define( 'DB_COLLATE', '' );

%(salts)s

$table_prefix = 'tts_';

/* Тестовый контур: ошибки в лог, а не на экран посетителю. */
define( 'WP_DEBUG', false );
define( 'WP_DEBUG_LOG', true );
define( 'WP_DEBUG_DISPLAY', false );

/* По п. 12.1 ТЗ ядро и плагины не правим — редактор файлов в админке не нужен. */
define( 'DISALLOW_FILE_EDIT', true );

/* Обновления безопасности ставятся сами, крупные версии — вручную после бэкапа (п. 15.3). */
define( 'WP_AUTO_UPDATE_CORE', 'minor' );

/* Плагины и обновления ставятся напрямую, без запроса доступов FTP. */
define( 'FS_METHOD', 'direct' );

if ( ! defined( 'ABSPATH' ) ) {
\tdefine( 'ABSPATH', __DIR__ . '/' );
}
require_once ABSPATH . 'wp-settings.php';
'''


def password(length=24):
    alphabet = string.ascii_letters + string.digits + '!@#%^*-_=+'
    return ''.join(secrets.choice(alphabet) for _ in range(length))


def main():
    db = read_env('card-199-db.env')
    r = Remote()

    if r.exists(SITE_SUBDIR, 'wp-config.php'):
        say('wp-config.php уже есть — оставляю как есть')
    else:
        salts = urllib.request.urlopen(SALT_URL, timeout=60).read().decode()
        r.put_text(CONFIG % {'db_name': db['DB_NAME'], 'db_user': db['DB_USER'],
                             'db_password': db['DB_PASSWORD'], 'db_host': db['DB_HOST'],
                             'salts': salts.strip()}, SITE_SUBDIR, 'wp-config.php')
        say('wp-config.php записан (пароль базы в нём, в git файл не попадает)')

    # Установка: WordPress сам создаёт таблицы и администратора.
    page = fetch(f'{SITE_URL}/wp-admin/install.php')
    if 'уже установлен' in page or 'already installed' in page.lower():
        say('WordPress уже установлен')
        r.close()
        return
    if 'Ошибка установления соединения с базой данных' in page:
        say('база не пускает — проверь доступы')
        r.close()
        return

    admin_pass = password()
    answer = fetch(f'{SITE_URL}/wp-admin/install.php?step=2', data={
        'weblog_title': SITE_TITLE,
        'user_name': ADMIN_LOGIN,
        'admin_password': admin_pass,
        'admin_password2': admin_pass,
        'pw_weak': '',
        'admin_email': ADMIN_EMAIL,
        # Тестовый контур не должен попасть в поиск (п. 14 ТЗ)
        'blog_public': '0',
        'Submit': 'Установить WordPress',
        'language': 'ru_RU',
    })
    done = 'Успешно' in answer or 'Success' in answer or 'wp-login.php' in answer
    say('установка: ' + ('прошла' if done else 'не прошла'))
    if not done:
        text = re.sub(r'<[^>]+>', ' ', answer)
        say('ответ сервера: ' + ' '.join(text.split())[:400])
        r.close()
        return

    path = write_env('card-199-cms.env', {
        'CMS_URL': f'{SITE_URL}/wp-admin/',
        'CMS_USER': ADMIN_LOGIN,
        'CMS_PASSWORD': admin_pass,
        'CMS_EMAIL': ADMIN_EMAIL,
    }, 'Админка WordPress (тестовый контур ТТС). Создано tools/wp/configure.py')
    say(f'доступы в админку сохранены: {os.path.basename(path)} (в папке creds)')
    r.close()


if __name__ == '__main__':
    main()
