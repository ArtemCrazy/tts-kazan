"""Проверка доступа: сервер, папка сайта, база. Пароли не выводит."""

import sys, os
sys.path.insert(0, os.path.dirname(os.path.abspath(__file__)))
from common import Remote, read_env, say, SITE_SUBDIR

r = Remote()
say(f'папка сайта: {r.root} -> {sorted(r.sftp.listdir(r.root))}')
say(f'подпапка {SITE_SUBDIR}: ' + ('уже есть' if r.exists(SITE_SUBDIR) else 'ещё нет'))
db = read_env('card-199-db.env')
say('база: ' + db['DB_NAME'] + ' на ' + db['DB_HOST'] + ' (пароль прочитан: '
    + ('да' if db.get('DB_PASSWORD') else 'нет') + ')')
r.close()
