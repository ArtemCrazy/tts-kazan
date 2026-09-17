<?php
/**
 * Поля блока «Пункты с маркерами». Разметка — blocks/points.php.
 */

defined( 'ABSPATH' ) || exit;

/** Пункты с маркерами. */
function tts_block_fields_points(): void {
	if ( ! function_exists( 'acf_add_local_field_group' ) ) {
		return;
	}

	acf_add_local_field_group(
		array(
			'key'      => 'group_tts_block_points',
			'title'    => 'Пункты с маркерами',
			'location' => tts_block_location( 'points' ),
			'fields'   => array(
				array(
					'key'          => 'field_tts_points_kicker',
					'label'        => 'Надзаголовок',
					'name'         => 'tts_points_kicker',
					'type'         => 'text',
					'instructions' => 'Короткая строка над заголовком. Необязательно.',
				),
				array(
					'key'   => 'field_tts_points_title',
					'label' => 'Заголовок секции',
					'name'  => 'tts_points_title',
					'type'  => 'text',
				),
				array(
					'key'          => 'field_tts_points_lead',
					'label'        => 'Описание',
					'name'         => 'tts_points_lead',
					'type'         => 'textarea',
					'rows'         => 3,
					'new_lines'    => '',
					'instructions' => 'Абзац под заголовком. Необязательно.',
				),
				array(
					'key'           => 'field_tts_points_tone',
					'label'         => 'Фон секции',
					'name'          => 'tts_points_tone',
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
					'key'          => 'field_tts_points_items',
					'label'        => 'Пункты',
					'name'         => 'tts_points_items',
					'type'         => 'repeater',
					'button_label' => 'Добавить пункт',
					'instructions' => 'Пункты идут списком друг под другом, с разделительной линией.',
					'layout'       => 'row',
					'sub_fields'   => array(
						array(
							'key'      => 'field_tts_points_item_title',
							'label'    => 'Заголовок пункта',
							'name'     => 'tts_points_item_title',
							'type'     => 'text',
							'required' => 1,
						),
						array(
							'key'       => 'field_tts_points_item_text',
							'label'     => 'Пояснение',
							'name'      => 'tts_points_item_text',
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
add_action( 'acf/include_fields', 'tts_block_fields_points' );
