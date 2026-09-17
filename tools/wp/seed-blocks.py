"""Наполнение страниц блоками редактора.

Блоки Gutenberg — это разметка в содержимом страницы, поэтому собрать её
скриптом можно только один раз, а дальше страницу ведёт редактор. Скрипт
ставит блоки и их значения, но НЕ перезаписывает страницу, если её уже
правили руками (сверяемся по метке tts_seeded).

Картинки ищутся в assets темы и попадают в медиатеку, чтобы редактор мог
их заменить обычным способом.

Состав блоков каждой страницы описан отдельным модулем в tools/wp/seed:
в модуле есть PAGE (адрес страницы) и функция blocks().

Запуск:  python tools/wp/seed-blocks.py            — наполнить, что пусто
         python tools/wp/seed-blocks.py --force    — перезаписать страницы
"""

import json
import os
import secrets
import sys

sys.path.insert(0, os.path.dirname(os.path.abspath(__file__)))
from common import Remote, fetch, say, SITE_SUBDIR, SITE_URL  # noqa: E402

RUNNER = r'''<?php
// Одноразовое наполнение страниц блоками. Удаляется сразу после работы.
header('Content-Type: text/plain; charset=utf-8');
if (($_GET['token'] ?? '') !== '%(token)s') { http_response_code(403); exit('нет токена'); }
require __DIR__ . '/wp-load.php';
require_once ABSPATH . 'wp-admin/includes/image.php';

$plan  = json_decode(file_get_contents(__DIR__ . '/%(payload)s'), true);
$force = %(force)s;

/** Картинка из assets темы в медиатеку (тот же приём, что в переносе контента). */
function tts_seed_image(string $rel): int {
    $found = get_posts(array('post_type' => 'attachment', 'post_status' => 'inherit',
        'meta_key' => '_tts_source', 'meta_value' => $rel, 'numberposts' => 1, 'fields' => 'ids'));
    if ($found) return (int) $found[0];
    $src = get_theme_file_path('assets/' . $rel);
    if (!file_exists($src)) return 0;
    $upload = wp_upload_bits(basename($src), null, file_get_contents($src));
    if (!empty($upload['error'])) return 0;
    $id = wp_insert_attachment(array(
        'post_mime_type' => wp_check_filetype($upload['file'])['type'],
        'post_title'     => pathinfo($src, PATHINFO_FILENAME),
        'post_status'    => 'inherit',
    ), $upload['file']);
    if (is_wp_error($id) || !$id) return 0;
    wp_update_attachment_metadata($id, wp_generate_attachment_metadata($id, $upload['file']));
    update_post_meta($id, '_tts_source', $rel);
    return (int) $id;
}

/**
 * Значение поля: картинки описаны как {"image": "img/foo.png"} и превращаются
 * в ID вложения. Если указан alt, он уезжает в медиатеку — иначе описание
 * картинки из статической версии потерялось бы (доступность, п. 15.2 ТЗ).
 */
function tts_seed_value($value) {
    // Направление задаётся слагом термина: в поле должен уехать его номер,
    // иначе блок моделей покажет всё оборудование подряд.
    if (is_array($value) && isset($value['term'])) {
        $term = get_term_by('slug', $value['term'], $value['taxonomy'] ?? 'direction');
        return $term ? (int) $term->term_id : '';
    }
    // Список записей по заголовкам: номера записей заранее неизвестны,
    // а названия моделей в наполнении читаются понятнее.
    if (is_array($value) && isset($value['posts'])) {
        $ids = array();
        foreach ((array) $value['posts'] as $title) {
            $found = get_posts(array(
                'post_type'   => $value['type'] ?? 'equipment',
                'title'       => $title,
                'numberposts' => 1,
                'fields'      => 'ids',
            ));
            if ($found) $ids[] = (int) $found[0];
        }
        return $ids;
    }
    if (!is_array($value) || !isset($value['image'])) return $value;
    $id = tts_seed_image($value['image']);
    if ($id && !empty($value['alt']) && !get_post_meta($id, '_wp_attachment_image_alt', true)) {
        update_post_meta($id, '_wp_attachment_image_alt', $value['alt']);
    }
    return $id;
}

/**
 * Данные блока в том виде, в котором их хранит редактор: значение поля плюс
 * ссылка на его ключ. У повторяющихся полей — количество строк и плоские
 * ключи вида имя_0_подполе.
 */
function tts_block_data(array $fields, string $prefix = ''): array {
    $data = array();
    foreach ($fields as $name => $value) {
        $key = $prefix ? $prefix . '_' . $name : $name;

        // Повторяющееся поле: количество строк плюс плоские ключи по строкам.
        // Вложенность любая — у таблицы сравнения ячейки лежат внутри строки.
        if (is_array($value) && array_is_list($value) && $value && is_array($value[0])) {
            $data[$key] = count($value);
            $data['_' . $key] = 'field_' . $name;
            foreach ($value as $i => $row) {
                $data = array_merge($data, tts_block_data($row, $key . '_' . $i));
            }
            continue;
        }

        $data[$key] = tts_seed_value($value);
        $data['_' . $key] = 'field_' . $name;
    }
    return $data;
}

/** Разметка блока для содержимого страницы. */
function tts_block_markup(array $block): string {
    $attrs = array(
        'name' => 'acf/tts-' . $block['block'],
        'data' => tts_block_data($block['fields'] ?? array()),
        'mode' => 'preview',
    );
    if (!empty($block['anchor'])) $attrs['anchor'] = $block['anchor'];
    return '<!-- wp:acf/tts-' . $block['block'] . ' '
        . wp_json_encode($attrs, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) . ' /-->';
}

foreach ($plan['pages'] as $page) {
    $post = get_page_by_path($page['path']);
    if (!$post) { echo $page['path'], ": страницы нет\n"; continue; }

    $seeded = get_post_meta($post->ID, 'tts_seeded', true);
    if (trim((string) $post->post_content) && !$seeded && !$force) {
        echo $page['path'], ": уже заполнена руками, не трогаю\n";
        continue;
    }

    $markup = array();
    foreach ($page['blocks'] as $block) $markup[] = tts_block_markup($block);

    $done = wp_update_post(array(
        'ID'           => $post->ID,
        'post_content' => implode("\n\n", $markup),
    ), true);
    if (is_wp_error($done)) { echo $page['path'], ': ошибка — ', $done->get_error_message(), "\n"; continue; }
    update_post_meta($post->ID, 'tts_seeded', '1');
    echo $page['path'], ': блоков ', count($markup), ' (', get_permalink($post->ID), ")\n";
}
'''


