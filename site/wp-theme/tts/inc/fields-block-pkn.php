<?php
/**
 * Поля блока «Пневмокамерные насосы». Разметка — blocks/pkn.php.
 */

defined( 'ABSPATH' ) || exit;

/** Пневмокамерные насосы. */
function tts_block_fields_pkn(): void {
	if ( ! function_exists( 'acf_add_local_field_group' ) ) {
		return;
	}

	acf_add_local_field_group(
		array(
			'key'      => 'group_tts_block_pkn',
			'title'    => 'Пневмокамерные насосы',
			'location' => tts_block_location( 'pkn' ),
			'fields'   => array(
				array(
					'key'           => 'field_tts_pkn_photo',
					'label'         => 'Снимок насоса',
					'name'          => 'tts_pkn_photo',
					'type'          => 'image',
					'instructions'  => 'Фото слева от текста. Подпись для незрячих берётся из поля «Альт. текст» в медиатеке.',
					'return_format' => 'id',
					'preview_size'  => 'medium',
				),
				array(
					'key'          => 'field_tts_pkn_caption',
					'label'        => 'Подпись у снимка',
					'name'         => 'tts_pkn_caption',
					'type'         => 'text',
					'instructions' => 'Например: «Дополнительное оборудование».',
				),
				array(
					'key'   => 'field_tts_pkn_kicker',
					'label' => 'Надзаголовок',
					'name'  => 'tts_pkn_kicker',
					'type'  => 'text',
				),
				array(
					'key'      => 'field_tts_pkn_title',
					'label'    => 'Заголовок',
					'name'     => 'tts_pkn_title',
					'type'     => 'text',
					'required' => 1,
				),
				array(
					'key'       => 'field_tts_pkn_lead',
					'label'     => 'Описание',
					'name'      => 'tts_pkn_lead',
					'type'      => 'textarea',
					'rows'      => 3,
					'new_lines' => '',
				),
				array(
					'key'          => 'field_tts_pkn_specs',
					'label'        => 'Характеристики',
					'name'         => 'tts_pkn_specs',
					'type'         => 'repeater',
					'instructions' => 'Цифры под описанием. Обычно три пункта.',
					'layout'       => 'table',
					'button_label' => 'Добавить характеристику',
					'sub_fields'   => array(
						array(
							'key'          => 'field_tts_pkn_spec_value',
							'label'        => 'Значение',
							'name'         => 'tts_pkn_spec_value',
							'type'         => 'text',
							'instructions' => 'Например: «10–60» или «до 250».',
							'required'     => 1,
						),
						array(
							'key'          => 'field_tts_pkn_spec_label',
							'label'        => 'Подпись',
							'name'         => 'tts_pkn_spec_label',
							'type'         => 'text',
							'instructions' => 'Единицы измерения. Например: «т/ч», «м по горизонтали».',
							'required'     => 1,
						),
					),
				),
				array(
					'key'          => 'field_tts_pkn_note',
					'label'        => 'Примечание',
					'name'         => 'tts_pkn_note',
					'type'         => 'textarea',
					'instructions' => 'Строка под характеристиками. Например про комплект поставки.',
					'rows'         => 2,
					'new_lines'    => '',
				),
				array(
					'key'           => 'field_tts_pkn_button',
					'label'         => 'Кнопка',
					'name'          => 'tts_pkn_button',
					'type'          => 'link',
					'return_format' => 'array',
					'instructions'  => 'Обычно ведёт в каталог ПКН. Без текста кнопка не показывается.',
				),
			),
		)
	);
}
add_action( 'acf/include_fields', 'tts_block_fields_pkn' );
