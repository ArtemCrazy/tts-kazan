"""Отчёт по контенту в WordPress: что создалось и заполнились ли поля.

Ничего не меняет — только читает. Нужен, чтобы после переноса убедиться,
что записи не пустые: по количеству записей этого не видно.

Запуск:  python tools/wp/report-content.py
"""

import os
import secrets
import sys

sys.path.insert(0, os.path.dirname(os.path.abspath(__file__)))
from common import Remote, fetch, say, SITE_SUBDIR, SITE_URL  # noqa: E402

REPORT = r'''<?php
header('Content-Type: text/plain; charset=utf-8');
if (($_GET['token'] ?? '') !== '%(token)s') { http_response_code(403); exit('нет токена'); }
require __DIR__ . '/wp-load.php';

foreach (array('equipment' => 'оборудование', 'project' => 'проекты',
               'faq' => 'вопросы', 'service_item' => 'услуги') as $type => $title) {
    $posts = get_posts(array('post_type' => $type, 'numberposts' => -1,
                             'orderby' => 'menu_order', 'order' => 'ASC'));
    echo strtoupper($title), ': ', count($posts), "\n";
}

$terms = get_terms(array('taxonomy' => 'direction', 'hide_empty' => false));
echo "НАПРАВЛЕНИЯ: ", count($terms), ' — ';
foreach ($terms as $t) echo $t->name, ' (', $t->count, ') ';
echo "\n\n";

// Пустые поля у оборудования — главный признак, что перенос не доехал
$empty = array();
foreach (get_posts(array('post_type' => 'equipment', 'numberposts' => -1)) as $post) {
    foreach (array('tts_equipment_code', 'tts_equipment_capacity', 'tts_equipment_status',
                   'tts_equipment_summary', 'tts_equipment_purpose', 'tts_equipment_scale',
                   'tts_equipment_features') as $field) {
        if (!get_field($field, $post->ID)) $empty[] = $post->post_title . ' / ' . $field;
    }
    if (!wp_get_post_terms($post->ID, 'direction', array('fields' => 'ids'))) {
        $empty[] = $post->post_title . ' / направление';
    }
}
echo 'пустых полей у оборудования: ', count($empty), "\n";
foreach (array_slice($empty, 0, 12) as $line) echo '  - ', $line, "\n";

$with_photo = 0;
foreach (get_posts(array('post_type' => 'equipment', 'numberposts' => -1)) as $post) {
    if (get_field('tts_equipment_photo', $post->ID)) $with_photo++;
}
echo 'моделей с рендером: ', $with_photo, "\n";
echo 'файлов в медиатеке: ', count(get_posts(array('post_type' => 'attachment',
     'post_status' => 'inherit', 'numberposts' => -1))), "\n\n";

$sample = get_posts(array('post_type' => 'equipment', 'numberposts' => 1,
                          'orderby' => 'menu_order', 'order' => 'ASC'));
if ($sample) {
    $id = $sample[0]->ID;
    echo "ПРИМЕР МОДЕЛИ: ", $sample[0]->post_title, "\n";
    foreach (array('tts_equipment_code', 'tts_equipment_capacity', 'tts_equipment_status',
                   'tts_equipment_purpose', 'tts_equipment_scale') as $field) {
        echo '  ', $field, ' = ', var_export(get_field($field, $id), true), "\n";
    }
    $features = get_field('tts_equipment_features', $id);
    echo '  особенностей: ', is_array($features) ? count($features) : 0, "\n";
    $photo = get_field('tts_equipment_photo', $id);
    echo '  рендер: ', $photo ? wp_get_attachment_url($photo) : 'нет', "\n\n";
}

$project = get_posts(array('post_type' => 'project', 'numberposts' => 1,
                           'orderby' => 'menu_order', 'order' => 'ASC'));
if ($project) {
    $id = $project[0]->ID;
    echo "ПРИМЕР ПРОЕКТА: ", $project[0]->post_title, "\n";
    echo '  город: ', get_field('tts_project_city', $id), "\n";
    echo '  задача: ', mb_substr((string) get_field('tts_project_task', $id), 0, 60), "...\n";
    $figures = get_field('tts_project_figures', $id);
    echo '  показателей: ', is_array($figures) ? count($figures) : 0, "\n";
    $photo = get_field('tts_project_photo', $id);
    echo '  фото: ', $photo ? 'да' : 'нет', "\n\n";
}

$matrix = get_field('tts_settings_quiz_matrix', 'option');
echo 'МАТРИЦА ПОДБОРА: ', is_array($matrix) ? count($matrix) : 0, " строк\n";
foreach ((array) $matrix as $row) {
    $primary = $row['tts_settings_quiz_primary'] ?? 0;
    $alt = $row['tts_settings_quiz_alt'] ?? 0;
    echo '  ', $row['tts_settings_quiz_object'], ' / ', $row['tts_settings_quiz_capacity'],
        ' -> ', $primary ? get_the_title($primary) : 'ПУСТО',
        $alt ? ' / ' . get_the_title($alt) : '', "\n";
}
'''


def main():
    token = secrets.token_hex(8)
    runner = f'report-{token}.php'
    r = Remote()
    r.put_text(REPORT % {'token': token}, SITE_SUBDIR, runner)
    say(fetch(f'{SITE_URL}/{runner}?token={token}').strip())
    try:
        r.remove(SITE_SUBDIR, runner)
    except FileNotFoundError:
        pass
    r.close()


if __name__ == '__main__':
    main()
