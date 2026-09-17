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

# --- проверка темы ---
say('')
for name, url in (('главная', SITE_URL + '/'), ('404', SITE_URL + '/net-takoy-stranicy/')):
    page = fetch(url)
    marks = {
        'шапка': 'class="masthead"' in page,
        'подвал': 'class="footer"' in page,
        'наши стили': '/themes/tts/assets/css/style.css' in page,
        'шрифты': 'montserrat-cyrillic.woff2' in page,
        'ошибки PHP': ('Fatal error' in page or 'Warning:' in page or 'Notice:' in page),
    }
    say(name + ': ' + ', '.join(f'{k} — {"да" if v else "нет"}' for k, v in marks.items()))
css = fetch(SITE_URL + '/wp-content/themes/tts/assets/css/style.css')
say(f'style.css отдаётся: {"да" if ".masthead" in css else "нет"} ({len(css) // 1024} КБ)')
