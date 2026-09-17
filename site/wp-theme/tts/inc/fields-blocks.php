<?php
/**
 * Поля блоков-секций. Разметка блоков — в blocks/<имя>.php.
 *
 * Держим отдельно от inc/fields.php: там поля записей и настроек, здесь —
 * поля секций страниц. Иначе один файл разрастается до неподъёмного.
 */

defined( 'ABSPATH' ) || exit;

/** Регистрация групп полей блоков. */
function tts_register_block_fields(): void {
	if ( ! function_exists( 'acf_add_local_field_group' ) ) {
		return;
	}

	tts_block_fields_hero();
	tts_block_fields_faq();
}
add_action( 'acf/include_fields', 'tts_register_block_fields' );

/** Условие «поля этой группы — для такого-то блока». */
function tts_block_location( string $name ): array {
	return array(
		array(
			array(
				'param'    => 'block',
				'operator' => '==',
				'value'    => 'acf/tts-' . $name,
			),
		),
	);
}

/** Первый экран. */
function tts_block_fields_hero(): void {
	acf_add_local_field_group(
		array(
			'key'      => 'group_tts_block_hero',
			'title'    => 'Первый экран',
			'location' => tts_block_location( 'hero' ),
			'fields'   => array(
				array(
					'key'          => 'field_tts_hero_title',
					'label'        => 'Заголовок',
					'name'         => 'tts_hero_title',
					'type'         => 'text',
					'required'     => 1,
					'instructions' => 'Главный заголовок страницы. Один на страницу.',
				),
				array(
					'key'          => 'field_tts_hero_accent',
					'label'        => 'Выделенная часть заголовка',
					'name'         => 'tts_hero_accent',
					'type'         => 'text',
					'instructions' => 'Продолжение заголовка, которое подсвечивается цветом. Необязательно.',
				),
				array(
					'key'       => 'field_tts_hero_lead',
					'label'     => 'Описание',
					'name'      => 'tts_hero_lead',
					'type'      => 'textarea',
					'rows'      => 3,
					'new_lines' => '',
				),
				array(
					'key'          => 'field_tts_hero_primary',
					'label'        => 'Главная кнопка',
					'name'         => 'tts_hero_primary',
					'type'         => 'link',
					'return_format' => 'array',
					'instructions' => 'Зелёная кнопка со стрелкой.',
				),
				array(
					'key'          => 'field_tts_hero_secondary',
					'label'        => 'Вторая кнопка',
					'name'         => 'tts_hero_secondary',
					'type'         => 'link',
					'return_format' => 'array',
					'instructions' => 'Кнопка с рамкой. Необязательно.',
				),
				array(
					'key'           => 'field_tts_hero_photo',
					'label'         => 'Снимок оборудования',
					'name'          => 'tts_hero_photo',
					'type'          => 'image',
					'return_format' => 'id',
					'instructions'  => 'Изображение справа от заголовка. Лучше без фона (PNG).',
				),
				array(
					'key'          => 'field_tts_hero_tag',
					'label'        => 'Подпись у снимка',
					'name'         => 'tts_hero_tag',
					'type'         => 'text',
				),
				array(
					'key'          => 'field_tts_hero_proofs',
					'label'        => 'Преимущества',
					'name'         => 'tts_hero_proofs',
					'type'         => 'repeater',
					'button_label' => 'Добавить преимущество',
					'instructions' => 'Строка под первым экраном. Обычно три пункта.',
					'layout'       => 'table',
					'sub_fields'   => array(
						array(
							'key'      => 'field_tts_hero_proof_text',
							'label'    => 'Текст',
							'name'     => 'tts_hero_proof_text',
							'type'     => 'text',
							'required' => 1,
						),
						array(
							// Значки лежат в теме: загружать SVG в медиатеку WordPress
							// не даёт из-за требований безопасности, и это правильно.
							'key'           => 'field_tts_hero_proof_icon',
							'label'         => 'Значок',
							'name'          => 'tts_hero_proof_icon',
							'type'          => 'select',
							'instructions'  => 'Необязательно. Набор значков темы.',
							'choices'       => array(
								'proof-production' => 'Собственное производство',
								'proof-stock'      => 'Оборудование в наличии',
								'proof-almaty'     => 'Филиал и география работ',
							),
							'allow_null'    => 1,
							'return_format' => 'value',
						),
					),
				),
			),
		)
	);
}

/** Вопросы и ответы. */
function tts_block_fields_faq(): void {
	acf_add_local_field_group(
		array(
			'key'      => 'group_tts_block_faq',
			'title'    => 'Вопросы и ответы',
			'location' => tts_block_location( 'faq' ),
			'fields'   => array(
				array(
					'key'   => 'field_tts_faq_block_kicker',
					'label' => 'Надзаголовок',
					'name'  => 'tts_faq_block_kicker',
					'type'  => 'text',
				),
				array(
					'key'   => 'field_tts_faq_block_title',
					'label' => 'Заголовок',
					'name'  => 'tts_faq_block_title',
					'type'  => 'text',
				),
				array(
					'key'          => 'field_tts_faq_block_place',
					'label'        => 'Какие вопросы показать',
					'name'         => 'tts_faq_block_place',
					'type'         => 'select',
					'instructions' => 'Берутся вопросы из раздела «Вопросы и ответы», у которых отмечена эта страница.',
					'choices'      => array(
						'home'    => 'Главная',
						'catalog' => 'Каталог',
						'vpi'     => 'Страница ВПИ',
						'service' => 'Сервис',
						'parts'   => 'Запчасти',
					),
					'default_value' => 'home',
					'allow_null'    => 0,
					'return_format' => 'value',
				),
			),
		)
	);
}
