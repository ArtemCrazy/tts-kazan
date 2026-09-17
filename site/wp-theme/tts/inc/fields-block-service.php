<?php
/**
 * Поля блока «Сервис и запчасти». Разметка — blocks/service.php.
 */

defined( 'ABSPATH' ) || exit;

/** Сервис и запчасти. */
function tts_block_fields_service(): void {
	if ( ! function_exists( 'acf_add_local_field_group' ) ) {
		return;
	}

	acf_add_local_field_group(
		array(
			'key'      => 'group_tts_block_service',
			'title'    => 'Сервис и запчасти',
			'location' => tts_block_location( 'service' ),
			'fields'   => array(
				array(
					'key'   => 'field_tts_service_block_kicker',
					'label' => 'Надзаголовок',
					'name'  => 'tts_service_block_kicker',
					'type'  => 'text',
				),
				array(
					'key'      => 'field_tts_service_block_title',
					'label'    => 'Заголовок',
					'name'     => 'tts_service_block_title',
					'type'     => 'text',
					'required' => 1,
				),
				array(
					'key'       => 'field_tts_service_block_lead',
					'label'     => 'Описание',
					'name'      => 'tts_service_block_lead',
					'type'      => 'textarea',
					'rows'      => 3,
					'new_lines' => '',
				),
				array(
					'key'          => 'field_tts_service_block_trust',
					'label'        => 'Показатели поддержки',
					'name'         => 'tts_service_block_trust',
					'type'         => 'repeater',
					'instructions' => 'Строка над карточками. Обычно три пункта.',
					'layout'       => 'table',
					'button_label' => 'Добавить показатель',
					'sub_fields'   => array(
						array(
							'key'          => 'field_tts_service_block_trust_value',
							'label'        => 'Значение',
							'name'         => 'tts_service_block_trust_value',
							'type'         => 'text',
							'instructions' => 'Например: «24/7» или «На площадке».',
							'required'     => 1,
						),
						array(
							'key'          => 'field_tts_service_block_trust_label',
							'label'        => 'Подпись',
							'name'         => 'tts_service_block_trust_label',
							'type'         => 'text',
							'instructions' => 'Например: «техническая поддержка».',
							'required'     => 1,
						),
					),
				),
				array(
					'key'          => 'field_tts_service_block_cards',
					'label'        => 'Карточки',
					'name'         => 'tts_service_block_cards',
					'type'         => 'repeater',
					'instructions' => 'Две большие карточки: инженерный сервис и запасные части. Номер 01, 02 ставится сам по порядку.',
					'layout'       => 'block',
					'button_label' => 'Добавить карточку',
					'max'          => 2,
					'sub_fields'   => array(
						array(
							'key'          => 'field_tts_service_block_card_tag',
							'label'        => 'Ярлык',
							'name'         => 'tts_service_block_card_tag',
							'type'         => 'text',
							'instructions' => 'Короткая подпись в углу карточки. Например: «Минимум простоев».',
						),
						array(
							'key'      => 'field_tts_service_block_card_title',
							'label'    => 'Заголовок карточки',
							'name'     => 'tts_service_block_card_title',
							'type'     => 'text',
							'required' => 1,
						),
						array(
							'key'       => 'field_tts_service_block_card_text',
							'label'     => 'Описание',
							'name'      => 'tts_service_block_card_text',
							'type'      => 'textarea',
							'rows'      => 3,
							'new_lines' => '',
						),
						array(
							'key'          => 'field_tts_service_block_card_points',
							'label'        => 'Что входит',
							'name'         => 'tts_service_block_card_points',
							'type'         => 'textarea',
							'instructions' => 'Список под описанием: каждый пункт с новой строки.',
							'rows'         => 5,
							'new_lines'    => '',
						),
						array(
							'key'           => 'field_tts_service_block_card_link',
							'label'         => 'Кнопка на страницу раздела',
							'name'          => 'tts_service_block_card_link',
							'type'          => 'link',
							'return_format' => 'array',
							'instructions'  => 'Выберите страницу «Инженерный сервис» или «Запасные части». Без текста кнопка не показывается.',
						),
						array(
							'key'           => 'field_tts_service_block_card_style',
							'label'         => 'Вид кнопки',
							'name'          => 'tts_service_block_card_style',
							'type'          => 'select',
							'instructions'  => 'В вёрстке первая карточка с заливкой, вторая с рамкой.',
							'choices'       => array(
								'solid' => 'С заливкой',
								'ghost' => 'С рамкой',
							),
							'default_value' => 'solid',
							'allow_null'    => 0,
							'return_format' => 'value',
						),
						array(
							'key'           => 'field_tts_service_block_card_request',
							'label'         => 'Ссылка на заявку',
							'name'          => 'tts_service_block_card_request',
							'type'          => 'link',
							'return_format' => 'array',
							'instructions'  => 'Вторая ссылка рядом с кнопкой, обычно на форму. Без текста не показывается.',
						),
					),
				),
			),
		)
	);
}
add_action( 'acf/include_fields', 'tts_block_fields_service' );
