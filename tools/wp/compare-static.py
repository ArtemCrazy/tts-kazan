"""Сверка WordPress-версии со статической: текст и внешний вид каждой страницы.

Статическая версия site/4 — утверждённый эталон. После переноса на WordPress
каждая страница должна совпадать с ней по тексту и по виду, на ПК и на телефоне,
включая состояния после действий посетителя (результат квиза).

Почему так подробно: первая проверка сравнивала только верх страниц на ПК и не
открывала квиз — и пропустила сломанные карточки квиза и слипшиеся пункты в
блоке «Сервис». Здесь сравнивается всё.

Что делает:
  1. Текст. Для каждой страницы берёт видимый текст внутри <main> у обеих
     версий и показывает строки, которых нет в WordPress или которые лишние.
  2. Вид. Снимает страницу целиком на 1440 и 390 px, режет на полосы и для
     каждой ищет совпадение со сдвигом (если выше что-то стало на пару пикселей
     выше, остальное не должно считаться сломанным). Сохраняет картинки
     расхождений, чтобы их можно было посмотреть.
  3. Квиз. На главной открывает результат подбора в обеих версиях и сравнивает
     его отдельно.

Запуск:  python tools/wp/compare-static.py            — все страницы
         python tools/wp/compare-static.py главная    — одна страница
"""

import html
import io
import os
import pathlib
import re
import subprocess
import sys
import tempfile

from PIL import Image, ImageChops

sys.path.insert(0, os.path.dirname(os.path.abspath(__file__)))
from common import fetch, say, SITE_URL  # noqa: E402

ROOT = pathlib.Path(__file__).resolve().parents[2]
STATIC = ROOT / 'site' / '4'

CHROME = next(
    (p for p in [
        r'C:\Program Files\Google\Chrome\Application\chrome.exe',
        r'C:\Program Files (x86)\Google\Chrome\Application\chrome.exe',
    ] if os.path.exists(p)),
    None,
)

# страница: (путь в статике, адрес в WordPress)
PAGES = {
    'главная': ('index.html', '/'),
    'каталог': ('catalog/index.html', '/catalog/'),
    'ЗССС': ('catalog/smartdrymix/index.html', '/catalog/smartdrymix/'),
    'бетонные заводы': ('catalog/smartbeton/index.html', '/catalog/smartbeton/'),
    'ВПИ': ('catalog/vpi/index.html', '/catalog/vpi/'),
    'терминалы': ('catalog/smartstock/index.html', '/catalog/smartstock/'),
    'ПКН': ('catalog/pkn/index.html', '/catalog/pkn/'),
    'сервис': ('service/index.html', '/service/'),
    'запчасти': ('parts/index.html', '/parts/'),
}

WIDTHS = (1440, 390)
BAND = 240          # высота полосы сравнения
SHIFT = 260         # насколько полоса может съехать по высоте
LIMIT = 4.0         # процент отличающихся пикселей, выше которого полоса — расхождение

# Действие на странице перед снимком: открыть результат квиза.
QUIZ = """
var d = f.contentDocument;
var sel = d.getElementById('quizDirection');
if (sel) {
  sel.selectedIndex = 1; sel.dispatchEvent(new Event('change'));
  var cap = d.getElementById('quizCapacity'); cap.selectedIndex = 1; cap.dispatchEvent(new Event('change'));
  d.getElementById('quizSubmit').click();
}
"""

