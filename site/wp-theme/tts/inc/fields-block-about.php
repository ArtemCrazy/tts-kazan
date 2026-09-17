<?php
/**
 * Поля блока «О компании». Разметка — blocks/about.php.
 */

defined( 'ABSPATH' ) || exit;

/** О компании. */
function tts_block_fields_about(): void {
	if ( ! function_exists( 'acf_add_local_field_group' ) ) {
		return;
	}

	acf_add_local_field_group(
		array(
			'key'      => 'group_tts_block_about',
			'title'    => 'О компании',
			'location' => tts_block_location( 'about' ),
			'fields'   => array(
				array(
					'key'   => 'field_tts_about_kicker',
					'label' => 'Надзаголовок',
					'name'  => 'tts_about_kicker',
					'type'  => 'text',
				),
				array(
					'key'      => 'field_tts_about_title',
					'label'    => 'Заголовок',
					'name'     => 'tts_about_title',
					'type'     => 'text',
					'required' => 1,
				),
				array(
					'key'          => 'field_tts_about_text',
					'label'        => 'Текст',
					'name'         => 'tts_about_text',
					'type'         => 'textarea',
					'instructions' => 'Каждый абзац — с новой строки. Обычно два абзаца.',
					'rows'         => 5,
					'new_lines'    => '',
				),
				array(
					'key'           => 'field_tts_about_button',
					'label'         => 'Кнопка',
					'name'          => 'tts_about_button',
					'type'          => 'link',
					'return_format' => 'array',
					'instructions'  => 'Зелёная кнопка со стрелкой под текстом. Без текста кнопка не показывается.',
				),
				array(
					'key'          => 'field_tts_about_stats',
					'label'        => 'Показатели',
					'name'         => 'tts_about_stats',
					'type'         => 'repeater',
					'instructions' => 'Таблица справа от текста. Обычно четыре пункта.',
					'layout'       => 'table',
					'button_label' => 'Добавить показатель',
					'sub_fields'   => array(
						array(
							'key'          => 'field_tts_about_stat_value',
							'label'        => 'Значение',
							'name'         => 'tts_about_stat_value',
							'type'         => 'text',
							'instructions' => 'Например: «20+» или «Техподдержка 24/7».',
							'required'     => 1,
						),
						array(
							'key'          => 'field_tts_about_stat_label',
							'label'        => 'Подпись',
							'name'         => 'tts_about_stat_label',
							'type'         => 'text',
							'instructions' => 'Например: «лет на рынке промышленного оборудования».',
							'required'     => 1,
						),
						array(
							// Цифра выводится крупно, а значение словами в том же
							// размере не влезает — для него в вёрстке свой вид.
							'key'           => 'field_tts_about_stat_words',
							'label'         => 'Значение словами',
							'name'          => 'tts_about_stat_words',
							'type'          => 'true_false',
							'instructions'  => 'Включите, если вместо цифры написан текст: он выводится мельче.',
							'ui'            => 1,
							'default_value' => 0,
						),
					),
				),
			),
		)
	);
}
add_action( 'acf/include_fields', 'tts_block_fields_about' );
