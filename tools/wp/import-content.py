"""Перенос контента статической версии в WordPress.

Что уезжает: направления, 23 позиции каталога с рендерами, матрица подбора
для квиза, проекты, вопросы и ответы, услуги сервиса, а также тексты
комплектаций со страниц направлений. Источник — файлы статической версии
(см. catalog_static.py, content_static.py и models_static.py), поэтому
тексты совпадают с сайтом дословно.

Скрипт можно запускать повторно: записи ищутся по коду или заголовку и
обновляются, картинки в медиатеку заливаются один раз.

Запуск:  python tools/wp/import-content.py
"""

import json
import re
import os
import secrets
import sys

sys.path.insert(0, os.path.dirname(os.path.abspath(__file__)))
from catalog_static import DIRECTIONS, catalog, quiz_matrix  # noqa: E402
from common import Remote, fetch, say, SITE_SUBDIR, SITE_URL  # noqa: E402

try:
    import content_static
except ImportError:  # модуль появляется отдельно — без него переносим только каталог
    content_static = None

try:
    import models_static
except ImportError:  # без него карточки направлений останутся с текстами каталога
    models_static = None

RUNNER = r'''<?php
// Одноразовый перенос контента. Удаляется сразу после работы.
header('Content-Type: text/plain; charset=utf-8');
if (($_GET['token'] ?? '') !== '%(token)s') { http_response_code(403); exit('нет токена'); }
require __DIR__ . '/wp-load.php';
require_once ABSPATH . 'wp-admin/includes/image.php';

$plan = json_decode(file_get_contents(__DIR__ . '/%(payload)s'), true);

/** Картинка из assets темы в медиатеку. Повторно тот же файл не заливаем. */
function tts_import_image(string $rel): int {
    $found = get_posts(array(
        'post_type'   => 'attachment',
        'post_status' => 'inherit',
        'meta_key'    => '_tts_source',
        'meta_value'  => $rel,
        'numberposts' => 1,
        'fields'      => 'ids',
    ));
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

/** Запись раздела по заголовку: находим существующую или создаём. */
function tts_upsert(string $type, string $title, int $order): int {
    $found = get_posts(array(
        'post_type'   => $type,
        'post_status' => array('publish', 'draft'),
        'title'       => $title,
        'numberposts' => 1,
        'fields'      => 'ids',
    ));
    $data = array(
        'post_type'   => $type,
        'post_title'  => $title,
        'post_status' => 'publish',
        'menu_order'  => $order,
    );
    if ($found) $data['ID'] = (int) $found[0];
    $id = wp_insert_post($data, true);
    return is_wp_error($id) ? 0 : (int) $id;
}

$report = array();

// ---------------------------------------------------------------- направления
$terms = array();
foreach ($plan['directions'] as $dir) {
    $term = get_term_by('slug', $dir['slug'], 'direction');
    if (!$term) {
        $made = wp_insert_term($dir['name'], 'direction', array('slug' => $dir['slug']));
        if (is_wp_error($made)) { echo 'направление ', $dir['slug'], ': ', $made->get_error_message(), "\n"; continue; }
        $term = get_term($made['term_id'], 'direction');
    }
    $terms[$dir['slug']] = (int) $term->term_id;
    $page = get_page_by_path($dir['page']);
    if ($page && function_exists('update_field')) {
        update_field('tts_direction_page', $page->ID, 'direction_' . $term->term_id);
    }
    if (function_exists('update_field')) {
        update_field('tts_direction_tab', $dir['tab'], 'direction_' . $term->term_id);
    }
}
$report[] = 'направлений: ' . count($terms);

// ---------------------------------------------------------------- оборудование
$models = array();
foreach ($plan['equipment'] as $item) {
    $id = tts_upsert('equipment', $item['name'], $item['order']);
    if (!$id) { echo 'модель ', $item['name'], ": не создалась\n"; continue; }
    $models[$item['name']] = $id;

    update_field('tts_equipment_code', $item['code'], $id);
    update_field('tts_equipment_status', $item['status'], $id);
    update_field('tts_equipment_summary', $item['summary'], $id);
    update_field('tts_equipment_capacity', $item['capacity'], $id);
    // Тексты карточки на странице направления: комплектация называется иначе,
    // описание своё, показателей два, подпись кнопки своя.
    update_field('tts_equipment_title_long', $item['title_long'], $id);
    update_field('tts_equipment_summary_long', $item['summary_long'], $id);
    update_field('tts_equipment_spec2_value', $item['spec2_value'], $id);
    update_field('tts_equipment_spec2_label', $item['spec2_label'], $id);
    update_field('tts_equipment_cta', $item['cta'], $id);
    update_field('tts_equipment_tag', $item['tag'], $id);
    update_field('tts_equipment_lead_name', $item['lead_name'], $id);
    update_field('tts_equipment_purpose', $item['purpose'], $id);
    update_field('tts_equipment_scale', $item['scale'], $id);
    update_field('tts_equipment_features', array_map(
        static fn($text) => array('tts_equipment_feature' => $text),
        $item['features']
    ), $id);

    if (!empty($item['image'])) {
        $image = tts_import_image($item['image']);
        if ($image) update_field('tts_equipment_photo', $image, $id);
    }
    if (!empty($item['direction']) && isset($terms[$item['direction']])) {
        wp_set_object_terms($id, array($terms[$item['direction']]), 'direction');
    }
    $page = !empty($item['page']) ? get_page_by_path($item['page']) : null;
    if ($page) update_field('tts_equipment_direction_page', $page->ID, $id);
}
$report[] = 'моделей: ' . count($models);

// ---------------------------------------------------------------- матрица подбора
$matrix = array();
foreach ($plan['quiz'] as $row) {
    $matrix[] = array(
        'tts_settings_quiz_object'   => $row['object'],
        'tts_settings_quiz_capacity' => $row['answer'],
        'tts_settings_quiz_text'     => $row['recommendation'],
        'tts_settings_quiz_primary'  => $models[$row['primary']] ?? '',
        'tts_settings_quiz_alt'      => $row['alt'] ? ($models[$row['alt']] ?? '') : '',
    );
}
if ($matrix) update_field('tts_settings_quiz_matrix', $matrix, 'option');
$report[] = 'строк матрицы подбора: ' . count($matrix);

// ---------------------------------------------------------------- проекты
foreach ($plan['projects'] as $project) {
    $id = tts_upsert('project', $project['title'], $project['order']);
    if (!$id) continue;
    update_field('tts_project_city', $project['city'], $id);
    update_field('tts_project_summary', $project['summary'], $id);
    update_field('tts_project_task', $project['task'], $id);
    update_field('tts_project_solution', $project['solution'], $id);
    update_field('tts_project_figures', array_map(
        static fn($pair) => array(
            'tts_project_figure_value' => $pair[0],
            'tts_project_figure_label' => $pair[1],
        ),
        $project['figures']
    ), $id);
    if (!empty($project['image'])) {
        $image = tts_import_image($project['image']);
        if ($image) {
            update_field('tts_project_photo', $image, $id);
            if (!empty($project['alt'])) update_post_meta($image, '_wp_attachment_image_alt', $project['alt']);
        }
    }
}
$report[] = 'проектов: ' . count($plan['projects']);

// ---------------------------------------------------------------- вопросы и ответы
foreach ($plan['faq'] as $faq) {
    $id = tts_upsert('faq', $faq['question'], $faq['order']);
    if (!$id) continue;
    update_field('tts_faq_answer', $faq['answer'], $id);
    update_field('tts_faq_places', $faq['places'], $id);
    update_field('tts_faq_visible', true, $id);
}
$report[] = 'вопросов: ' . count($plan['faq']);

// ---------------------------------------------------------------- услуги сервиса
foreach ($plan['services'] as $service) {
    $id = tts_upsert('service_item', $service['title'], $service['order']);
    if (!$id) continue;
    update_field('tts_service_summary', $service['summary'], $id);
    if (!empty($service['steps'])) {
        update_field('tts_service_steps', array_map(
            static fn($pair) => array(
                'tts_service_step_title' => $pair[0],
                'tts_service_step_text'  => $pair[1],
            ),
            $service['steps']
        ), $id);
    }
    if (!empty($service['benefits'])) {
        update_field('tts_service_benefits', array_map(
            static fn($text) => array('tts_service_benefit' => $text),
            $service['benefits']
        ), $id);
    }
}
$report[] = 'услуг: ' . count($plan['services']);

echo implode("\n", $report), "\n";
echo 'функция полей доступна: ', (function_exists('update_field') ? 'да' : 'НЕТ — поля не заполнились'), "\n";
'''