HARNESS = """<!doctype html><meta charset="utf-8"><title>cmp</title>
<style>html,body{margin:0;background:#fff}iframe{border:0;display:block}</style>
<body><script>
var q = new URLSearchParams(location.search);
var f = document.createElement('iframe');
f.width = q.get('w'); f.height = 900;
f.onload = function () {
  setTimeout(function () {
    %(action)s
    setTimeout(function () {
      var d = f.contentDocument;
      // Анимации появления и ленивые картинки на снимке не нужны
      var st = d.createElement('style');
      // Первый экран занимает высоту окна (100vh), а окно на снимке — вся страница:
      // без ограничения он растягивается на тысячи пикселей.
      st.textContent = '*{animation:none!important;transition:none!important}.reveal{opacity:1!important;transform:none!important}.hero{min-height:900px!important;height:auto!important}';
      d.documentElement.style.setProperty('--vh', '9px');
      d.head.appendChild(st);
      [].forEach.call(d.querySelectorAll('img[loading=lazy]'), function (i) { i.loading = 'eager'; });
      var h = Math.min(d.documentElement.scrollHeight, 16000);
      f.height = h;
      document.title = 'READY';
    }, 700);
  }, 800);
};
f.src = q.get('page');
document.body.appendChild(f);
</script>
"""


# ------------------------------------------------------------------ текст

def visible_lines(markup):
    """Видимый текст внутри <main>: по строке на блок, без служебных узлов."""
    main = re.search(r'<main[^>]*>(.*)</main>', markup, re.S)
    body = main.group(1) if main else markup
    body = re.sub(r'<(script|style|template|svg)[^>]*>.*?</\1>', ' ', body, flags=re.S | re.I)
    body = re.sub(r'<!--.*?-->', ' ', body, flags=re.S)
    # блочные теги — это переносы строк, строчные — просто склейка
    body = re.sub(r'</?(p|li|h[1-6]|div|section|article|dt|dd|tr|td|th|label|option|button|a|span|strong|summary)\b[^>]*>',
                  '\n', body, flags=re.I)
    body = re.sub(r'<[^>]+>', ' ', body)
    text = html.unescape(body)
    text = text.replace('\u00a0', ' ').replace('\u00ad', '').replace('\u2011', '-').replace('\u202f', ' ')
    lines = []
    for line in text.split('\n'):
        line = re.sub(r'\s+', ' ', line).strip()
        if line:
            lines.append(line)
    return lines


def compare_text(name, static_html, wp_html):
    a = visible_lines(static_html)
    b = visible_lines(wp_html)
    missing = [line for line in dict.fromkeys(a) if line not in b]
    extra = [line for line in dict.fromkeys(b) if line not in a]
    return missing, extra


# ------------------------------------------------------------------ вид

def snapshot(page_uri, width, out, action=''):
    folder = pathlib.Path(out).parent
    harness = folder / '_cmp.html'
    harness.write_text(HARNESS % {'action': action}, encoding='utf-8')
    url = '%s?page=%s&w=%d' % (harness.as_uri(), page_uri, width)
    cmd = [CHROME, '--headless', '--disable-gpu', '--allow-file-access-from-files',
           '--hide-scrollbars', '--window-size=%d,16000' % (width + 20),
           '--virtual-time-budget=15000', '--screenshot=%s' % out, url]
    subprocess.run(cmd, capture_output=True, timeout=240)
    img = Image.open(out).convert('RGB')
    # обрезаем пустой хвост окна: сравниваем только саму страницу
    bbox = ImageChops.difference(img, Image.new('RGB', img.size, (255, 255, 255))).getbbox()
    if bbox:
        img = img.crop((0, 0, width, bbox[3]))
    img.save(out)
    return img


