<?php
/**
 * Поля редактора для плагина Secure Custom Fields (форк ACF 6.x, тот же API).
 *
 * Поля описаны кодом, а не через интерфейс плагина: так они уезжают на сервер
 * вместе с темой и одинаковы на staging и на продакшене. Заводить те же поля
 * руками в админке не нужно — они появятся сами.
 *
 * Типы записей (equipment, project, faq) и таксономия direction
 * регистрируются отдельно, здесь мы только навешиваем на них поля.
 */

defined( 'ABSPATH' ) || exit;

/**
 * Группы полей.
 *
 * Хук acf/include_fields — штатное место для локальных полей: к этому моменту
 * плагин загружен, а интерфейс редактора ещё не собран.
 */
function tts_register_fields(): void {
	if ( ! function_exists( 'acf_add_local_field_group' ) ) {
		return;
	}

	tts_fields_equipment();
	tts_fields_direction();
	tts_fields_project();
	tts_fields_faq();
	tts_fields_page();
	tts_fields_settings();
}
add_action( 'acf/include_fields', 'tts_register_fields' );

/**
 * Страница общих настроек сайта.
 *
 * Регистрируется на acf/init: страница должна существовать до того, как
 * WordPress начнёт собирать меню админки.
 */
function tts_register_settings_page(): void {
	if ( ! function_exists( 'acf_add_options_page' ) ) {
		return;
	}

	acf_add_options_page(
		array(
			'page_title'      => 'Настройки сайта',
			'menu_title'      => 'Настройки сайта',
			'menu_slug'       => 'tts-settings',
			'capability'      => 'manage_options',
			'icon_url'        => 'dashicons-admin-settings',
			'redirect'        => false,
			'update_button'   => 'Сохранить настройки',
			'updated_message' => 'Настройки сохранены.',
		)
	);
}
add_action( 'acf/init', 'tts_register_settings_page' );

