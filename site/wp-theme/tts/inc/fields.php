<?php
/**
 * Поля редактора для плагина Secure Custom Fields (форк ACF 6.x, тот же API).
 *
 * Поля описаны кодом, а не через интерфейс плагина: так они уезжают на сервер
 * вместе с темой и одинаковы на staging и на продакшене. Заводить те же поля
 * руками в админке не нужно — они появятся сами.
 *
 * Типы записей (equipment, project, faq, service_item) и таксономия direction
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
	tts_fields_service_item();
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
						'ready'   => 'Готовая конфигурация',
						'typical' => 'Типовая модель',
						'example' => 'Пример конфигурации',
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
					'key'          => 'field_tts_equipment_capacity',
					'label'        => 'Производительность или вместимость',
					'name'         => 'tts_equipment_capacity',
					'type'         => 'text',
					'instructions' => 'Одной строкой, с единицами измерения: «до 40 м³/ч», «1000 тонн».',
				),
				array(
					'key'           => 'field_tts_equipment_purpose',
					'label'         => 'Назначение',
					'name'          => 'tts_equipment_purpose',
					'type'          => 'select',
					'instructions'  => 'По этому полю работает фильтр в общем каталоге и подбор в квизе на главной.',
					'required'      => 1,
					'choices'       => array(
						'dry-mix'        => 'Сухие смеси',
						'concrete'       => 'Товарный бетон и дороги',
						'vpi'            => 'ВПИ',
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
					'key'          => 'field_tts_direction_summary',
					'label'        => 'Описание для карточки',
					'name'         => 'tts_direction_summary',
					'type'         => 'textarea',
					'instructions' => 'Текст в плитке направления на главной и в каталоге. Одно-два предложения.',
					'rows'         => 3,
					'new_lines'    => '',
				),
				array(
					'key'           => 'field_tts_direction_cover',
					'label'         => 'Обложка',
					'name'          => 'tts_direction_cover',
					'type'          => 'image',
					'instructions'  => 'Горизонтальное фото для плитки направления, минимум 1200 px по ширине.',
					'return_format' => 'id',
					'preview_size'  => 'medium',
					'library'       => 'all',
				),
				array(
					'key'           => 'field_tts_direction_page',
					'label'         => 'Страница каталога',
					'name'          => 'tts_direction_page',
					'type'          => 'post_object',
					'instructions'  => 'Куда ведёт плитка направления.',
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
					'key'          => 'field_tts_project_object',
					'label'        => 'Тип объекта',
					'name'         => 'tts_project_object',
					'type'         => 'text',
					'instructions' => 'Необязательно. Например: завод сухих смесей 20 т/ч.',
					'required'     => 0,
				),
				array(
					'key'          => 'field_tts_project_summary',
					'label'        => 'Краткое описание',
					'name'         => 'tts_project_summary',
					'type'         => 'textarea',
					'instructions' => 'Что сделали на объекте — два-три предложения.',
					'rows'         => 3,
					'new_lines'    => '',
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
				array(
					'key'          => 'field_tts_project_button_label',
					'label'        => 'Текст кнопки',
					'name'         => 'tts_project_button_label',
					'type'         => 'text',
					'instructions' => 'Необязательно. Если текст и ссылка пустые, кнопка на карточке не показывается.',
				),
				array(
					'key'          => 'field_tts_project_button_url',
					'label'        => 'Ссылка кнопки',
					'name'         => 'tts_project_button_url',
					'type'         => 'url',
					'instructions' => 'Необязательно. Полный адрес вида https://…',
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

/** Услуги сервиса. */
function tts_fields_service_item(): void {
	acf_add_local_field_group(
		array(
			'key'                   => 'group_tts_service_item',
			'title'                 => 'Данные услуги',
			'fields'                => array(
				array(
					'key'          => 'field_tts_service_summary',
					'label'        => 'Краткое описание',
					'name'         => 'tts_service_summary',
					'type'         => 'textarea',
					'instructions' => 'Одно-два предложения для карточки услуги.',
					'rows'         => 3,
					'new_lines'    => '',
				),
				array(
					'key'          => 'field_tts_service_steps',
					'label'        => 'Этапы',
					'name'         => 'tts_service_steps',
					'type'         => 'repeater',
					'instructions' => 'Как проходит работа, по порядку. Нумерация на сайте считается сама, в заголовке цифру писать не нужно.',
					'layout'       => 'row',
					'button_label' => 'Добавить этап',
					'sub_fields'   => array(
						array(
							'key'      => 'field_tts_service_step_title',
							'label'    => 'Заголовок этапа',
							'name'     => 'tts_service_step_title',
							'type'     => 'text',
							'required' => 1,
						),
						array(
							'key'       => 'field_tts_service_step_text',
							'label'     => 'Описание этапа',
							'name'      => 'tts_service_step_text',
							'type'      => 'textarea',
							'rows'      => 3,
							'new_lines' => '',
						),
					),
				),
				array(
					'key'          => 'field_tts_service_benefits',
					'label'        => 'Преимущества',
					'name'         => 'tts_service_benefits',
					'type'         => 'repeater',
					'instructions' => 'Короткие пункты списка, по одному в строке.',
					'layout'       => 'table',
					'button_label' => 'Добавить преимущество',
					'sub_fields'   => array(
						array(
							'key'   => 'field_tts_service_benefit',
							'label' => 'Преимущество',
							'name'  => 'tts_service_benefit',
							'type'  => 'text',
						),
					),
				),
				array(
					'key'          => 'field_tts_service_button_label',
					'label'        => 'Текст кнопки',
					'name'         => 'tts_service_button_label',
					'type'         => 'text',
					'instructions' => 'Например: «Оставить заявку». Если пусто, подставится общий текст из настроек сайта.',
				),
				array(
					'key'          => 'field_tts_service_form_anchor',
					'label'        => 'Якорь формы на странице',
					'name'         => 'tts_service_form_anchor',
					'type'         => 'text',
					'instructions' => 'Куда кнопка прокручивает страницу: #contact. Со знаком решётки, без адреса сайта.',
				),
			),
			'location'              => array(
				array(
					array(
						'param'    => 'post_type',
						'operator' => '==',
						'value'    => 'service_item',
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

/** Общие настройки сайта: контакты, тексты кнопок, аналитика, матрица квиза. */
function tts_fields_settings(): void {
	acf_add_local_field_group(
		array(
			'key'                   => 'group_tts_settings',
			'title'                 => 'Настройки сайта',
			'fields'                => array(
				array(
					'key'       => 'field_tts_settings_tab_contacts',
					'label'     => 'Контакты',
					'name'      => '',
					'type'      => 'tab',
					'placement' => 'top',
				),
				array(
					'key'           => 'field_tts_settings_logo',
					'label'         => 'Логотип',
					'name'          => 'tts_settings_logo',
					'type'          => 'image',
					'instructions'  => 'Файл для шапки сайта: SVG или PNG с прозрачным фоном.',
					'return_format' => 'id',
					'preview_size'  => 'medium',
					'library'       => 'all',
				),
				array(
					'key'          => 'field_tts_settings_phone',
					'label'        => 'Телефон',
					'name'         => 'tts_settings_phone',
					'type'         => 'text',
					'instructions' => 'В том виде, в каком показываем на сайте: +7 700 000 00 00. Ссылку для звонка сайт соберёт сам.',
					'required'     => 1,
				),
				array(
					'key'          => 'field_tts_settings_email',
					'label'        => 'E-mail',
					'name'         => 'tts_settings_email',
					'type'         => 'email',
					'instructions' => 'Адрес для связи, который показывается в шапке и подвале.',
					'required'     => 1,
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
					'key'          => 'field_tts_settings_legal',
					'label'        => 'Реквизиты',
					'name'         => 'tts_settings_legal',
					'type'         => 'textarea',
					'instructions' => 'Наименование компании, БИН, юридический адрес. Каждый пункт с новой строки — они попадут в подвал.',
					'rows'         => 4,
				),
				array(
					'key'          => 'field_tts_settings_socials',
					'label'        => 'Соцсети и мессенджеры',
					'name'         => 'tts_settings_socials',
					'type'         => 'repeater',
					'instructions' => 'Ссылки в подвале. Пустых строк не оставляйте.',
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
					'key'       => 'field_tts_settings_tab_buttons',
					'label'     => 'Кнопки',
					'name'      => '',
					'type'      => 'tab',
					'placement' => 'top',
				),
				array(
					'key'          => 'field_tts_settings_header_button',
					'label'        => 'Текст главной кнопки в шапке',
					'name'         => 'tts_settings_header_button',
					'type'         => 'text',
					'instructions' => 'Например: «Подобрать оборудование».',
					'required'     => 1,
				),
				array(
					'key'          => 'field_tts_settings_form_button',
					'label'        => 'Текст кнопки отправки формы',
					'name'         => 'tts_settings_form_button',
					'type'         => 'text',
					'instructions' => 'Одинаковый для всех форм сайта. Например: «Отправить заявку».',
					'required'     => 1,
				),
				array(
					'key'          => 'field_tts_settings_form_success',
					'label'        => 'Текст подтверждения после отправки',
					'name'         => 'tts_settings_form_success',
					'type'         => 'text',
					'instructions' => 'Что человек видит вместо формы после отправки. Например: «Спасибо, мы свяжемся с вами в течение рабочего дня».',
					'required'     => 1,
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
					'instructions' => 'Вид GTM-XXXXXXX. Пока поле пустое, код на сайт не подключается.',
				),
				array(
					'key'          => 'field_tts_settings_ga4_id',
					'label'        => 'ID Google Analytics 4',
					'name'         => 'tts_settings_ga4_id',
					'type'         => 'text',
					'instructions' => 'Вид G-XXXXXXXXXX. Пока поле пустое, код на сайт не подключается.',
				),
				array(
					'key'          => 'field_tts_settings_ads_id',
					'label'        => 'ID Google Ads',
					'name'         => 'tts_settings_ads_id',
					'type'         => 'text',
					'instructions' => 'Вид AW-XXXXXXXXX. Пока поле пустое, код на сайт не подключается.',
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
