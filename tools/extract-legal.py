"""Текст юридических документов из docx заказчика — в структуру для генератора.

Оригинал лежит в source/legal/ как есть. Скрипт раскладывает его на блоки
(заголовки, абзацы, списки, таблицы) и пишет source/legal/privacy-policy.json.
Руками текст не перепечатываем: юридический документ должен совпадать с
утверждённым дословно, а при новой редакции достаточно перезапустить скрипт.

Запуск:  python tools/extract-legal.py
"""

import io
import json
import os
import re
import sys
import zipfile

sys.stdout.reconfigure(encoding='utf-8')

ROOT = os.path.dirname(os.path.dirname(os.path.abspath(__file__)))
SRC = os.path.join(ROOT, 'source', 'legal', 'tts_kz_privacy_policy_full.docx')
OUT = os.path.join(ROOT, 'source', 'legal', 'privacy-policy.json')

STYLE = {'Heading1': 'h2', 'Heading2': 'h3'}


def runs(p):
    """Прогоны текста абзаца: [[текст, жирный], ...], соседние одинаковые склеены."""
    out = []
    for r in re.findall(r'<w:r[ >].*?</w:r>', p, re.S):
        text = ''.join(re.findall(r'<w:t[^>]*>([^<]*)</w:t>', r))
        if not text:
            continue
        text = (text.replace('&amp;', '&').replace('&lt;', '<')
                .replace('&gt;', '>').replace('&quot;', '"').replace('&apos;', "'"))
        bold = bool(re.search(r'<w:b/>|<w:b w:val="(1|true)"/>', r))
        if out and out[-1][1] == bold:
            out[-1][0] += text
        else:
            out.append([text, bold])
    return out


def plain(rs):
    return ''.join(t for t, _ in rs).strip()


def main():
    xml = zipfile.ZipFile(SRC).read('word/document.xml').decode('utf-8')
    body = re.search(r'<w:body>(.*)</w:body>', xml, re.S).group(1)
    blocks = []
    for m in re.finditer(r'<w:tbl>.*?</w:tbl>|<w:p[ >].*?</w:p>|<w:p/>', body, re.S):
        chunk = m.group(0)
        if chunk.startswith('<w:tbl>'):
            rows = []
            for tr in re.findall(r'<w:tr[ >].*?</w:tr>', chunk, re.S):
                rows.append([' '.join(plain(runs(p)) for p in re.findall(r'<w:p[ >].*?</w:p>', tc, re.S)).strip()
                             for tc in re.findall(r'<w:tc>.*?</w:tc>', tr, re.S)])
            blocks.append({'type': 'table', 'head': rows[0], 'rows': rows[1:]})
            continue
        rs = runs(chunk)
        if not plain(rs):
            continue
        style = re.search(r'<w:pStyle w:val="([^"]+)"', chunk)
        style = style.group(1) if style else ''
        if style in STYLE:
            blocks.append({'type': STYLE[style], 'text': plain(rs)})
        elif '<w:numPr>' in chunk or style == 'ListParagraph':
            if blocks and blocks[-1]['type'] == 'ul':
                blocks[-1]['items'].append(rs)
            else:
                blocks.append({'type': 'ul', 'items': [rs]})
        else:
            blocks.append({'type': 'p', 'runs': rs})

    # первый абзац — название документа, на странице оно стоит в H1
    title = plain(blocks.pop(0)['runs'])
    split = next(i for i, b in enumerate(blocks) if b['type'] == 'h2' and b['text'].startswith('Приложение'))
    data = {'title': title, 'policy': blocks[:split],
            'appendix_title': blocks[split]['text'], 'appendix': blocks[split + 1:]}
    io.open(OUT, 'w', encoding='utf-8').write(json.dumps(data, ensure_ascii=False, indent=1))
    kinds = {}
    for b in blocks:
        kinds[b['type']] = kinds.get(b['type'], 0) + 1
    print('название:', title)
    print('блоков:', kinds, '→', os.path.relpath(OUT, ROOT))


if __name__ == '__main__':
    main()
