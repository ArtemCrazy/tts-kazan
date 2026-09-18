"""Поблочная сверка: высота каждой секции в статике и в WordPress.

compare-static.py показывает, что страница где-то разъехалась. Этот скрипт
отвечает, где именно: для каждой секции страницы печатает её высоту в обеих
версиях на ПК и на телефоне. Секция, у которой высота отличается, — место,
куда смотреть.

Запуск:  python tools/wp/compare-sections.py            — все страницы
         python tools/wp/compare-sections.py ПКН        — одна
"""

import importlib.util
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

_spec = importlib.util.spec_from_file_location('cmp', os.path.join(os.path.dirname(__file__), 'compare-static.py'))
cmp = importlib.util.module_from_spec(_spec)
_spec.loader.exec_module(cmp)

MEASURE = """<!doctype html><meta charset="utf-8"><title>m</title><body><script>
var q = new URLSearchParams(location.search);
var f = document.createElement('iframe');
f.width = q.get('w'); f.height = 900; f.style.border = 0;
f.onload = function () { setTimeout(function () {
  var d = f.contentDocument;
  var st = d.createElement('style');
  st.textContent = '*{animation:none!important;transition:none!important}.reveal{opacity:1!important;transform:none!important}.hero{min-height:900px!important}';
  d.head.appendChild(st);
  var main = d.querySelector('main') || d.body;
  var out = [];
  [].forEach.call(main.children, function (el) {
    var h = Math.round(el.getBoundingClientRect().height);
    if (!h) return;
    // Таблица-продолжение в WordPress — отдельный блок, а в статике часть
    // предыдущей секции: считаем их вместе, иначе сверка показывает ложную разницу.
    if (el.classList.contains('page-section--continue') && out.length) { out[out.length - 1].h += h; return; }
    out.push({ id: el.id || '', cls: (el.className || el.tagName).toString().split(' ').slice(0, 2).join('.'), h: h });
  });
  var pre = document.createElement('pre'); pre.id = 'out'; pre.textContent = JSON.stringify(out);
  document.body.appendChild(pre);
}, 1500); };
f.src = q.get('page'); document.body.appendChild(f);
</script>"""


def measure(uri, width, folder):
    harness = pathlib.Path(folder) / '_measure.html'
    harness.write_text(MEASURE, encoding='utf-8')
    url = '%s?page=%s&w=%d' % (harness.as_uri(), uri, width)
    dom = subprocess.run([cmp.CHROME, '--headless', '--disable-gpu', '--allow-file-access-from-files',
                          '--window-size=%d,900' % (width + 20), '--virtual-time-budget=12000',
                          '--dump-dom', url], capture_output=True, timeout=180).stdout.decode('utf-8', 'replace')
    found = re.search(r'<pre id="out">(.*?)</pre>', dom, re.S)
    return json.loads(found.group(1)) if found else []


def main():
    only = sys.argv[1:] or list(cmp.PAGES)
    folder = tempfile.mkdtemp(prefix='tts-sections-')
    local = (cmp.ROOT / 'site' / 'assets').resolve().as_uri() + '/'

    for name in only:
        static_rel, wp_path = cmp.PAGES[name]
        wp_html = fetch(SITE_URL + wp_path)
        pattern = re.escape(SITE_URL + '/wp-content/themes/tts/assets/') + r"""([^"'?\s]+)(\?ver=[^"']*)?"""
        wp_html = re.sub(pattern, lambda m: local + m.group(1), wp_html)
        wp_file = pathlib.Path(folder) / ('wp-%s.html' % re.sub(r'\W+', '_', name))
        wp_file.write_text(wp_html.replace('loading="lazy"', 'loading="eager"'), encoding='utf-8')

        say('\n=== %s ===' % name)
        for width in cmp.WIDTHS:
            a = measure((cmp.STATIC / static_rel).resolve().as_uri(), width, folder)
            b = measure(wp_file.resolve().as_uri(), width, folder)
            say('  %d px: секций в статике %d, в WordPress %d' % (width, len(a), len(b)))
            for i in range(max(len(a), len(b))):
                x = a[i] if i < len(a) else None
                y = b[i] if i < len(b) else None
                label = (x or y)['id'] or (x or y)['cls']
                hx = x['h'] if x else '—'
                hy = y['h'] if y else '—'
                mark = '' if x and y and abs(hx - hy) <= 4 else '   <-- разница'
                say('    %-26s %6s | %6s%s' % (label[:26], hx, hy, mark))


if __name__ == '__main__':
    main()