/** Оборудование: карточка модели в каталоге. */
function tts_fields_equipment(): void {
	acf_add_local_field_group(
		array(
			'key'                   => 'group_tts_equipment',
			'title'                 => 'Данные оборудования',
			'fields'                => array(
				array(
					'key'          => 'field_tts_equipment_code',
					'label'        => 'Код модели',
					'name'         => 'tts_equipment_code',
					'type'         => 'text',
					'instructions' => 'Как модель называется в документах: SmartDryMix 20, ПКН-30. Показывается в карточке и в поиске по каталогу.',
					'required'     => 1,
				),
				array(
					'key'           => 'field_tts_equipment_status',
					'label'         => 'Статус',
					'name'          => 'tts_equipment_status',
					'type'          => 'select',
					'instructions'  => 'Подпись на карточке: готовая конфигурация, типовая модель или пример.',
					'required'      => 1,
					'choices'       => array(
						'ready'     => 'Готовая конфигурация',
						'typical'   => 'Типовая модель',
						'example'   => 'Пример конфигурации',
						'project'   => 'Проектная конфигурация',
						'modified'  => 'Модифицированный',
						'transport' => 'Пневмотранспорт',
					),
					'default_value' => 'example',
					'allow_null'    => 0,
					'return_format' => 'value',
				),
				array(
					'key'           => 'field_tts_equipment_photo',
					'label'         => 'Фото',
					'name'          => 'tts_equipment_photo',
					'type'          => 'image',
					'instructions'  => 'Горизонтальное фото оборудования, минимум 1200 px по ширине. Сток использовать нельзя.',
					'return_format' => 'id',
					'preview_size'  => 'medium',
					'library'       => 'all',
				),
				array(
					'key'          => 'field_tts_equipment_summary',
					'label'        => 'Краткое описание',
					'name'         => 'tts_equipment_summary',
					'type'         => 'textarea',
					'instructions' => 'Одно-два предложения для карточки в каталоге. Подробное описание пишется в основном тексте записи.',
					'rows'         => 3,
					'new_lines'    => '',
				),
				array(
					'key'          => 'field_tts_equipment_title_long',
					'label'        => 'Название на странице направления',
					'name'         => 'tts_equipment_title_long',
					'type'         => 'text',
					'instructions' => 'Как комплектация называется на странице направления: «SmartBeton 30 S + QS 1000–1200». Если пусто, берётся заголовок записи.',
				),
				array(
					'key'          => 'field_tts_equipment_summary_long',
					'label'        => 'Описание на странице направления',
					'name'         => 'tts_equipment_summary_long',
					'type'         => 'textarea',
					'instructions' => 'Описание комплектации: из чего собрана и для кого. Если пусто, берётся краткое описание.',
					'rows'         => 3,
					'new_lines'    => '',
				),
				array(
					'key'          => 'field_tts_equipment_capacity',
					'label'        => 'Производительность или вместимость',
					'name'         => 'tts_equipment_capacity',
					'type'         => 'text',
					'instructions' => 'Одной строкой, с единицами измерения: «до 40 м³/ч», «1000 тонн».',
				),
				array(
					'key'          => 'field_tts_equipment_spec2_value',
					'label'        => 'Второй показатель: значение',
					'name'         => 'tts_equipment_spec2_value',
					'type'         => 'text',
					'instructions' => 'Второй показатель в карточке на странице направления: «до 30», «G · L». Пусто — показывается только производительность.',
				),
				array(
					'key'          => 'field_tts_equipment_spec2_label',
					'label'        => 'Второй показатель: подпись',
					'name'         => 'tts_equipment_spec2_label',
					'type'         => 'text',
					'instructions' => 'Подпись ко второму показателю: «изделий за цикл», «марок цемента».',
				),
				array(
					'key'           => 'field_tts_equipment_purpose',
					'label'         => 'Назначение',
					'name'          => 'tts_equipment_purpose',
					'type'          => 'select',
					'instructions'  => 'По этому полю работает фильтр в общем каталоге и подбор в квизе на главной.',
					'required'      => 1,
					'choices'       => array(
						'dry-mix'        => 'Сухие строительные смеси',
						'concrete'       => 'Товарный бетон и дороги',
						'vpi'            => 'Вибропрессованные изделия',
						'cement-storage' => 'Хранение цемента',
						'pneumo'         => 'Пневмотранспорт',
					),
					'allow_null'    => 0,
					'return_format' => 'value',
				),
				array(
					'key'           => 'field_tts_equipment_scale',
					'label'         => 'Масштаб',
					'name'          => 'tts_equipment_scale',
					'type'          => 'select',
					'instructions'  => 'Второй фильтр каталога: под какой объём производства подходит модель.',
					'required'      => 1,
					'choices'       => array(
						'compact'    => 'Компактный',
						'medium'     => 'Средний',
						'industrial' => 'Промышленный',
					),
					'allow_null'    => 0,
					'return_format' => 'value',
				),
				array(
					'key'          => 'field_tts_equipment_features',
					'label'        => 'Особенности',
					'name'         => 'tts_equipment_features',
					'type'         => 'repeater',
					'instructions' => 'Короткие пункты списка в карточке, по одному в строке. 3–5 пунктов читаются лучше всего.',
					'layout'       => 'table',
					'button_label' => 'Добавить особенность',
					'sub_fields'   => array(
						array(
							'key'   => 'field_tts_equipment_feature',
							'label' => 'Особенность',
							'name'  => 'tts_equipment_feature',
							'type'  => 'text',
						),
					),
				),
				array(
					'key'          => 'field_tts_equipment_tag',
					'label'        => 'Ярлык на странице направления',
					'name'         => 'tts_equipment_tag',
					'type'         => 'text',
					'instructions' => 'Короткая подпись над кодом: «Стартовая линия», «Компактный». Если пусто, показывается статус.',
				),
				array(
					'key'          => 'field_tts_equipment_lead_name',
					'label'        => 'Название для заявки',
					'name'         => 'tts_equipment_lead_name',
					'type'         => 'text',
					'instructions' => 'Как модель попадёт в заявку с кнопки в карточке. Если пусто, берётся название на странице направления или заголовок записи.',
				),
				array(
					'key'          => 'field_tts_equipment_cta',
					'label'        => 'Подпись кнопки в карточке',
					'name'         => 'tts_equipment_cta',
					'type'         => 'text',
					'instructions' => 'Своя подпись кнопки для этой модели: «Подобрать линию», «Подобрать Т10». Если пусто, берётся подпись из блока.',
				),
				array(
					'key'           => 'field_tts_equipment_direction_page',
					'label'         => 'Страница направления',
					'name'          => 'tts_equipment_direction_page',
					'type'          => 'post_object',
					'instructions'  => 'Страница каталога, к которой относится модель: с карточки будет ссылка «Смотреть направление».',
					'post_type'     => array( 'page' ),
					'return_format' => 'id',
					'allow_null'    => 1,
					'multiple'      => 0,
					'ui'            => 1,
				),
			),
			'location'              => array(
				array(
					array(
						'param'    => 'post_type',
						'operator' => '==',
						'value'    => 'equipment',
					),
				),
			),
			'menu_order'            => 0,
			'position'              => 'normal',
			'style'                 => 'default',
			'label_placement'       => 'top',
			'instruction_placement' => 'label',
			'active'                => true,
		)
	);
}

