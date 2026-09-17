<?php
/**
 * Поля блока «Направления каталога». Разметка — blocks/categories.php.
 */

defined( 'ABSPATH' ) || exit;

/** Направления каталога. */
function tts_block_fields_categories(): void {
	if ( ! function_exists( 'acf_add_local_field_group' ) ) {
		return;
	}

	acf_add_local_field_group(
		array(
			'key'      => 'group_tts_block_categories',
			'title'    => 'Направления каталога',
			'location' => tts_block_location( 'categories' ),
			'fields'   => array(
				array(
					'key'          => 'field_tts_categories_kicker',
					'label'        => 'Надзаголовок',
					'name'         => 'tts_categories_kicker',
					'type'         => 'text',
					'instructions' => 'Мелкая строка над заголовком, например «Каталог оборудования».',
				),
				array(
					'key'      => 'field_tts_categories_title',
					'label'    => 'Заголовок',
					'name'     => 'tts_categories_title',
					'type'     => 'text',
					'required' => 1,
				),
				array(
					'key'       => 'field_tts_categories_lead',
					'label'     => 'Описание',
					'name'      => 'tts_categories_lead',
					'type'      => 'textarea',
					'rows'      => 3,
					'new_lines' => '',
				),
				array(
					'key'          => 'field_tts_categories_cards',
					'label'        => 'Карточки',
					'name'         => 'tts_categories_cards',
					'type'         => 'repeater',
					'button_label' => 'Добавить карточку',
					'instructions' => 'Основные направления на главной. Оформление рассчитано на три карточки.',
					'layout'       => 'block',
					'max'          => 3,
					'sub_fields'   => array(
						array(
							'key'      => 'field_tts_category_title',
							'label'    => 'Заголовок карточки',
							'name'     => 'tts_category_title',
							'type'     => 'text',
							'required' => 1,
						),
						array(
							'key'       => 'field_tts_category_text',
							'label'     => 'Описание',
							'name'      => 'tts_category_text',
							'type'      => 'textarea',
							'rows'      => 3,
							'new_lines' => '',
						),
						array(
							'key'           => 'field_tts_category_link',
							'label'         => 'Кнопка карточки',
							'name'          => 'tts_category_link',
							'type'          => 'link',
							'return_format' => 'array',
							'instructions'  => 'Страница направления и подпись кнопки, например «Смотреть модели».',
						),
						array(
							// Рендер в карточке рисует CSS фоном по data-cat, поэтому
							// это выбор из набора темы, а не загрузка файла: картинка
							// из медиатеки в эту рамку без правок стилей не встанет.
							'key'           => 'field_tts_category_render',
							'label'         => 'Рендер оборудования',
							'name'          => 'tts_category_render',
							'type'          => 'select',
							'instructions'  => 'Изображение в верхней части карточки. Необязательно: без него остаётся светлое поле с номером.',
							'choices'       => array(
								'zsss'     => 'Завод сухих смесей',
								'beton'    => 'Бетонный завод',
								'terminal' => 'Цементный терминал',
							),
							'allow_null'    => 1,
							'return_format' => 'value',
						),
						array(
							'key'               => 'field_tts_category_alt',
							'label'             => 'Описание рендера',
							'name'              => 'tts_category_alt',
							'type'              => 'text',
							'instructions'      => 'Что видно на изображении — читают программы чтения с экрана.',
							'conditional_logic' => array(
								array(
									array(
										'field'    => 'field_tts_category_render',
										'operator' => '!=empty',
									),
								),
							),
						),
						array(
							'key'          => 'field_tts_category_index',
							'label'        => 'Номер карточки',
							'name'         => 'tts_category_index',
							'type'         => 'text',
							'instructions' => 'Кружок в углу изображения. Пусто — номер считается по порядку карточек (01, 02, 03).',
						),
					),
				),
			),
		)
	);
}
add_action( 'acf/include_fields', 'tts_block_fields_categories' );
