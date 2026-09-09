"""Достаёт рендеры оборудования из брошюр клиента в ассеты сайта.

Брошюры (ЗССС, БСУ, ВПИ) присланы клиентом в переписку и лежат вне репозитория —
в `.claude/context/media/`, потому что папка агента на прод и в git не уходит.
Здесь описано, какой рендер из какой брошюры взят: если клиент пришлёт новую
версию буклета, достаточно поменять номер объекта и перезапустить скрипт.

В тёмной брошюре ВПИ рендеры лежат с готовой маской прозрачности — её и берём,
края получаются ровно такими, как задумал дизайнер брошюры. В светлых брошюрах
(ЗССС и БСУ) маска пустая, фон белый залит в саму картинку: там фон снимается
заливкой от краёв, а не порогом яркости — силосы сами почти белые, порог бы
продырявил их насквозь.

Запуск:  python tools/extract-renders.py
"""

import io
import os
import sys

import pymupdf
from PIL import Image, ImageDraw, ImageFilter

sys.stdout.reconfigure(encoding='utf-8')

ROOT = os.path.dirname(os.path.dirname(os.path.abspath(__file__)))
SRC = os.path.join(ROOT, '.claude', 'context', 'media', '2026-09-02')
IMG = os.path.join(ROOT, 'site', 'assets', 'img')
LOOSE_SRC = os.path.join(ROOT, 'source', 'renders')

BROCHURE = {
    'zsss': '1611-document.pdf',   # Брошюра ЗССС
    'bsu': '1612-document.pdf',    # Брошюра БСУ
    'vpi': '1613-document.pdf',    # Брошюра ВПИ
}

# имя ассета · брошюра · номер объекта в PDF · что на рендере
RENDERS = [
    ('smartdrymix-5',   'zsss', 4913, 'SmartDryMix 5 G-L, 5 т/ч'),
    ('smartdrymix-20',  'zsss', 4909, 'SmartDryMix 20 C-L, 20 т/ч'),
    ('smartdrymix-50',  'zsss', 4897, 'SmartDryMix 50 GC-T, 50 т/ч'),
    ('smartbeton-60',   'bsu',   119, 'SmartBeton 60, до 50 м³/ч'),
    ('smartbeton-90',   'bsu',   123, 'SmartBeton 90, до 75 м³/ч'),
    ('smartbeton-120',  'bsu',   115, 'SmartBeton 120, до 90 м³/ч'),
    ('smartbeton-135',  'bsu',   135, 'SmartBeton 135, до 100 м³/ч'),
    ('smartbeton-30s',  'vpi',   343, 'SmartBeton 30 S для линии ВПИ'),
    ('smartbeton-60s',  'vpi',   363, 'SmartBeton 60 S для линии ВПИ'),
    ('smartbeton-90s',  'vpi',   227, 'SmartBeton 90 S для линии ВПИ'),
    ('pkn-pump',        'bsu',   111, 'Пневмокамерный насос'),
    ('equipment-silos', 'bsu',   103, 'Силосы для сыпучих материалов'),
]

# Рендеры, присланные не в брошюре, а отдельными картинками (таблица правок).
# Оригиналы лежат в source/renders/, здесь только имя файла и что на нём.
LOOSE = [
    ('smartstock-1000', 'smartstock-1000.jpg', 'Цементный терминал на 1000 тонн'),
    ('smartstock-2000', 'smartstock-2000.jpg', 'Цементный терминал на 2000 тонн'),
    ('smartstock-5000', 'smartstock-5000.jpg', 'Цементный терминал на 5000 тонн'),
]

# Подложка шапок направлений: тот же диагональный градиент, что уже стоит
# под силосами и вибропрессом, — иначе новая шапка выбьется из ряда.
BED = ((0x14, 0x2F, 0x44), (0x1C, 0x3C, 0x55))
BED_SIZE = (1600, 900)

# имя ассета · рендер, который ложится на подложку · доля высоты кадра
BEDS = [
    ('smartdrymix-bg', 'smartdrymix-50', 0.84),
]

# PNG — мастер: остаётся в репозитории на случай, если рендер понадобится крупнее.
# WebP — то, что грузит браузер: карточка показывает рендер шириной около 400 px,
# с запасом на экраны с двойной плотностью хватает 900.
MAX_SIDE = 1400
MAX_WEB = 900