/** Направления: поля термина таксономии. */
function tts_fields_direction(): void {
	acf_add_local_field_group(
		array(
			'key'                   => 'group_tts_direction',
			'title'                 => 'Данные направления',
			'fields'                => array(
				array(
					'key'          => 'field_tts_direction_tab',
					'label'        => 'Подпись в табах каталога',
					'name'         => 'tts_direction_tab',
					'type'         => 'text',
					'instructions' => 'Короткое название для таба в общем каталоге, например «ПКН». Если пусто, берётся название направления.',
				),
				array(
					'key'           => 'field_tts_direction_page',
					'label'         => 'Страница каталога',
					'name'          => 'tts_direction_page',
					'type'          => 'post_object',
					'instructions'  => 'Страница направления: на неё ведут ссылки из общего каталога. Описание, картинки и SEO направления редактируются на самой этой странице.',
					'post_type'     => array( 'page' ),
					'return_format' => 'id',
					'allow_null'    => 1,
					'multiple'      => 0,
					'ui'            => 1,
				),
			),
			'location'              => array(
				array(
					array(
						'param'    => 'taxonomy',
						'operator' => '==',
						'value'    => 'direction',
					),
				),
			),
			'menu_order'            => 0,
			'style'                 => 'default',
			'label_placement'       => 'top',
			'instruction_placement' => 'label',
			'active'                => true,
		)
	);
}

/** Проекты: блок «Реализованные объекты» на главной. */
function tts_fields_project(): void {
	acf_add_local_field_group(
		array(
			'key'                   => 'group_tts_project',
			'title'                 => 'Данные проекта',
			'fields'                => array(
				array(
					'key'          => 'field_tts_project_city',
					'label'        => 'Город',
					'name'         => 'tts_project_city',
					'type'         => 'text',
					'instructions' => 'Например: Астана. Показывается подписью на карточке проекта.',
					'required'     => 1,
				),
				array(
					'key'          => 'field_tts_project_task',
					'label'        => 'Задача',
					'name'         => 'tts_project_task',
					'type'         => 'textarea',
					'instructions' => 'Что требовалось клиенту.',
					'rows'         => 3,
					'new_lines'    => '',
				),
				array(
					'key'          => 'field_tts_project_solution',
					'label'        => 'Решение',
					'name'         => 'tts_project_solution',
					'type'         => 'textarea',
					'instructions' => 'Что предложили и сделали.',
					'rows'         => 3,
					'new_lines'    => '',
				),
				array(
					'key'          => 'field_tts_project_figures',
					'label'        => 'Показатели',
					'name'         => 'tts_project_figures',
					'type'         => 'repeater',
					'instructions' => 'Цифры под фотографией. Обычно две-три.',
					'layout'       => 'table',
					'button_label' => 'Добавить показатель',
					'sub_fields'   => array(
						array(
							'key'          => 'field_tts_project_figure_value',
							'label'        => 'Значение',
							'name'         => 'tts_project_figure_value',
							'type'         => 'text',
							'instructions' => 'Например: «90 м³/ч».',
							'required'     => 1,
						),
						array(
							'key'          => 'field_tts_project_figure_label',
							'label'        => 'Подпись',
							'name'         => 'tts_project_figure_label',
							'type'         => 'text',
							'instructions' => 'Например: «производительность завода».',
							'required'     => 1,
						),
					),
				),
				array(
					'key'           => 'field_tts_project_photo',
					'label'         => 'Фото',
					'name'          => 'tts_project_photo',
					'type'          => 'image',
					'instructions'  => 'Фото объекта, минимум 1200 px по ширине. Только свои фото, с правом публикации.',
					'return_format' => 'id',
					'preview_size'  => 'medium',
					'library'       => 'all',
				),
			),
			'location'              => array(
				array(
					array(
						'param'    => 'post_type',
						'operator' => '==',
						'value'    => 'project',
					),
				),
			),
			'menu_order'            => 0,
			'position'              => 'normal',
			'style'                 => 'default',
			'label_placement'       => 'top',
			'instruction_placement' => 'label',
			'active'                => true,
		)
	);
}

