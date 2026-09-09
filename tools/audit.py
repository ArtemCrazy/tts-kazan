"""Проверка вёрстки: сетка, переполнение по горизонтали, картинки на местах.

Каждая страница открывается в headless Chrome на шести ширинах и сверяется
с главной: колонка контента должна начинаться там же, страница не должна
разъезжаться по горизонтали, а у каждой полосы с рендером должна быть
подставлена картинка.

Проверять глазами по скриншотам недостаточно: сдвиг колонки на десяток
пикселей на глаз незаметен, а сетка от этого уже сломана.

Запуск:  python tools/audit.py
"""

import io
import json
import os
import pathlib
import re
import subprocess
import sys

sys.stdout.reconfigure(encoding='utf-8')

ROOT = os.path.dirname(os.path.dirname(os.path.abspath(__file__)))
SITE = os.path.join(ROOT, 'site', '4')
HARNESS = os.path.join(SITE, '_audit.html')

CHROME = next((p for p in [
    r'C:\Program Files\Google\Chrome\Application\chrome.exe',
    r'C:\Program Files (x86)\Google\Chrome\Application\chrome.exe',
] if os.path.exists(p)), None)

WIDTHS = [1920, 1440, 1024, 768, 390, 360]

PAGE = """<!doctype html><meta charset="utf-8"><title>audit</title>
<style>html,body{margin:0}iframe{border:0;display:block}</style>
<body>
<script>
var q = new URLSearchParams(location.search);
var f = document.createElement('iframe');
f.width = q.get('w'); f.height = 900;
f.onload = function () {
  var d = f.contentDocument, win = f.contentWindow;
  var shell = d.querySelector('.shell');
  var media = [].slice.call(d.querySelectorAll('.model__media'));
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
      return win.getComputedStyle(el).backgroundImage === 'none';
    }).length,
    wide: wide.slice(0, 3)
  };
  document.title = 'READY';
  var box = document.createElement('pre');
  box.id = 'out';
  box.textContent = JSON.stringify(out);
  document.body.appendChild(box);
};
f.src = q.get('page');
document.body.appendChild(f);
</script>
"""


def measure(page, width):
    uri = pathlib.Path(HARNESS).resolve().as_uri()
    url = '%s?page=%s&w=%d' % (uri, page, width)
    cmd = [CHROME, '--headless', '--disable-gpu', '--allow-file-access-from-files',
           '--window-size=%d,900' % width, '--virtual-time-budget=8000', '--dump-dom', url]
    dom = subprocess.run(cmd, capture_output=True, timeout=90).stdout.decode('utf-8', 'replace')
    m = re.search(r'<pre id="out">(.*?)</pre>', dom, re.S)
    if not m:
        return None
    return json.loads(m.group(1))


def pages():
    found = []
    for dirpath, _dirs, files in os.walk(SITE):
        if 'index.html' in files:
            rel = os.path.relpath(dirpath, SITE).replace(os.sep, '/')
            found.append('index.html' if rel == '.' else rel + '/index.html')
    return sorted(found, key=lambda p: (p.count('/'), p))


def main():
    if not CHROME:
        sys.exit('Chrome не найден — проверить нечем.')
    io.open(HARNESS, 'w', encoding='utf-8').write(PAGE)
    try:
        bad = 0
        checks = 0
        for width in WIDTHS:
            base = None
            print('--- %d px ---' % width)
            for page in pages():
                r = measure(page, width)
                checks += 1
                if r is None:
                    print('   ОШИБКА  %-34s замер не получен' % page)
                    bad += 1
                    continue
                if base is None:
                    base = r['shell']
                notes = []
                if r['shell'] != base:
                    notes.append('колонка %s вместо %s' % (r['shell'], base))
                if r['scrollW'] > r['clientW'] + 1:
                    notes.append('ширина %s при экране %s: %s'
                                 % (r['scrollW'], r['clientW'], ', '.join(r['wide']) or '?'))
                if r['mediaEmpty']:
                    notes.append('без картинки полос: %d из %d' % (r['mediaEmpty'], r['media']))
                if notes:
                    bad += 1
                    print('   ПРОВАЛ  %-34s %s' % (page, '; '.join(notes)))
        print()
        print('проверок: %d, провалов: %d' % (checks, bad))
    finally:
        if os.path.exists(HARNESS):
            os.remove(HARNESS)


if __name__ == '__main__':
    main()