def image_for(item):
    """Рендер модели: в вёрстке лежит имя без расширения."""
    if not item.get('img'):
        return ''
    # webp — первым: именно его браузер показывает в статической версии
    # (через <picture> и image-set), а у pkn-pump.jpg даже другой кадр.
    for ext in ('.webp', '.png', '.jpg'):
        if os.path.exists(os.path.join(
                os.path.dirname(os.path.dirname(os.path.dirname(os.path.abspath(__file__)))),
                'site', 'assets', 'img', item['img'] + ext)):
            return 'img/' + item['img'] + ext
    return ''


def plan():
    # Подписи табов общего каталога — как в статической версии
    catalog_html = open(os.path.join(os.path.dirname(os.path.dirname(os.path.dirname(os.path.abspath(__file__)))),
                                     'site', '4', 'catalog', 'index.html'), encoding='utf-8').read()
    tabs = dict(re.findall(r'data-category="([a-z]+)"[^>]*>([^<]+)</button>', catalog_html))

    # Все страницы направлений лежат внутри каталога (п. 4.1 ТЗ)
    pages = {slug: f'catalog/{slug}' for slug, _ in DIRECTIONS.values()}

    # Карточки со страниц направлений: ключ — направление и код модели.
    # Код уникален только внутри направления, поэтому ищем по паре.
    cards = models_static.by_code() if models_static else {}

    items = catalog()
    equipment = []
    for index, item in enumerate(items):
        # Позиция каталога может не попасть ни на одну страницу направления
        # (на ВПИ показывают три комплектации из семи) — тогда длинных
        # текстов у неё просто нет.
        card = cards.get((item['direction'], item['code']), {})
        specs = card.get('specs') or []
        spec2 = specs[1] if len(specs) > 1 else ('', '')

        equipment.append({
            'name': item['name'],
            'code': item['code'],
            'status': item['status_field'],
            'summary': item['text'],
            'capacity': item['capacity'],
            'purpose': item['purpose_field'],
            'scale': item['scale'],
            'features': item.get('features', []),
            'direction': item['direction'],
            'page': pages.get(item['direction'], ''),
            # Своего рендера у позиции каталога может не быть (ПКН, SmartStock 3000),
            # а на странице направления у той же модели он есть — берём оттуда.
            'image': image_for(item) or image_for({'img': card.get('img', '')}),
            'title_long': card.get('title', ''),
            'summary_long': card.get('text', ''),
            'spec2_value': spec2[0],
            'spec2_label': spec2[1],
            'cta': card.get('cta', ''),
            'tag': card.get('tag', ''),
            'lead_name': card.get('value', ''),
            'order': (index + 1) * 10,
        })

    projects, faq, services = [], [], []
    if content_static:
        for index, project in enumerate(content_static.projects()):
            projects.append({
                'title': project['title'],
                'city': project['city'],
                'summary': project.get('summary', ''),
                'task': project['task'],
                'solution': project['solution'],
                'figures': project['figures'],
                'image': f"img/project-{project['photo']}.webp",
                'alt': project.get('alt', ''),
                'order': (index + 1) * 10,
            })
        for index, item in enumerate(content_static.faq()):
            answer = item['answer']
            services_html = '\n'.join(f'<p>{part}</p>' for part in answer) \
                if isinstance(answer, list) else f'<p>{answer}</p>'
            faq.append({
                'question': item['question'],
                'answer': services_html,
                'places': ['home'],
                'order': (index + 1) * 10,
            })
        for index, item in enumerate(content_static.service_items()):
            services.append({
                'title': item['title'],
                'summary': item.get('text', ''),
                'steps': item.get('steps', []),
                'benefits': item.get('benefits', []),
                'order': (index + 1) * 10,
            })

    return {
        'directions': [
            {'slug': slug, 'name': name, 'page': pages[slug], 'tab': tabs.get(key, name)}
            for key, (slug, name) in DIRECTIONS.items()
        ],
        'equipment': equipment,
        'quiz': quiz_matrix(),
        'projects': projects,
        'faq': faq,
        'services': services,
    }


def main():
    data = plan()
    say(f"к переносу: направлений {len(data['directions'])}, моделей {len(data['equipment'])}, "
        f"строк матрицы {len(data['quiz'])}, проектов {len(data['projects'])}, "
        f"вопросов {len(data['faq'])}, услуг {len(data['services'])}")

    matched = [item for item in data['equipment'] if item['title_long']]
    say(f'из них с текстами комплектаций со страниц направлений: {len(matched)}')
    rest = [item['name'] for item in data['equipment'] if not item['title_long']]
    if rest:
        say('только в общем каталоге: ' + ', '.join(rest))

    payload = 'import-' + secrets.token_hex(4) + '.json'
    token = secrets.token_hex(8)
    runner = f'import-{token}.php'

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