/** Вопросы и ответы: заголовок записи — сам вопрос. */
function tts_fields_faq(): void {
	acf_add_local_field_group(
		array(
			'key'                   => 'group_tts_faq',
			'title'                 => 'Ответ и показ',
			'fields'                => array(
				array(
					'key'          => 'field_tts_faq_answer',
					'label'        => 'Ответ',
					'name'         => 'tts_faq_answer',
					'type'         => 'wysiwyg',
					'instructions' => 'Текст ответа. Можно выделять жирным и делать списки.',
					'required'     => 1,
					'tabs'         => 'visual',
					'toolbar'      => 'basic',
					'media_upload' => 0,
					'delay'        => 0,
				),
				array(
					'key'           => 'field_tts_faq_places',
					'label'         => 'Где показывать',
					'name'          => 'tts_faq_places',
					'type'          => 'select',
					'instructions'  => 'Можно выбрать несколько страниц. Один и тот же вопрос не нужно заводить дважды.',
					'required'      => 1,
					'choices'       => array(
						'home'    => 'Главная',
						'catalog' => 'Каталог',
						'vpi'     => 'Страница ВПИ',
						'service' => 'Сервис',
						'parts'   => 'Запчасти',
					),
					'multiple'      => 1,
					'ui'            => 1,
					'allow_null'    => 0,
					'return_format' => 'value',
				),
				array(
					'key'           => 'field_tts_faq_visible',
					'label'         => 'Показывать на сайте',
					'name'          => 'tts_faq_visible',
					'type'          => 'true_false',
					'instructions'  => 'Выключите, чтобы временно скрыть вопрос, не удаляя его.',
					'default_value' => 1,
					'ui'            => 1,
				),
			),
			'location'              => array(
				array(
					array(
						'param'    => 'post_type',
						'operator' => '==',
						'value'    => 'faq',
					),
				),
			),
			'menu_order'            => 0,
			'position'              => 'normal',
			'style'                 => 'default',
			'label_placement'       => 'top',
			'instruction_placement' => 'label',
			'active'                => true,
		)
	);
}

/** Страница: то, что меняется в шапке от страницы к странице. */
function tts_fields_page(): void {
	acf_add_local_field_group(
		array(
			'key'                   => 'group_tts_page',
			'title'                 => 'Шапка страницы',
			'fields'                => array(
				array(
					'key'          => 'field_tts_page_topline',
					'label'        => 'Верхняя строка, справа',
					'name'         => 'tts_page_topline',
					'type'         => 'text',
					'instructions' => 'Своя подпись для этой страницы в самой верхней полосе сайта. Если пусто — общий текст из «Настроек сайта → Шапка».',
				),
			),
			'location'              => array(
				array(
					array(
						'param'    => 'post_type',
						'operator' => '==',
						'value'    => 'page',
					),
				),
			),
			'menu_order'            => 0,
			'position'              => 'side',
			'style'                 => 'default',
			'label_placement'       => 'top',
			'instruction_placement' => 'label',
			'active'                => true,
		)
	);
}

