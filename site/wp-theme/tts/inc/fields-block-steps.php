<?php
/**
 * Поля блока «Этапы работы». Разметка — blocks/steps.php.
 */

defined( 'ABSPATH' ) || exit;

/** Этапы работы. */
function tts_block_fields_steps(): void {
	if ( ! function_exists( 'acf_add_local_field_group' ) ) {
		return;
	}

	acf_add_local_field_group(
		array(
			'key'      => 'group_tts_block_steps',
			'title'    => 'Этапы работы',
			'location' => tts_block_location( 'steps' ),
			'fields'   => array(
				array(
					'key'          => 'field_tts_steps_kicker',
					'label'        => 'Надзаголовок',
					'name'         => 'tts_steps_kicker',
					'type'         => 'text',
					'instructions' => 'Короткая строка над заголовком. Необязательно.',
				),
				array(
					'key'   => 'field_tts_steps_title',
					'label' => 'Заголовок секции',
					'name'  => 'tts_steps_title',
					'type'  => 'text',
				),
				array(
					'key'          => 'field_tts_steps_lead',
					'label'        => 'Описание',
					'name'         => 'tts_steps_lead',
					'type'         => 'textarea',
					'rows'         => 3,
					'new_lines'    => '',
					'instructions' => 'Абзац под заголовком. Необязательно.',
				),
				array(
					'key'           => 'field_tts_steps_tone',
					'label'         => 'Фон секции',
					'name'          => 'tts_steps_tone',
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
					'key'          => 'field_tts_steps_items',
					'label'        => 'Этапы',
					'name'         => 'tts_steps_items',
					'type'         => 'repeater',
					'button_label' => 'Добавить этап',
					'instructions' => 'Номера (01, 02, 03…) расставляются сами по порядку этапов. Лучше всего смотрятся три или четыре этапа: они встают в один ряд.',
					'layout'       => 'row',
					'sub_fields'   => array(
						array(
							'key'      => 'field_tts_steps_item_title',
							'label'    => 'Название этапа',
							'name'     => 'tts_steps_item_title',
							'type'     => 'text',
							'required' => 1,
						),
						array(
							'key'       => 'field_tts_steps_item_text',
							'label'     => 'Что происходит на этапе',
							'name'      => 'tts_steps_item_text',
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
add_action( 'acf/include_fields', 'tts_block_fields_steps' );
