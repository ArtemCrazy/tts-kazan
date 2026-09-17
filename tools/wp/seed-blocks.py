"""Наполнение страниц блоками редактора.

Блоки Gutenberg — это разметка в содержимом страницы, поэтому собрать её
скриптом можно только один раз, а дальше страницу ведёт редактор. Скрипт
ставит блоки и их значения, но НЕ перезаписывает страницу, если её уже
правили руками (сверяемся по метке tts_seeded).

Картинки ищутся в assets темы и попадают в медиатеку, чтобы редактор мог
их заменить обычным способом.

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

/** Значение поля: картинки описаны как {"image": "img/foo.png"} и превращаются в ID. */
function tts_seed_value($value) {
    if (is_array($value) && isset($value['image'])) return tts_seed_image($value['image']);
    return $value;
}

/**
 * Данные блока в том виде, в котором их хранит редактор: значение поля плюс
 * ссылка на его ключ. У повторяющихся полей — количество строк и плоские
 * ключи вида имя_0_подполе.
 */
function tts_block_data(array $fields): array {
    $data = array();
    foreach ($fields as $name => $value) {
        if (is_array($value) && array_is_list($value) && $value && is_array($value[0])) {
            $data[$name] = count($value);
            $data['_' . $name] = 'field_' . $name;
            foreach ($value as $i => $row) {
                foreach ($row as $sub => $sub_value) {
                    $data[$name . '_' . $i . '_' . $sub] = tts_seed_value($sub_value);
                    $data['_' . $name . '_' . $i . '_' . $sub] = 'field_' . $sub;
                }
            }
            continue;
        }
        $data[$name] = tts_seed_value($value);
        $data['_' . $name] = 'field_' . $name;
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


def home_blocks():
    """Главная страница: пока первый экран и вопросы, остальные блоки добавим по ходу."""
    return [
        {
            'block': 'hero',
            'anchor': 'hero',
            'fields': {
                'tts_hero_title': 'Заводы и производственные линии для стройматериалов',
                'tts_hero_accent': 'от проекта до запуска',
                'tts_hero_lead': 'Оборудование для производства сухих строительных смесей, '
                                 'товарного бетона, ЖБИ, ВПИ и строительства «под ключ».',
                'tts_hero_primary': {'title': 'Перейти в каталог', 'url': '/catalog/', 'target': ''},
                'tts_hero_secondary': {'title': 'Выбрать оборудование', 'url': '#quiz', 'target': ''},
                'tts_hero_photo': {'image': 'img/hero-plant-tts.png'},
                'tts_hero_tag': 'Проектирование и поставка под ключ',
                'tts_hero_proofs': [
                    {'tts_hero_proof_text': 'Собственное производство',
                     'tts_hero_proof_icon': 'proof-production'},
                    {'tts_hero_proof_text': 'Оборудование в наличии',
                     'tts_hero_proof_icon': 'proof-stock'},
                    {'tts_hero_proof_text': 'Филиал в Алматы — проекты по Казахстану',
                     'tts_hero_proof_icon': 'proof-almaty'},
                ],
            },
        },
        {
            'block': 'quiz',
            'anchor': 'quiz',
            'fields': {
                'tts_quiz_kicker': 'Подбор за 2 минуты',
                'tts_quiz_title': 'Какой объект вы планируете?',
                'tts_quiz_lead': 'Ответьте на три вопроса — покажем базовую конфигурацию '
                                 'и подготовим исходные данные для инженера.',
                'tts_quiz_label_object': 'Тип объекта',
                'tts_quiz_label_capacity': 'Производительность / хранение',
                'tts_quiz_label_stage': 'Стадия проекта',
                'tts_quiz_stages': [
                    {'tts_quiz_stage': 'Формируем идею'},
                    {'tts_quiz_stage': 'Выбираем технологию'},
                    {'tts_quiz_stage': 'Есть площадка и ТЗ'},
                ],
                'tts_quiz_submit': 'Показать решение',
                'tts_quiz_result_kicker': 'Предварительная рекомендация',
                'tts_quiz_cta': {'title': 'Получить инженерный расчёт', 'url': '#contact', 'target': ''},
                'tts_quiz_matches_title': 'Оборудование под выбранные параметры',
                'tts_quiz_matches_note': 'Первая карточка — основная рекомендация. Вторая, если '
                                         'доступна, показывает вариант с запасом производительности.',
            },
        },
        {
            'block': 'faq',
            'anchor': 'faq',
            'fields': {
                'tts_faq_block_kicker': 'Частые вопросы',
                'tts_faq_block_title': 'Подбор начинается с вашей задачи',
                'tts_faq_block_place': 'home',
            },
        },
        {
            'block': 'form',
            'anchor': 'contact',
            'fields': {
                'tts_form_kicker': 'Обсудим проект',
                'tts_form_title': 'Получите расчёт оборудования под вашу задачу',
                'tts_form_lead': 'Инженер уточнит продукт, производительность и исходные данные '
                                 'площадки, затем предложит состав оборудования и следующий этап проекта.',
                'tts_form_office': 'Филиал ТТС Инжиниринг в Казахстане',
                'tts_form_details': [
                    {'tts_form_detail': 'Алматы, ул. Казыбек Би, 22, офис 302'},
                    {'tts_form_detail': 'Пн–Пт, 9:00–18:00'},
                ],
                'tts_form_card_title': 'Оставьте контакты',
                'tts_form_note': 'Перезвоним в рабочее время и уточним задачу.',
                'tts_form_direction_label': 'Направление',
                'tts_form_directions': [
                    {'tts_form_direction': 'Заводы сухих смесей'},
                    {'tts_form_direction': 'Бетонные заводы'},
                    {'tts_form_direction': 'Заводы ВПИ'},
                    {'tts_form_direction': 'Цементные терминалы'},
                    {'tts_form_direction': 'Инженерный сервис'},
                    {'tts_form_direction': 'Запасные части и автоматика'},
                ],
                'tts_form_comment_label': 'Комментарий',
                'tts_form_comment_hint': 'Кратко опишите задачу',
                'tts_form_submit': 'Получить консультацию',
                'tts_form_done_title': 'Заявка отправлена',
                'tts_form_done_text': 'Мы получили обращение и свяжемся с вами в рабочее время.',
                'tts_form_again': 'Отправить ещё одну заявку',
                'tts_form_source': 'general',
            },
        },
    ]


def plan():
    return {'pages': [{'path': 'home', 'blocks': home_blocks()}]}


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
