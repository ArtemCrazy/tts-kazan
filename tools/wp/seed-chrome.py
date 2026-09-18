"""Меню шапки и подвала и «Настройки сайта» — как в статической версии.

Шапка и подвал в WordPress редактируются в админке: пункты меню —
«Внешний вид → Меню», тексты и логотипы — «Настройки сайта». Скрипт
заводит их с теми же текстами, что в статике.

Повторный запуск ничего не перезаписывает: меню, которое уже есть,
и заполненные поля настроек не трогаются — их мог поправить редактор.

Запуск:  python tools/wp/seed-chrome.py            — создать недостающее
         python tools/wp/seed-chrome.py --force    — пересоздать меню и настройки
"""

import html
import io
import json
import os
import pathlib
import re
import sys

sys.path.insert(0, os.path.dirname(os.path.abspath(__file__)))
from common import say  # noqa: E402

import importlib.util  # noqa: E402

_spec = importlib.util.spec_from_file_location('runphp', os.path.join(os.path.dirname(__file__), 'run-php.py'))
runphp = importlib.util.module_from_spec(_spec)
_spec.loader.exec_module(runphp)

# Пункт: (название, адрес, подпись под пунктом, вложенные пункты, класс).
# Адрес — путь страницы («/catalog/vpi/»), ссылка на блок главной («/#company»)
# или «#» у пункта, который только раскрывает список.
DIRECTIONS = [
    ('Заводы сухих смесей', '/catalog/smartdrymix/', 'SmartDryMix 5–50+ т/ч'),
    ('Бетонные заводы', '/catalog/smartbeton/', 'Товарный бетон, ЖБИ и дороги'),
    ('Заводы ВПИ', '/catalog/vpi/', 'Готовые линии QUNFENG + ТТС'),
    ('Цементные терминалы', '/catalog/smartstock/', 'SmartStock 1000–5000 тонн'),
    ('Пневмокамерные насосы', '/catalog/pkn/', 'ПКН 10–60 т/ч, подача до 250 м'),
]

MENUS = {
    'header': ('Шапка сайта', [
        ('Оборудование', '/#catalog', '', []),
        ('Каталог', '#', '', [
            *[(title, url, note, []) for title, url, note in DIRECTIONS],
            ('Весь каталог', '/catalog/', '23 позиции, поиск и фильтры', [], 'dropdown__link--all'),
        ]),
        ('Сервис', '#', '', [
            ('Инженерный сервис', '/service/', 'Диагностика, запуск и поддержка 24/7', []),
            ('Запасные части', '/parts/', 'Подбор комплектующих и автоматики', []),
        ]),
        ('О компании', '/#company', '', []),
        ('Проекты', '/#projects', '', []),
    ]),
    'footer-1': ('Оборудование', [(title, url, '', []) for title, url, _ in DIRECTIONS]),
    'footer-2': ('Компания', [
        ('О компании', '/#company', '', []),
        ('Проекты', '/#projects', '', []),
        ('Инженерный сервис', '/service/', '', []),
        ('Запасные части', '/parts/', '', []),
    ]),
    'footer-3': ('Документы', [
        ('Политика конфиденциальности', '/privacy-policy/', '', []),
        ('Согласие на обработку персональных данных', '/personal-data/', '', []),
        ('Политика использования файлов cookie', '/cookie/', '', []),
    ]),
}

SETTINGS = {
    'tts_settings_brand_name': 'ТТС Инжиниринг',
    'tts_settings_brand_region': 'Казахстан',
    'tts_settings_topline_left': 'Инжиниринг для строительной индустрии Казахстана с 2006 года',
    'tts_settings_topline_right': 'Поставка, монтаж и сервис по Казахстану',
    'tts_settings_header_button': 'Получить расчёт',
    'tts_settings_footer_about': 'Заводы, терминалы и технологические линии для строительной индустрии Казахстана.',
    'tts_settings_footer_legal': 'БИН 191141028147',
    'tts_settings_footer_catalog_title': 'Весь каталог оборудования',
    'tts_settings_footer_catalog_note': '23 конфигурации в пяти направлениях — с поиском и фильтрами.',
    'tts_settings_footer_catalog_button': 'Открыть весь каталог',
    'tts_settings_footer_home_title': 'Вернуться на главную',
    'tts_settings_footer_home_note': 'Подбор оборудования за две минуты, направления и проекты в Казахстане.',
    'tts_settings_footer_home_button': 'На главную',
    'tts_settings_copyright': 'ТТС Инжиниринг Казахстан',
}

