<?php
/**
 * Поля блока «Карточки». Разметка — blocks/cards.php.
 */

defined( 'ABSPATH' ) || exit;

/** Карточки. */
function tts_block_fields_cards(): void {
	if ( ! function_exists( 'acf_add_local_field_group' ) ) {
		return;
	}

	acf_add_local_field_group(
		array(
			'key'      => 'group_tts_block_cards',
			'title'    => 'Карточки',
			'location' => tts_block_location( 'cards' ),
			'fields'   => array(
				array(
					'key'          => 'field_tts_cards_kicker',
					'label'        => 'Надзаголовок',
					'name'         => 'tts_cards_kicker',
					'type'         => 'text',
					'instructions' => 'Короткая строка над заголовком. Необязательно.',
				),
				array(
					'key'   => 'field_tts_cards_title',
					'label' => 'Заголовок секции',
					'name'  => 'tts_cards_title',
					'type'  => 'text',
				),
				array(
					'key'          => 'field_tts_cards_lead',
					'label'        => 'Описание',
					'name'         => 'tts_cards_lead',
					'type'         => 'textarea',
					'rows'         => 3,
					'new_lines'    => '',
					'instructions' => 'Абзац под заголовком. Необязательно.',
				),
				array(
					'key'           => 'field_tts_cards_grid',
					'label'         => 'Карточек в ряду',
					'name'          => 'tts_cards_grid',
					'type'          => 'select',
					'instructions'  => 'На узких экранах карточки всё равно перестраиваются в одну колонку.',
					'choices'       => array(
						'three' => 'Три в ряд (обычный вариант)',
						'two'   => 'Две в ряд (крупные карточки)',
						'four'  => 'Четыре в ряд (короткие карточки)',
					),
					'default_value' => 'three',
					'allow_null'    => 0,
					'return_format' => 'value',
				),
				array(
					'key'           => 'field_tts_cards_tone',
					'label'         => 'Фон секции',
					'name'          => 'tts_cards_tone',
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
					'key'          => 'field_tts_cards_items',
					'label'        => 'Карточки',
					'name'         => 'tts_cards_items',
					'type'         => 'repeater',
					'button_label' => 'Добавить карточку',
					'instructions' => 'Порядок карточек можно менять перетаскиванием.',
					'layout'       => 'row',
					'sub_fields'   => array(
						array(
							'key'          => 'field_tts_cards_item_index',
							'label'        => 'Номер',
							'name'         => 'tts_cards_item_index',
							'type'         => 'text',
							'instructions' => 'Например «01». Необязательно. Если номер заполнен, метка не показывается.',
						),
						array(
							'key'          => 'field_tts_cards_item_tag',
							'label'        => 'Метка',
							'name'         => 'tts_cards_item_tag',
							'type'         => 'text',
							'instructions' => 'Короткая подпись над заголовком: «Вариант 01», «CO-NELE». Необязательно.',
						),
						array(
							'key'      => 'field_tts_cards_item_title',
							'label'    => 'Заголовок',
							'name'     => 'tts_cards_item_title',
							'type'     => 'text',
							'required' => 1,
						),
						array(
							'key'       => 'field_tts_cards_item_text',
							'label'     => 'Текст',
							'name'      => 'tts_cards_item_text',
							'type'      => 'textarea',
							'rows'      => 3,
							'new_lines' => '',
						),
						array(
							'key'          => 'field_tts_cards_item_note',
							'label'        => 'Примечание',
							'name'         => 'tts_cards_item_note',
							'type'         => 'text',
							'instructions' => 'Строка-вывод под текстом: «Приоритет: компактность». Необязательно.',
						),
						array(
							'key'           => 'field_tts_cards_item_link',
							'label'         => 'Ссылка',
							'name'          => 'tts_cards_item_link',
							'type'          => 'link',
							'return_format' => 'array',
							'instructions'  => 'Необязательно. Если заполнить, вся карточка станет кликабельной.',
						),
					),
				),
			),
		)
	);
}
add_action( 'acf/include_fields', 'tts_block_fields_cards' );