/** Общие настройки сайта: шапка, подвал, контакты, заявки, аналитика, матрица квиза. */
function tts_fields_settings(): void {
	acf_add_local_field_group(
		array(
			'key'                   => 'group_tts_settings',
			'title'                 => 'Настройки сайта',
			'fields'                => array(
				array(
					'key'       => 'field_tts_settings_tab_header',
					'label'     => 'Шапка',
					'name'      => '',
					'type'      => 'tab',
					'placement' => 'top',
				),
				array(
					'key'       => 'field_tts_settings_header_menu_note',
					'label'     => 'Пункты меню',
					'name'      => '',
					'type'      => 'message',
					'message'   => 'Пункты меню шапки и выпадающие списки редактируются в разделе <a href="nav-menus.php">Внешний вид → Меню</a>, меню «Шапка сайта».',
					'new_lines' => 'wpautop',
				),
				array(
					'key'           => 'field_tts_settings_logo_light',
					'label'         => 'Логотип для тёмного фона',
					'name'          => 'tts_settings_logo_light',
					'type'          => 'image',
					'instructions'  => 'Светлый вариант: в шапке на первом экране и в подвале. PNG или WebP с прозрачным фоном. Если не загружен, показывается логотип темы.',
					'return_format' => 'id',
					'preview_size'  => 'medium',
					'library'       => 'all',
				),
				array(
					'key'           => 'field_tts_settings_logo_dark',
					'label'         => 'Логотип для светлого фона',
					'name'          => 'tts_settings_logo_dark',
					'type'          => 'image',
					'instructions'  => 'Тёмный вариант: в шапке, когда страница прокручена. Если не загружен, показывается логотип темы.',
					'return_format' => 'id',
					'preview_size'  => 'medium',
					'library'       => 'all',
				),
				array(
					'key'          => 'field_tts_settings_brand_name',
					'label'        => 'Название рядом с логотипом',
					'name'         => 'tts_settings_brand_name',
					'type'         => 'text',
					'instructions' => 'Например: «ТТС Инжиниринг».',
				),
				array(
					'key'          => 'field_tts_settings_brand_region',
					'label'        => 'Подпись под названием',
					'name'         => 'tts_settings_brand_region',
					'type'         => 'text',
					'instructions' => 'Например: «Казахстан».',
				),
				array(
					'key'          => 'field_tts_settings_topline_left',
					'label'        => 'Верхняя строка, слева',
					'name'         => 'tts_settings_topline_left',
					'type'         => 'text',
					'instructions' => 'Короткое позиционирование в самой верхней полосе сайта.',
				),
				array(
					'key'          => 'field_tts_settings_topline_right',
					'label'        => 'Верхняя строка, справа',
					'name'         => 'tts_settings_topline_right',
					'type'         => 'text',
					'instructions' => 'Общий текст для всех страниц. У страницы может быть своя подпись — поле «Верхняя строка, справа» справа в редакторе страницы. На телефоне не показывается.',
				),
				array(
					'key'          => 'field_tts_settings_header_button',
					'label'        => 'Текст кнопки в шапке',
					'name'         => 'tts_settings_header_button',
					'type'         => 'text',
					'instructions' => 'Кнопка ведёт к форме заявки: на этой же странице, а если формы на странице нет — на главной.',
				),
				array(
					'key'       => 'field_tts_settings_tab_footer',
					'label'     => 'Подвал',
					'name'      => '',
					'type'      => 'tab',
					'placement' => 'top',
				),
				array(
					'key'       => 'field_tts_settings_footer_menu_note',
					'label'     => 'Ссылки в колонках',
					'name'      => '',
					'type'      => 'message',
					'message'   => 'Ссылки в трёх колонках подвала редактируются в разделе <a href="nav-menus.php">Внешний вид → Меню</a>: меню «Подвал: первая колонка», «вторая» и «третья». Заголовок колонки — название меню.',
					'new_lines' => 'wpautop',
				),
				array(
					'key'          => 'field_tts_settings_footer_about',
					'label'        => 'Описание под логотипом',
					'name'         => 'tts_settings_footer_about',
					'type'         => 'textarea',
					'instructions' => 'Одно предложение о компании.',
					'rows'         => 2,
					'new_lines'    => '',
				),
				array(
					'key'          => 'field_tts_settings_footer_legal',
					'label'        => 'Реквизиты',
					'name'         => 'tts_settings_footer_legal',
					'type'         => 'text',
					'instructions' => 'Строка под документами, например БИН. Публикуем только подтверждённые данные (п. 5.2 ТЗ).',
				),
				array(
					'key'          => 'field_tts_settings_footer_catalog_title',
					'label'        => 'Плашка каталога: заголовок',
					'name'         => 'tts_settings_footer_catalog_title',
					'type'         => 'text',
					'instructions' => 'Полоса со ссылкой на общий каталог над копирайтом. Если заголовок и текст кнопки пустые, плашка не показывается.',
				),
				array(
					'key'          => 'field_tts_settings_footer_catalog_note',
					'label'        => 'Плашка каталога: подпись',
					'name'         => 'tts_settings_footer_catalog_note',
					'type'         => 'text',
					'instructions' => 'Например: «23 конфигурации в пяти направлениях — с поиском и фильтрами».',
				),
				array(
					'key'          => 'field_tts_settings_footer_catalog_button',
					'label'        => 'Плашка каталога: текст кнопки',
					'name'         => 'tts_settings_footer_catalog_button',
					'type'         => 'text',
					'instructions' => 'Кнопка ведёт в общий каталог.',
				),
				array(
					'key'          => 'field_tts_settings_footer_home_title',
					'label'        => 'Плашка на странице каталога: заголовок',
					'name'         => 'tts_settings_footer_home_title',
					'type'         => 'text',
					'instructions' => 'На самой странице общего каталога вместо плашки «Весь каталог» — ссылка на главную.',
				),
				array(
					'key'          => 'field_tts_settings_footer_home_note',
					'label'        => 'Плашка на странице каталога: подпись',
					'name'         => 'tts_settings_footer_home_note',
					'type'         => 'text',
					'instructions' => 'Например: «Подбор оборудования за две минуты, направления и проекты в Казахстане».',
				),
				array(
					'key'          => 'field_tts_settings_footer_home_button',
					'label'        => 'Плашка на странице каталога: текст кнопки',
					'name'         => 'tts_settings_footer_home_button',
					'type'         => 'text',
					'instructions' => 'Кнопка ведёт на главную.',
				),
				array(
					'key'          => 'field_tts_settings_copyright',
					'label'        => 'Копирайт',
					'name'         => 'tts_settings_copyright',
					'type'         => 'text',
					'instructions' => 'Текст после знака © и текущего года — год меняется сам.',
				),
				array(
					'key'       => 'field_tts_settings_tab_contacts',
					'label'     => 'Контакты',
					'name'      => '',
					'type'      => 'tab',
					'placement' => 'top',
				),
				array(
					'key'       => 'field_tts_settings_contacts_note',
					'label'     => 'Где видны контакты',
					'name'      => '',
					'type'      => 'message',
					'message'   => 'Заполненные поля появляются в подвале под описанием компании и в разметке для поисковиков. Пустые поля на сайте не показываются — пока заказчик не подтвердил контакты, их можно не заполнять.',
					'new_lines' => 'wpautop',
				),
				array(
					'key'          => 'field_tts_settings_phone',
					'label'        => 'Телефон',
					'name'         => 'tts_settings_phone',
					'type'         => 'text',
					'instructions' => 'В том виде, в каком показываем на сайте: +7 700 000 00 00. Ссылку для звонка сайт соберёт сам.',
				),
				array(
					'key'          => 'field_tts_settings_email',
					'label'        => 'E-mail',
					'name'         => 'tts_settings_email',
					'type'         => 'email',
					'instructions' => 'Адрес для связи. Куда приходят заявки — во вкладке «Заявки».',
				),
				array(
					'key'          => 'field_tts_settings_address',
					'label'        => 'Адрес',
					'name'         => 'tts_settings_address',
					'type'         => 'text',
					'instructions' => 'Город, улица, дом — одной строкой.',
				),
				array(
					'key'          => 'field_tts_settings_hours',
					'label'        => 'Часы работы',
					'name'         => 'tts_settings_hours',
					'type'         => 'text',
					'instructions' => 'Например: пн–пт, 9:00–18:00.',
				),
				array(
					'key'          => 'field_tts_settings_socials',
					'label'        => 'Соцсети и мессенджеры',
					'name'         => 'tts_settings_socials',
					'type'         => 'repeater',
					'instructions' => 'Ссылки в подвале, например WhatsApp или Instagram. Открываются в новой вкладке.',
					'layout'       => 'table',
					'button_label' => 'Добавить ссылку',
					'sub_fields'   => array(
						array(
							'key'      => 'field_tts_settings_social_title',
							'label'    => 'Название',
							'name'     => 'tts_settings_social_title',
							'type'     => 'text',
							'required' => 1,
						),
						array(
							'key'      => 'field_tts_settings_social_url',
							'label'    => 'Ссылка',
							'name'     => 'tts_settings_social_url',
							'type'     => 'url',
							'required' => 1,
						),
					),
				),
				array(
					'key'   => 'field_tts_settings_tab_leads',
					'label' => 'Заявки',
					'name'  => '',
					'type'  => 'tab',
				),
				array(
					'key'          => 'field_tts_settings_lead_email',
					'label'        => 'Куда отправлять заявки',
					'name'         => 'tts_settings_lead_email',
					'type'         => 'text',
					'instructions' => 'Один адрес или несколько через запятую. Пока поле пустое, '
						. 'письма не отправляются, но заявки сохраняются в разделе «Заявки».',
				),
				array(
					'key'          => 'field_tts_settings_lead_subject',
					'label'        => 'Тема письма',
					'name'         => 'tts_settings_lead_subject',
					'type'         => 'text',
					'instructions' => 'Если не заполнить — «Заявка с сайта ТТС Инжиниринг».',
				),
				array(
					'key'           => 'field_tts_settings_lead_days',
					'label'         => 'Сколько дней хранить заявки',
					'name'          => 'tts_settings_lead_days',
					'type'          => 'number',
					'instructions'  => 'Старые заявки удаляются автоматически: персональные данные '
						. 'не должны лежать в админке дольше согласованного срока. '
						. '0 — не удалять (тогда чистить вручную).',
					'default_value' => 90,
					'min'           => 0,
					'max'           => 3650,
				),
				array(
					'key'       => 'field_tts_settings_tab_analytics',
					'label'     => 'Аналитика',
					'name'      => '',
					'type'      => 'tab',
					'placement' => 'top',
				),
				array(
					'key'          => 'field_tts_settings_gtm_id',
					'label'        => 'ID Google Tag Manager',
					'name'         => 'tts_settings_gtm_id',
					'type'         => 'text',
					'instructions' => 'Вид GTM-XXXXXXX. GA4 и Google Ads подключаются внутри контейнера GTM (п. 13 ТЗ), отдельные поля для них не нужны. Пока поле пустое, код на сайт не подключается.',
				),
				array(
					'key'          => 'field_tts_settings_metrika_id',
					'label'        => 'Номер счётчика Яндекс Метрики',
					'name'         => 'tts_settings_metrika_id',
					'type'         => 'text',
					'instructions' => 'Только цифры. Пока поле пустое, код на сайт не подключается.',
				),
				array(
					'key'          => 'field_tts_settings_roistat_key',
					'label'        => 'Ключ Roistat',
					'name'         => 'tts_settings_roistat_key',
					'type'         => 'text',
					'instructions' => 'Ключ проекта из личного кабинета Roistat. Пока поле пустое, код на сайт не подключается.',
				),
				array(
					'key'       => 'field_tts_settings_tab_quiz',
					'label'     => 'Квиз подбора',
					'name'      => '',
					'type'      => 'tab',
					'placement' => 'top',
				),
				array(
					'key'          => 'field_tts_settings_quiz_matrix',
					'label'        => 'Матрица рекомендаций',
					'name'         => 'tts_settings_quiz_matrix',
					'type'         => 'repeater',
					'instructions' => 'Строки соответствуют матрице подбора из ТЗ: на каждое сочетание ответов квиз выдаёт свою модель. Если сочетания в матрице нет, квиз предложит связаться с инженером.',
					'layout'       => 'row',
					'button_label' => 'Добавить строку матрицы',
					'sub_fields'   => array(
						array(
							'key'           => 'field_tts_settings_quiz_object',
							'label'         => 'Тип объекта',
							'name'          => 'tts_settings_quiz_object',
							'type'          => 'select',
							'instructions'  => 'Ответ на первый вопрос квиза.',
							'required'      => 1,
							'choices'       => array(
								'dry-mix'  => 'Завод сухих смесей',
								'concrete' => 'Бетонный завод',
								'terminal' => 'Цементный терминал',
							),
							'allow_null'    => 0,
							'return_format' => 'value',
						),
						array(
							'key'          => 'field_tts_settings_quiz_capacity',
							'label'        => 'Ответ про производительность или объём',
							'name'         => 'tts_settings_quiz_capacity',
							'type'         => 'text',
							'instructions' => 'Слово в слово как вариант ответа в квизе: «до 20 т/ч», «1000–2000 тонн».',
							'required'     => 1,
						),
						array(
							'key'          => 'field_tts_settings_quiz_text',
							'label'        => 'Текст рекомендации',
							'name'         => 'tts_settings_quiz_text',
							'type'         => 'textarea',
							'instructions' => 'Что посетитель увидит над карточками, когда нажмёт «Показать решение».',
							'rows'         => 3,
							'new_lines'    => '',
						),
						array(
							'key'           => 'field_tts_settings_quiz_primary',
							'label'         => 'Основная модель',
							'name'          => 'tts_settings_quiz_primary',
							'type'          => 'post_object',
							'instructions'  => 'Что квиз порекомендует в первую очередь.',
							'required'      => 1,
							'post_type'     => array( 'equipment' ),
							'return_format' => 'id',
							'allow_null'    => 0,
							'multiple'      => 0,
							'ui'            => 1,
						),
						array(
							'key'          => 'field_tts_settings_quiz_primary_text',
							'label'        => 'Текст карточки основной модели',
							'name'         => 'tts_settings_quiz_primary_text',
							'type'         => 'text',
							'instructions' => 'Необязательно. Короткое описание в карточке результата. Если пусто — «Краткое описание» модели из каталога.',
						),
						array(
							'key'          => 'field_tts_settings_quiz_primary_capacity',
							'label'        => 'Производительность в карточке основной модели',
							'name'         => 'tts_settings_quiz_primary_capacity',
							'type'         => 'text',
							'instructions' => 'Необязательно. Если пусто — из каталога.',
						),
						array(
							'key'           => 'field_tts_settings_quiz_alt',
							'label'         => 'Альтернативная модель',
							'name'          => 'tts_settings_quiz_alt',
							'type'          => 'post_object',
							'instructions'  => 'Необязательно. Показывается второй карточкой, если заполнено.',
							'post_type'     => array( 'equipment' ),
							'return_format' => 'id',
							'allow_null'    => 1,
							'multiple'      => 0,
							'ui'            => 1,
						),
						array(
							'key'          => 'field_tts_settings_quiz_alt_text',
							'label'        => 'Текст карточки альтернативы',
							'name'         => 'tts_settings_quiz_alt_text',
							'type'         => 'text',
							'instructions' => 'Необязательно. Если пусто — «Краткое описание» модели из каталога.',
						),
						array(
							'key'          => 'field_tts_settings_quiz_alt_capacity',
							'label'        => 'Производительность в карточке альтернативы',
							'name'         => 'tts_settings_quiz_alt_capacity',
							'type'         => 'text',
							'instructions' => 'Необязательно. Если пусто — из каталога.',
						),
					),
				),
			),
			'location'              => array(
				array(
					array(
						'param'    => 'options_page',
						'operator' => '==',
						'value'    => 'tts-settings',
					),
				),
			),
			'menu_order'            => 0,
			'position'              => 'normal',
			'style'                 => 'default',
			'label_placement'       => 'top',
			'instruction_placement' => 'label',
			'active'                => true,
		)
	);
}