def pages():
    """Страницы, для которых описано наполнение: модули в tools/wp/seed."""
    import importlib.util

    folder = os.path.join(os.path.dirname(os.path.abspath(__file__)), 'seed')
    found = []
    for name in sorted(os.listdir(folder)):
        if not name.endswith('.py') or name.startswith('_'):
            continue
        spec = importlib.util.spec_from_file_location('seed_' + name[:-3], os.path.join(folder, name))
        module = importlib.util.module_from_spec(spec)
        spec.loader.exec_module(module)
        if not hasattr(module, 'PAGE') or not hasattr(module, 'blocks'):
            continue
        found.append({'path': module.PAGE, 'blocks': module.blocks()})
    return found


def plan():
    return {'pages': pages()}


def main():
    force = '--force' in sys.argv
    data = plan()
    payload = 'seed-' + secrets.token_hex(4) + '.json'
    token = secrets.token_hex(8)
    runner = f'seed-{token}.php'

    r = Remote()
    r.put_text(json.dumps(data, ensure_ascii=False), SITE_SUBDIR, payload)
    r.put_text(RUNNER % {'token': token, 'payload': payload,
                         'force': 'true' if force else 'false'}, SITE_SUBDIR, runner)
    say(fetch(f'{SITE_URL}/{runner}?token={token}').strip())
    for junk in (runner, payload):
        try:
            r.remove(SITE_SUBDIR, junk)
        except FileNotFoundError:
            pass
    r.close()


if __name__ == '__main__':
    main()
