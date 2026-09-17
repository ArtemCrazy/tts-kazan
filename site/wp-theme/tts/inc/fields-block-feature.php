<?php
/**
 * Поля блока «Кадр и текст». Разметка — blocks/feature.php.
 */

defined( 'ABSPATH' ) || exit;

/** Кадр и текст. */
function tts_block_fields_feature(): void {
	if ( ! function_exists( 'acf_add_local_field_group' ) ) {
		return;
	}

	acf_add_local_field_group(
		array(
			'key'      => 'group_tts_block_feature',
			'title'    => 'Кадр и текст',
			'location' => tts_block_location( 'feature' ),
			'fields'   => array(
				array(
					'key'          => 'field_tts_feature_kicker',
					'label'        => 'Надзаголовок',
					'name'         => 'tts_feature_kicker',
					'type'         => 'text',
					'instructions' => 'Мелкая строка над заголовком, например «QUNFENG + ТТС · готовые линии ВПИ».',
				),
				array(
					'key'      => 'field_tts_feature_title',
					'label'    => 'Заголовок',
					'name'     => 'tts_feature_title',
					'type'     => 'text',
					'required' => 1,
				),
				array(
					'key'       => 'field_tts_feature_lead',
					'label'     => 'Описание',
					'name'      => 'tts_feature_lead',
					'type'      => 'textarea',
					'rows'      => 3,
					'new_lines' => '',
				),
				array(
					'key'          => 'field_tts_feature_points',
					'label'        => 'Пункты',
					'name'         => 'tts_feature_points',
					'type'         => 'repeater',
					'button_label' => 'Добавить пункт',
					'instructions' => 'Список под описанием. Обычно три пункта.',
					'layout'       => 'table',
					'sub_fields'   => array(
						array(
							'key'      => 'field_tts_feature_point',
							'label'    => 'Текст',
							'name'     => 'tts_feature_point',
							'type'     => 'text',
							'required' => 1,
						),
					),
				),
				array(
					'key'           => 'field_tts_feature_cta',
					'label'         => 'Кнопка',
					'name'          => 'tts_feature_cta',
					'type'          => 'link',
					'return_format' => 'array',
					'instructions'  => 'Зелёная кнопка со стрелкой под списком. Необязательно.',
				),
				array(
					// Блок ставится на разные секции (ВПИ, бетонные заводы),
					// поэтому запасной картинки из темы здесь нет: без поля
					// нельзя угадать, какой снимок нужен.
					'key'           => 'field_tts_feature_shot',
					'label'         => 'Снимок оборудования',
					'name'          => 'tts_feature_shot',
					'type'          => 'image',
					'return_format' => 'id',
					'instructions'  => 'Изображение в тёмной рамке. Лучше без фона (PNG). Описание изображения (alt) задаётся в медиатеке.',
				),
				array(
					'key'          => 'field_tts_feature_caption',
					'label'        => 'Подпись под снимком',
					'name'         => 'tts_feature_caption',
					'type'         => 'text',
					'instructions' => 'Короткая строка, например «единая линия ВПИ» или перечень моделей.',
				),
				array(
					'key'           => 'field_tts_feature_partner',
					'label'         => 'Логотип партнёра',
					'name'          => 'tts_feature_partner',
					'type'          => 'image',
					'return_format' => 'id',
					'instructions'  => 'Необязательно. Если загружен, в подписи появляется связка «знак ТТС × логотип партнёра». Описание изображения (alt) задаётся в медиатеке.',
				),
				array(
					'key'           => 'field_tts_feature_mirror',
					'label'         => 'Зеркальная раскладка',
					'name'          => 'tts_feature_mirror',
					'type'          => 'true_false',
					'instructions'  => 'Снимок уходит направо, текст налево. Нужно, чтобы две такие секции подряд не выглядели близнецами.',
					'ui'            => 1,
					'default_value' => 0,
				),
			),
		)
	);
}
add_action( 'acf/include_fields', 'tts_block_fields_feature' );
