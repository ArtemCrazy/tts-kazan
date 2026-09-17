"""Страницы сайта в WordPress: структура адресов и юридические тексты.

Адреса берём из п. 4.1 ТЗ. Юридические страницы заполняем утверждённым
текстом заказчика (source/legal/privacy-policy.json) — но не одним куском
HTML, а обычными блоками редактора: заголовками, абзацами, списками и
таблицами. Так клиент правит документ штатно, как требует п. 12.1 ТЗ.

Скрипт можно запускать повторно: существующие страницы находит по адресу
и обновляет, а не создаёт вторые.

Запуск:  python tools/wp/seed-pages.py
"""

import html
import io
import json
import re
import os
import secrets
import sys

sys.path.insert(0, os.path.dirname(os.path.abspath(__file__)))
from common import Remote, fetch, say, SITE_SUBDIR, SITE_URL  # noqa: E402

ROOT = os.path.dirname(os.path.dirname(os.path.dirname(os.path.abspath(__file__))))
LEGAL = os.path.join(ROOT, 'source', 'legal', 'privacy-policy.json')

RUNNER = '''<?php
// Одноразовый создатель страниц. Удаляется сразу после работы.
header('Content-Type: text/plain; charset=utf-8');
if (($_GET['token'] ?? '') !== '%(token)s') { http_response_code(403); exit('нет токена'); }
require __DIR__ . '/wp-load.php';

$plan = json_decode(file_get_contents(__DIR__ . '/%(payload)s'), true);
$ids = array();

foreach ($plan['pages'] as $page) {
    $parent = $page['parent'] ? ($ids[$page['parent']] ?? 0) : 0;
    $existing = get_page_by_path(
        $page['parent'] ? $page['parent'] . '/' . $page['slug'] : $page['slug']
    );
    $data = array(
        'post_type'    => 'page',
        'post_title'   => $page['title'],
        'post_name'    => $page['slug'],
        'post_status'  => 'publish',
        'post_parent'  => $parent,
        'menu_order'   => $page['order'],
        'post_content' => $page['content'],
    );
    if ($existing) {
        $data['ID'] = $existing->ID;
        // Содержимое перезаписываем только там, где оно наше (юридические тексты),
        // чтобы не стереть правки редактора на других страницах.
        if (!$page['content']) unset($data['post_content']);
        $id = wp_update_post($data, true);
        $verb = 'обновлена';
    } else {
        $id = wp_insert_post($data, true);
        $verb = 'создана';
    }
    if (is_wp_error($id)) { echo $page['slug'], ': ошибка — ', $id->get_error_message(), "\\n"; continue; }
    $ids[$page['key']] = $id;
    if (!empty($page['lead'])) update_post_meta($id, 'tts_lead', $page['lead']);
    if (!empty($page['kicker'])) update_post_meta($id, 'tts_kicker', $page['kicker']);
    echo $page['slug'], ': ', $verb, ' (', get_permalink($id), ")\\n";
}

// Главная страница сайта и страница записей
if (!empty($ids['home'])) {
    update_option('show_on_front', 'page');
    update_option('page_on_front', $ids['home']);
    echo "главная страница назначена\\n";
}

// Человекочитаемые адреса (п. 14 ТЗ)
if (get_option('permalink_structure') !== '/%%postname%%/') {
    update_option('permalink_structure', '/%%postname%%/');
    flush_rewrite_rules();
    echo "включены человекочитаемые адреса\\n";
}

// Демо-контент установки в корзину: это не удаление данных клиента.
foreach (array('sample-page', 'privet-mir', 'hello-world') as $slug) {
    $demo = get_page_by_path($slug, OBJECT, array('page', 'post'));
    if ($demo && 'trash' !== $demo->post_status) { wp_trash_post($demo->ID); echo "демо «$slug» убрано в корзину\\n"; }
}

echo 'страниц всего: ', count($ids), "\\n";
'''


# ------------------------------------------------------------------ блоки

def esc(text):
    return html.escape(text, quote=False)


def inline(runs):
    return ''.join(f'<strong>{esc(t)}</strong>' if bold else esc(t) for t, bold in runs)


