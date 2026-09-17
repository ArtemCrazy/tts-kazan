<?php
/**
 * Поля блока «Две колонки: показатели и пункты». Разметка — blocks/split.php.
 */

defined( 'ABSPATH' ) || exit;

/** Две колонки: показатели и пункты. */
function tts_block_fields_split(): void {
	if ( ! function_exists( 'acf_add_local_field_group' ) ) {
		return;
	}

	acf_add_local_field_group(
		array(
			'key'      => 'group_tts_block_split',
			'title'    => 'Две колонки: показатели и пункты',
			'location' => tts_block_location( 'split' ),
			'fields'   => array(
				// Поля выбора фона нет: секция всегда тёмная, как в статике.
				array(
					'key'          => 'field_tts_split_kicker',
					'label'        => 'Надзаголовок (левая колонка)',
					'name'         => 'tts_split_kicker',
					'type'         => 'text',
					'instructions' => 'Короткая строка над заголовком. Необязательно.',
				),
				array(
					'key'          => 'field_tts_split_title',
					'label'        => 'Заголовок (левая колонка)',
					'name'         => 'tts_split_title',
					'type'         => 'text',
					'instructions' => 'Главный заголовок секции.',
				),
				array(
					'key'          => 'field_tts_split_lead',
					'label'        => 'Описание (левая колонка)',
					'name'         => 'tts_split_lead',
					'type'         => 'textarea',
					'rows'         => 3,
					'new_lines'    => '',
					'instructions' => 'Абзац под заголовком: здесь же живут оговорки о расчётном '
						. 'характере показателей. Необязательно.',
				),
				array(
					'key'          => 'field_tts_split_figures',
					'label'        => 'Показатели',
					'name'         => 'tts_split_figures',
					'type'         => 'repeater',
					'button_label' => 'Добавить показатель',
					'instructions' => 'Показатели встают по два в ряд, поэтому чётное количество '
						. 'выглядит аккуратнее.',
					'layout'       => 'table',
					'sub_fields'   => array(
						array(
							'key'          => 'field_tts_split_figure_value',
							'label'        => 'Значение',
							'name'         => 'tts_split_figure_value',
							'type'         => 'text',
							'required'     => 1,
							'instructions' => 'Цифра с единицей измерения: «5–50 т/ч», «−20%».',
						),
						array(
							'key'          => 'field_tts_split_figure_label',
							'label'        => 'Подпись',
							'name'         => 'tts_split_figure_label',
							'type'         => 'text',
							'required'     => 1,
							'instructions' => 'Что означает цифра: «производительность линии».',
						),
					),
				),
				array(
					'key'          => 'field_tts_split_points_kicker',
					'label'        => 'Надзаголовок (правая колонка)',
					'name'         => 'tts_split_points_kicker',
					'type'         => 'text',
					'instructions' => 'Короткая строка над заголовком списка. Необязательно.',
				),
				array(
					'key'          => 'field_tts_split_points_title',
					'label'        => 'Заголовок правой колонки',
					'name'         => 'tts_split_points_title',
					'type'         => 'text',
					'instructions' => 'Подзаголовок над списком пунктов.',
				),
				array(
					'key'          => 'field_tts_split_points',
					'label'        => 'Пункты',
					'name'         => 'tts_split_points',
					'type'         => 'repeater',
					'button_label' => 'Добавить пункт',
					'instructions' => 'Пункты идут списком друг под другом, с разделительной линией.',
					'layout'       => 'row',
					'sub_fields'   => array(
						array(
							'key'      => 'field_tts_split_point_title',
							'label'    => 'Заголовок пункта',
							'name'     => 'tts_split_point_title',
							'type'     => 'text',
							'required' => 1,
						),
						array(
							'key'       => 'field_tts_split_point_text',
							'label'     => 'Пояснение',
							'name'      => 'tts_split_point_text',
							'type'      => 'textarea',
							'rows'      => 3,
							'new_lines' => '',
						),
					),
				),
			),
		)
	);
}
add_action( 'acf/include_fields', 'tts_block_fields_split' );
