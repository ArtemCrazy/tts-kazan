"""SEO-заголовки и описания страниц — как в статической версии.

В статике у каждой страницы свой <title> и meta description, их уже
согласовали. В WordPress их ведёт плагин Rank Math (поля rank_math_title и
rank_math_description у страницы), редактор правит их внизу страницы.
Скрипт переносит значения из статики, чтобы после переезда в поиске
ничего не поменялось.

Запуск:  python tools/wp/seed-seo.py
"""

import html
import io
import json
import os
import pathlib
import re
import secrets
import sys

sys.path.insert(0, os.path.dirname(os.path.abspath(__file__)))
from common import Remote, fetch, say, SITE_SUBDIR, SITE_URL  # noqa: E402

ROOT = pathlib.Path(__file__).resolve().parents[2]

# адрес страницы в WordPress -> файл статической версии
PAGES = {
    'home': 'index.html',
    'catalog': 'catalog/index.html',
    'catalog/smartdrymix': 'catalog/smartdrymix/index.html',
    'catalog/smartbeton': 'catalog/smartbeton/index.html',
    'catalog/vpi': 'catalog/vpi/index.html',
    'catalog/smartstock': 'catalog/smartstock/index.html',
    'catalog/pkn': 'catalog/pkn/index.html',
    'service': 'service/index.html',
    'parts': 'parts/index.html',
    'privacy-policy': 'privacy/index.html',
    'personal-data': 'personal-data/index.html',
    'cookie': 'cookie/index.html',
}

RUNNER = r'''<?php
// Одноразовая запись SEO-полей. Удаляется сразу после работы.
header('Content-Type: text/plain; charset=utf-8');
if (($_GET['token'] ?? '') !== '%(token)s') { http_response_code(403); exit('нет токена'); }
require __DIR__ . '/wp-load.php';
$plan = json_decode(file_get_contents(__DIR__ . '/%(payload)s'), true);
foreach ($plan as $path => $seo) {
    $page = get_page_by_path($path);
    if (!$page) { echo $path, ": страницы нет\n"; continue; }
    update_post_meta($page->ID, 'rank_math_title', wp_slash($seo['title']));
    update_post_meta($page->ID, 'rank_math_description', wp_slash($seo['description']));
    echo $path, ": ", $seo['title'], "\n";
}
'''


def clean(text):
    text = html.unescape(text)
    return re.sub(r'\s+', ' ', text.replace(' ', ' ').replace('­', '')).strip()


def plan():
    data = {}
    for path, rel in PAGES.items():
        markup = io.open(ROOT / 'site' / '4' / rel, encoding='utf-8').read()
        title = re.search(r'<title>(.*?)</title>', markup, re.S)
        desc = re.search(r'<meta name="description" content="([^"]*)"', markup)
        data[path] = {
            'title': clean(title.group(1)) if title else '',
            'description': clean(desc.group(1)) if desc else '',
        }
    return data


def main():
    data = plan()
    payload = 'seo-' + secrets.token_hex(4) + '.json'
    token = secrets.token_hex(8)
    runner = f'seo-{token}.php'
    r = Remote()
    r.put_text(json.dumps(data, ensure_ascii=False), SITE_SUBDIR, payload)
    r.put_text(RUNNER % {'token': token, 'payload': payload}, SITE_SUBDIR, runner)
    say(fetch(f'{SITE_URL}/{runner}?token={token}').strip())
    for junk in (runner, payload):
        try:
            r.remove(SITE_SUBDIR, junk)
        except FileNotFoundError:
            pass
    r.close()


if __name__ == '__main__':
    main()
