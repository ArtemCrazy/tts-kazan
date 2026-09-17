<?php
/**
 * Поля блока «Показатели». Разметка — blocks/figures.php.
 */

defined( 'ABSPATH' ) || exit;

/** Показатели. */
function tts_block_fields_figures(): void {
	if ( ! function_exists( 'acf_add_local_field_group' ) ) {
		return;
	}

	acf_add_local_field_group(
		array(
			'key'      => 'group_tts_block_figures',
			'title'    => 'Показатели',
			'location' => tts_block_location( 'figures' ),
			'fields'   => array(
				array(
					'key'          => 'field_tts_figures_kicker',
					'label'        => 'Надзаголовок',
					'name'         => 'tts_figures_kicker',
					'type'         => 'text',
					'instructions' => 'Короткая строка над заголовком. Необязательно.',
				),
				array(
					'key'   => 'field_tts_figures_title',
					'label' => 'Заголовок секции',
					'name'  => 'tts_figures_title',
					'type'  => 'text',
				),
				array(
					'key'          => 'field_tts_figures_lead',
					'label'        => 'Описание',
					'name'         => 'tts_figures_lead',
					'type'         => 'textarea',
					'rows'         => 3,
					'new_lines'    => '',
					'instructions' => 'Абзац под заголовком. Необязательно.',
				),
				array(
					'key'           => 'field_tts_figures_tone',
					'label'         => 'Фон секции',
					'name'          => 'tts_figures_tone',
					'type'          => 'select',
					'instructions'  => 'Чередуйте фон у соседних секций, чтобы страница не выглядела монотонной.',
					'choices'       => array(
						'light' => 'Светлый',
						'muted' => 'Приглушённый',
						'dark'  => 'Тёмный',
					),
					'default_value' => 'light',
					'allow_null'    => 0,
					'return_format' => 'value',
				),
				array(
					'key'          => 'field_tts_figures_items',
					'label'        => 'Показатели',
					'name'         => 'tts_figures_items',
					'type'         => 'repeater',
					'button_label' => 'Добавить показатель',
					'instructions' => 'Показатели встают по два в ряд, поэтому чётное количество выглядит аккуратнее.',
					'layout'       => 'table',
					'sub_fields'   => array(
						array(
							'key'          => 'field_tts_figures_item_value',
							'label'        => 'Значение',
							'name'         => 'tts_figures_item_value',
							'type'         => 'text',
							'required'     => 1,
							'instructions' => 'Цифра с единицей измерения: «5–50 т/ч», «−20%».',
						),
						array(
							'key'          => 'field_tts_figures_item_label',
							'label'        => 'Подпись',
							'name'         => 'tts_figures_item_label',
							'type'         => 'text',
							'required'     => 1,
							'instructions' => 'Что означает цифра: «производительность линии».',
						),
					),
				),
			),
		)
	);
}
add_action( 'acf/include_fields', 'tts_block_fields_figures' );