def blocks(items):
    """Блоки Gutenberg из разобранного юридического документа."""
    out = []
    for item in items:
        kind = item['type']
        if kind in ('h2', 'h3'):
            level = 2 if kind == 'h2' else 3
            out.append(f'<!-- wp:heading {{"level":{level}}} -->\n'
                       f'<h{level} class="wp-block-heading">{esc(item["text"])}</h{level}>\n'
                       f'<!-- /wp:heading -->')
        elif kind == 'p':
            out.append(f'<!-- wp:paragraph -->\n<p>{inline(item["runs"])}</p>\n<!-- /wp:paragraph -->')
        elif kind == 'ul':
            rows = '\n'.join(f'<!-- wp:list-item -->\n<li>{inline(rs)}</li>\n<!-- /wp:list-item -->'
                             for rs in item['items'])
            out.append('<!-- wp:list -->\n<ul class="wp-block-list">\n' + rows + '\n</ul>\n<!-- /wp:list -->')
        elif kind == 'table':
            head = ''.join(f'<th>{esc(h)}</th>' for h in item['head'])
            body = '\n'.join(
                '<tr>' + ''.join(f'<td>{esc(c)}</td>' for c in row) + '</tr>'
                for row in item['rows'])
            out.append('<!-- wp:table -->\n<figure class="wp-block-table"><table>'
                       f'<thead><tr>{head}</tr></thead><tbody>\n{body}\n</tbody></table></figure>\n'
                       '<!-- /wp:table -->')
    return '\n\n'.join(out)


def cookie_part(policy):
    """Раздел 12 политики — текст страницы про cookie."""
    out, inside = [], False
    for item in policy:
        if item['type'] == 'h2':
            inside = item['text'].startswith('12.')
        if inside:
            out.append(item)
    return out


def consent_part(appendix):
    """В приложении пункты «1.» и «2.» — это разделы, «1.1.» и дальше — подразделы."""
    fixed = []
    for item in appendix:
        # «1.» и «2.» — разделы, а «1.1.» и дальше — подразделы внутри них
        top = item['type'] == 'h3' and re.match(r'^\d+\.\s', item['text'])
        fixed.append(dict(item, type='h2') if top else item)
    return fixed


def plan():
    legal = json.load(io.open(LEGAL, encoding='utf-8'))
    pages = [
        ('home', '', 'Главная', 0, '', '', ''),
        ('catalog', '', 'Каталог оборудования', 10, '', 'Каталог',
         'Полный список конфигураций ТТС с поиском и фильтрами по назначению и масштабу.'),
        ('smartdrymix', 'catalog', 'Заводы сухих смесей SmartDryMix', 11, '', 'Направление', ''),
        ('smartbeton', 'catalog', 'Бетонные заводы SmartBeton', 12, '', 'Направление', ''),
        ('vpi', 'catalog', 'Заводы ВПИ QUNFENG + ТТС', 13, '', 'Направление', ''),
        ('smartstock', 'catalog', 'Цементные терминалы SmartStock', 14, '', 'Направление', ''),
        ('pkn', 'catalog', 'Пневмокамерные насосы', 15, '', 'Направление', ''),
        ('service', '', 'Инженерный сервис', 20, '', 'Сервис', ''),
        ('parts', '', 'Запасные части и автоматика', 21, '', 'Сервис', ''),
        ('privacy', '', 'Политика конфиденциальности и обработки персональных данных', 30,
         blocks(legal['policy']), 'Документы',
         'Как Филиал ООО «ТТС Инжиниринг» в Республике Казахстан собирает, обрабатывает '
         'и защищает персональные данные.'),
        ('personal', '', 'Согласие на обработку персональных данных', 31,
         blocks(consent_part(legal['appendix'])), 'Документы',
         'Приложение № 1 к Политике конфиденциальности: согласие, которое посетитель '
         'даёт отметкой при отправке формы на сайте.'),
        ('cookie', '', 'Политика использования файлов cookie', 32,
         blocks(cookie_part(legal['policy'])), 'Документы',
         'Раздел 12 Политики конфиденциальности: какие технологии использует сайт '
         'и как управлять согласием.'),
    ]
    slugs = {'home': 'home', 'catalog': 'catalog', 'smartdrymix': 'smartdrymix',
             'smartbeton': 'smartbeton', 'vpi': 'vpi', 'smartstock': 'smartstock',
             'pkn': 'pkn', 'service': 'service', 'parts': 'parts',
             'privacy': 'privacy-policy', 'personal': 'personal-data', 'cookie': 'cookie'}
    return {'pages': [
        {'key': key, 'slug': slugs[key], 'parent': parent, 'title': title,
         'order': order, 'content': content, 'kicker': kicker, 'lead': lead}
        for key, parent, title, order, content, kicker, lead in pages
    ]}


def main():
    data = plan()
    payload = 'pages-' + secrets.token_hex(4) + '.json'
    token = secrets.token_hex(8)
    runner = f'pages-{token}.php'

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