def compare_images(a, b, name, width, folder):
    """Полосы, которые не нашли себе пары даже со сдвигом."""
    bad = []
    h = min(a.size[1], b.size[1])
    for top in range(0, h - BAND, BAND):
        strip = a.crop((0, top, width, top + BAND))

        def score(shift):
            y = top + shift
            if y < 0 or y + BAND > b.size[1]:
                return 100.0
            other = b.crop((0, y, width, y + BAND))
            diff = ImageChops.difference(strip, other).convert('L')
            return sum(diff.histogram()[40:]) * 100 / (width * BAND)

        # Сначала грубо шагом 6 px, потом точно до пикселя вокруг лучшего:
        # сдвиг на 2–3 px иначе выглядел бы как сломанная вёрстка.
        coarse = min(range(-SHIFT, SHIFT + 1, 6), key=score)
        best = min(score(s) for s in range(coarse - 6, coarse + 7))
        if best > LIMIT:
            bad.append((top, round(best, 1)))
            pair = Image.new('RGB', (width * 2 + 10, BAND), (255, 0, 0))
            pair.paste(strip, (0, 0))
            pair.paste(b.crop((0, top, width, min(top + BAND, b.size[1]))), (width + 10, 0))
            pair.save(os.path.join(folder, '%s-%d-%d.png' % (re.sub(r'\W+', '_', name), width, top)))
    if abs(a.size[1] - b.size[1]) > SHIFT:
        bad.append(('высота', '%d против %d' % (a.size[1], b.size[1])))
    return bad


# ------------------------------------------------------------------ запуск

def main():
    if not CHROME:
        sys.exit('Chrome не найден — сравнивать нечем.')
    only = sys.argv[1:] or list(PAGES)
    folder = tempfile.mkdtemp(prefix='tts-compare-')
    say('картинки расхождений: ' + folder)
    problems = 0

    for name in only:
        static_rel, wp_path = PAGES[name]
        static_file = STATIC / static_rel
        static_html = io.open(static_file, encoding='utf-8').read()
        wp_html = fetch(SITE_URL + wp_path)
        # WordPress-страницу кладём рядом, чтобы открыть её в той же рамке
        wp_file = pathlib.Path(folder) / ('wp-' + re.sub(r'\W+', '_', name) + '.html')
        # Стили, скрипты и шрифты темы — те же файлы, что site/assets, поэтому
        # берём их локально: шрифты с сервера в локальный файл не загрузятся
        # (браузер требует для них тот же адрес), и снимок был бы с чужим шрифтом.
        local = (ROOT / 'site' / 'assets').resolve().as_uri() + '/'
        pattern = re.escape(SITE_URL + '/wp-content/themes/tts/assets/') + r"""([^"'?\s]+)(\?ver=[^"']*)?"""
        page = re.sub(pattern, lambda m: local + m.group(1), wp_html)
        wp_file.write_text(page.replace('loading="lazy"', 'loading="eager"'), encoding='utf-8')

        say('\n=== %s ===' % name)
        missing, extra = compare_text(name, static_html, wp_html)
        if missing:
            problems += len(missing)
            say('  нет в WordPress (%d):' % len(missing))
            for line in missing[:15]:
                say('    - ' + line[:110])
        if extra:
            say('  есть только в WordPress (%d):' % len(extra))
            for line in extra[:15]:
                say('    + ' + line[:110])
        if not missing and not extra:
            say('  текст совпадает')

        states = [('', '')]
        if name == 'главная':
            states.append((QUIZ, ' + результат квиза'))
        for width in WIDTHS:
            for action, label in states:
                a = snapshot(static_file.resolve().as_uri(), width,
                             os.path.join(folder, 's-%s-%d%s.png' % (re.sub(r'\W+', '_', name), width, '-q' if action else '')), action)
                b = snapshot(wp_file.resolve().as_uri(), width,
                             os.path.join(folder, 'w-%s-%d%s.png' % (re.sub(r'\W+', '_', name), width, '-q' if action else '')), action)
                bad = compare_images(a, b, name + ('-квиз' if action else ''), width, folder)
                if bad:
                    problems += len(bad)
                    say('  %d px%s: расходится %s' % (width, label, ', '.join(
                        ('y=%s (%s%%)' % (top, pct)) if top != 'высота' else ('высота %s' % pct)
                        for top, pct in bad[:10])))
                else:
                    say('  %d px%s: вид совпадает' % (width, label))

    say('\nрасхождений всего: %d' % problems)
    say('картинки расхождений: ' + folder)


if __name__ == '__main__':
    main()
