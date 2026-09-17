"""Короткая проверка: сайт отвечает, админка на месте, версия и индексация."""

import os, re, sys
sys.path.insert(0, os.path.dirname(os.path.abspath(__file__)))
from common import fetch, say, SITE_URL

home = fetch(SITE_URL + '/')
title = re.search(r'<title>(.*?)</title>', home, re.S)
say('главная: ' + (title.group(1).strip() if title else 'заголовок не найден'))
say('тема: ' + (re.search(r'/themes/([^/]+)/', home).group(1) if '/themes/' in home else '—'))
say('запрет индексации: ' + ('да' if 'noindex' in home else 'нет'))
login = fetch(SITE_URL + '/wp-login.php')
say('страница входа: ' + ('открылась' if 'user_login' in login else 'нет'))
say('адрес админки: ' + SITE_URL + '/wp-admin/')