def drop_white(im):
    """Снимает белый фон заливкой от краёв: фон связный, внутрь объекта не пройдёт."""
    w, h = im.size
    seeds = [(x, 0) for x in range(0, w, 24)] + [(x, h - 1) for x in range(0, w, 24)]
    seeds += [(0, y) for y in range(0, h, 24)] + [(w - 1, y) for y in range(0, h, 24)]
    for seed in seeds:
        if im.getpixel(seed)[3] == 0:
            continue
        ImageDraw.floodfill(im, seed, (0, 0, 0, 0), thresh=32)

    # заливка режет край ступенькой — смягчаем ровно на пиксель, чтобы
    # рендер не выглядел вырезанным ножницами
    alpha = im.getchannel('A').filter(ImageFilter.GaussianBlur(0.6))
    im.putalpha(alpha)
    return im


def trim(im):
    """Обрезать прозрачные поля по краям."""
    box = im.getbbox()
    return im.crop(box) if box else im


def cutout(doc, xref):
    """Рендер без фона: маской брошюры, если она есть, иначе заливкой от краёв."""
    pix = pymupdf.Pixmap(doc, xref)
    smask = {img[0]: img[1] for img in doc[0].get_images(full=True)}.get(xref)
    if smask:
        pix = pymupdf.Pixmap(pix, pymupdf.Pixmap(doc, smask))
    im = Image.open(io.BytesIO(pix.tobytes('png'))).convert('RGBA')
    if im.getchannel('A').getextrema()[0] == 255:
        im = drop_white(im)
    return trim(im)


def save(im, name, alpha=True, max_side=MAX_SIDE, max_web=MAX_WEB):
    """PNG для запаса качества, WebP — то, что реально грузит браузер."""
    im.thumbnail((max_side, max_side), Image.LANCZOS)
    web = im.copy()
    web.thumbnail((max_web, max_web), Image.LANCZOS)
    if alpha:
        png = os.path.join(IMG, name + '.png')
        im.save(png, optimize=True)
        webp = os.path.join(IMG, name + '.webp')
        web.save(webp, quality=86, method=6)
        out = [png, webp]
    else:
        jpg = os.path.join(IMG, name + '.jpg')
        im.convert('RGB').save(jpg, quality=86, optimize=True, progressive=True)
        webp = os.path.join(IMG, name + '.webp')
        web.convert('RGB').save(webp, quality=84, method=6)
        out = [jpg, webp]
    sizes = ' · '.join('%s %d КБ' % (os.path.splitext(p)[1][1:], os.path.getsize(p) // 1024)
                       for p in out)
    print('   %-18s мастер %4dx%-4d  веб %4dx%-4d  %s'
          % (name, im.width, im.height, web.width, web.height, sizes))


def bed(fill_from, height_share):
    """Рендер на фирменной подложке — кадр для шапки направления."""
    w, h = BED_SIZE
    canvas = Image.new('RGB', (w, h))
    top, bottom = BED
    for y in range(h):
        for_row = y / (h - 1)
        canvas.paste(tuple(int(top[i] + (bottom[i] - top[i]) * for_row) for i in range(3)),
                     (0, y, w, y + 1))

    art = Image.open(os.path.join(IMG, fill_from + '.png')).convert('RGBA')
    target_h = int(h * height_share)
    art = art.resize((max(1, int(art.width * target_h / art.height)), target_h), Image.LANCZOS)
    if art.width > w:
        art = art.resize((w, max(1, int(art.height * w / art.width))), Image.LANCZOS)
    canvas.paste(art, ((w - art.width) // 2, h - art.height), art)
    return canvas


def main():
    if not os.path.isdir(SRC):
        sys.exit('Брошюр нет на месте: %s\n'
                 'Они приходят из переписки и в репозиторий не попадают.' % SRC)

    print('Рендеры из брошюр:')
    docs = {}
    for name, key, xref, note in RENDERS:
        if key not in docs:
            docs[key] = pymupdf.open(os.path.join(SRC, BROCHURE[key]))
        save(cutout(docs[key], xref), name)
    for doc in docs.values():
        doc.close()

    # Шапка растягивается на всю ширину экрана, поэтому её не ужимаем
    # до карточного размера — оставляем как у остальных направлений.
    print('Отдельные рендеры от клиента:')
    for name, fname, note in LOOSE:
        path = os.path.join(LOOSE_SRC, fname)
        if not os.path.exists(path):
            print('   %-18s нет исходника: %s' % (name, fname))
            continue
        save(trim(drop_white(Image.open(path).convert('RGBA'))), name)

    print('Кадры для шапок:')
    for name, source, share in BEDS:
        save(bed(source, share), name, alpha=False,
             max_side=BED_SIZE[0], max_web=BED_SIZE[0])


if __name__ == '__main__':
    main()
