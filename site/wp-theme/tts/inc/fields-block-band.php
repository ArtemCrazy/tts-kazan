<?php
/**
 * Поля блока «Плашка с кнопкой». Разметка — blocks/band.php.
 */

defined( 'ABSPATH' ) || exit;

/** Плашка с кнопкой. */
function tts_block_fields_band(): void {
	if ( ! function_exists( 'acf_add_local_field_group' ) ) {
		return;
	}

	acf_add_local_field_group(
		array(
			'key'      => 'group_tts_block_band',
			'title'    => 'Плашка с кнопкой',
			'location' => tts_block_location( 'band' ),
			'fields'   => array(
				array(
					'key'          => 'field_tts_band_title',
					'label'        => 'Заголовок',
					'name'         => 'tts_band_title',
					'type'         => 'text',
					'required'     => 1,
					'instructions' => 'Одна строка: что предлагаем сделать дальше.',
				),
				array(
					'key'          => 'field_tts_band_text',
					'label'        => 'Текст',
					'name'         => 'tts_band_text',
					'type'         => 'textarea',
					'rows'         => 2,
					'new_lines'    => '',
					'instructions' => 'Пояснение под заголовком. Необязательно.',
				),
				array(
					'key'           => 'field_tts_band_button',
					'label'         => 'Кнопка',
					'name'          => 'tts_band_button',
					'type'          => 'link',
					'return_format' => 'array',
					'instructions'  => 'Надпись и адрес перехода. Без адреса кнопка не показывается.',
				),
				array(
					'key'           => 'field_tts_band_tone',
					'label'         => 'Фон секции',
					'name'          => 'tts_band_tone',
					'type'          => 'select',
					'instructions'  => 'В вёрстке плашка тёмная — так она заметнее на светлой странице.',
					'choices'       => array(
						'light' => 'Светлый',
						'muted' => 'Приглушённый',
						'dark'  => 'Тёмный',
					),
					'default_value' => 'dark',
					'allow_null'    => 0,
					'return_format' => 'value',
				),
			),
		)
	);
}
add_action( 'acf/include_fields', 'tts_block_fields_band' );
