"""Проверка вёрстки WordPress-версии на шести ширинах (п. 3.3 ТЗ).

Работает так же, как tools/audit.py для статической версии: страница
открывается в headless Chrome на каждой ширине, и проверяется, что колонка
контента стоит на месте, страница не разъезжается по горизонтали и полосы
с рендерами не пустые.

Отличие одно: страницы живут на сервере, поэтому сначала скачиваем их HTML
в папку рядом с assets темы. Адреса стилей и скриптов при этом остаются
серверными — проверяется именно то, что отдаёт сайт.

Запуск:  python tools/wp/audit-wp.py
"""

import io
import json
import os
import pathlib
import re
import subprocess
import sys
import tempfile

sys.path.insert(0, os.path.dirname(os.path.abspath(__file__)))
from common import fetch, say, SITE_URL  # noqa: E402

CHROME = next(
    (p for p in [
        r'C:\Program Files\Google\Chrome\Application\chrome.exe',
        r'C:\Program Files (x86)\Google\Chrome\Application\chrome.exe',
    ] if os.path.exists(p)),
    None,
)

WIDTHS = [1920, 1440, 1024, 768, 390, 360]

PAGES = [
    ('главная', '/'),
    ('каталог', '/catalog/'),
    ('ЗССС', '/catalog/smartdrymix/'),
    ('бетонные заводы', '/catalog/smartbeton/'),
    ('ВПИ', '/catalog/vpi/'),
    ('терминалы', '/catalog/smartstock/'),
    ('ПКН', '/catalog/pkn/'),
    ('сервис', '/service/'),
    ('запчасти', '/parts/'),
    ('политика', '/privacy-policy/'),
    ('согласие', '/personal-data/'),
    ('cookie', '/cookie/'),
]

HARNESS = """<!doctype html><meta charset="utf-8"><title>audit</title>
<style>html,body{margin:0}iframe{border:0;display:block}</style>
<body>
<script>
var q = new URLSearchParams(location.search);
var f = document.createElement('iframe');
f.width = q.get('w'); f.height = 900;
f.onload = function () {
  setTimeout(function () {
    var d = f.contentDocument, win = f.contentWindow;
    var shell = d.querySelector('.shell');
    var media = [].slice.call(d.querySelectorAll('.model__media, .product__media'));
    var wide = [];
    [].forEach.call(d.querySelectorAll('body *'), function (el) {
      var r = el.getBoundingClientRect();
      if (r.width > d.documentElement.clientWidth + 1) wide.push(el.className || el.tagName);
    });
    var out = {
      shell: shell ? Math.round(shell.getBoundingClientRect().left) : null,
      scrollW: d.documentElement.scrollWidth,
      clientW: d.documentElement.clientWidth,
      media: media.length,
      mediaEmpty: media.filter(function (el) {
        var style = win.getComputedStyle(el);
        return style.backgroundImage === 'none' && !el.querySelector('img');
      }).length,
      wide: wide.slice(0, 4)
    };
    document.title = 'READY';
    var box = document.createElement('pre');
    box.id = 'out';
    box.textContent = JSON.stringify(out);
    document.body.appendChild(box);
  }, 600);
};
f.src = q.get('page');
document.body.appendChild(f);
</script>
"""


def download(folder):
    """Страницы сайта в локальные файлы — иначе iframe не измерить."""
    saved = []
    for name, path in PAGES:
        html = fetch(SITE_URL + path)
        # Ленивые картинки в iframe не грузятся, а без них полосы выглядят
        # пустыми: для проверки просим браузер грузить их сразу.
        html = html.replace('loading="lazy"', 'loading="eager"')
        target = os.path.join(folder, re.sub(r'[^a-z0-9]+', '-', name.lower()) + '.html')
        io.open(target, 'w', encoding='utf-8').write(html)
        saved.append((name, target))
    return saved


def measure(harness, page, width):
    url = '%s?page=%s&w=%d' % (
        pathlib.Path(harness).resolve().as_uri(),
        pathlib.Path(page).resolve().as_uri(),
        width,
    )
    cmd = [CHROME, '--headless', '--disable-gpu', '--allow-file-access-from-files',
           '--window-size=%d,900' % width, '--virtual-time-budget=9000', '--dump-dom', url]
    dom = subprocess.run(cmd, capture_output=True, timeout=120).stdout.decode('utf-8', 'replace')
    found = re.search(r'<pre id="out">(.*?)</pre>', dom, re.S)
    return json.loads(found.group(1)) if found else None


def main():
    if not CHROME:
        sys.exit('Chrome не найден — проверить нечем.')

    folder = tempfile.mkdtemp(prefix='tts-wp-audit-')
    harness = os.path.join(folder, '_audit.html')
    io.open(harness, 'w', encoding='utf-8').write(HARNESS)

    say('скачиваю страницы...')
    saved = download(folder)

    bad = 0
    checks = 0
    for width in WIDTHS:
        say('--- %d px ---' % width)
        base = None
        for name, page in saved:
            result = measure(harness, page, width)
            checks += 1
            if result is None:
                say('   ОШИБКА  %-20s замер не получен' % name)
                bad += 1
                continue
            if base is None:
                base = result['shell']
            notes = []
            if result['shell'] != base:
                notes.append('колонка %s вместо %s' % (result['shell'], base))
            if result['scrollW'] > result['clientW'] + 1:
                notes.append('ширина %s при экране %s: %s'
                             % (result['scrollW'], result['clientW'], ', '.join(result['wide']) or '?'))
            if result['mediaEmpty']:
                notes.append('без картинки полос: %d из %d' % (result['mediaEmpty'], result['media']))
            if notes:
                bad += 1
                say('   ПРОВАЛ  %-20s %s' % (name, '; '.join(notes)))

    say('')
    say('проверок: %d, провалов: %d' % (checks, bad))


if __name__ == '__main__':
    main()