# Страница WordPress -> файл статики: у страниц своя подпись справа в верхней строке.
STATIC = pathlib.Path(__file__).resolve().parents[2] / 'site' / '4'
PAGES = {
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


def toplines():
    """Подписи справа, которые отличаются от общей (она — как на главной)."""
    out = {}
    for path, rel in PAGES.items():
        markup = io.open(STATIC / rel, encoding='utf-8').read()
        found = re.search(r'topline__item--muted">([^<]*)<', markup)
        # неразрывные пробелы в статике ставил типограф, в теме он их ставит сам
        text = re.sub(r'\s+', ' ', html.unescape(found.group(1)).replace('\xa0', ' ')).strip() if found else ''
        if text and text != SETTINGS['tts_settings_topline_right']:
            out[path] = text
    return out


CODE = r'''
$plan  = json_decode(base64_decode('%(plan)s'), true);
$force = %(force)s;

/** Пункт меню: страница сайта — ссылкой на страницу, остальное — произвольной ссылкой. */
function tts_seed_item(int $menu, array $row, int $parent, int $order): int {
    list($title, $url, $note, $children) = $row;
    $class = $row[4] ?? '';
    $args  = array(
        'menu-item-title'     => $title,
        'menu-item-status'    => 'publish',
        'menu-item-parent-id' => $parent,
        'menu-item-position'  => $order,
        'menu-item-classes'   => $class,
    );
    $page = ($url !== '#' && strpos($url, '#') === false) ? get_page_by_path(trim($url, '/')) : null;
    if ($page) {
        $args += array('menu-item-type' => 'post_type', 'menu-item-object' => 'page',
                       'menu-item-object-id' => $page->ID);
    } else {
        $args += array('menu-item-type' => 'custom', 'menu-item-url' => $url);
    }
    $id = wp_update_nav_menu_item($menu, 0, $args);
    if (is_wp_error($id)) { echo '  ошибка: ', $title, ' — ', $id->get_error_message(), "\n"; return 0; }
    if ($note) update_field('field_tts_menu_note', $note, $id);
    foreach ($children as $i => $child) tts_seed_item($menu, $child, $id, $order * 100 + $i + 1);
    return $id;
}

$locations = get_nav_menu_locations();
foreach ($plan['menus'] as $location => list($name, $items)) {
    $current = $locations[$location] ?? 0;
    if ($current && wp_get_nav_menu_object($current) && !$force) {
        echo $location, ': меню уже есть, не трогаю', "\n";
        continue;
    }
    if ($current && $force) wp_delete_nav_menu($current);
    $old = wp_get_nav_menu_object($name);
    if ($old) wp_delete_nav_menu($old->term_id);
    $menu = wp_create_nav_menu($name);
    if (is_wp_error($menu)) { echo $location, ': ', $menu->get_error_message(), "\n"; continue; }
    foreach ($items as $i => $row) tts_seed_item($menu, $row, 0, $i + 1);
    $locations[$location] = $menu;
    echo $location, ': «', $name, '», пунктов ', count(wp_get_nav_menu_items($menu)), "\n";
}
set_theme_mod('nav_menu_locations', $locations);

$filled = 0;
foreach ($plan['settings'] as $field => $value) {
    if (!$force && trim((string) get_field($field, 'option')) !== '') continue;
    update_field($field, $value, 'option');
    $filled++;
}
echo 'настроек заполнено: ', $filled, "\n";

$pages = 0;
foreach ($plan['toplines'] as $path => $text) {
    $page = get_page_by_path($path);
    if (!$page) { echo $path, ': страницы нет', "\n"; continue; }
    if (!$force && trim((string) get_field('tts_page_topline', $page->ID)) !== '') continue;
    update_field('field_tts_page_topline', $text, $page->ID);
    $pages++;
}
echo 'подписей страниц в верхней строке: ', $pages, "\n";
'''


def main():
    import base64
    force = '--force' in sys.argv
    plan = json.dumps({'menus': MENUS, 'settings': SETTINGS, 'toplines': toplines()}, ensure_ascii=False)
    code = CODE % {
        'plan': base64.b64encode(plan.encode('utf-8')).decode('ascii'),
        'force': 'true' if force else 'false',
    }
    say(runphp.run(code).strip())


if __name__ == '__main__':
    main()
