<?php
/**
 * Поля блока «Шапка раздела». Разметка — blocks/page-head.php.
 */

defined( 'ABSPATH' ) || exit;

/** Шапка раздела. */
function tts_block_fields_page_head(): void {
	if ( ! function_exists( 'acf_add_local_field_group' ) ) {
		return;
	}

	acf_add_local_field_group(
		array(
			'key'      => 'group_tts_block_page_head',
			'title'    => 'Шапка раздела',
			'location' => tts_block_location( 'page-head' ),
			'fields'   => array(
				array(
					'key'          => 'field_tts_page_head_kicker',
					'label'        => 'Надзаголовок',
					'name'         => 'tts_page_head_kicker',
					'type'         => 'text',
					'instructions' => 'Короткая строка над заголовком. Например: «QUNFENG + ТТС · готовые линии ВПИ».',
				),
				array(
					'key'          => 'field_tts_page_head_title',
					'label'        => 'Заголовок',
					'name'         => 'tts_page_head_title',
					'type'         => 'text',
					'required'     => 1,
					'instructions' => 'Главный заголовок страницы (H1). Один на страницу.',
				),
				array(
					'key'          => 'field_tts_page_head_lead',
					'label'        => 'Лид',
					'name'         => 'tts_page_head_lead',
					'type'         => 'textarea',
					'rows'         => 3,
					'new_lines'    => '',
					'instructions' => 'Два-три предложения под заголовком.',
				),
				array(
					'key'          => 'field_tts_page_head_crumb',
					'label'        => 'Подпись в крошках',
					'name'         => 'tts_page_head_crumb',
					'type'         => 'text',
					'instructions' => 'Последний элемент хлебных крошек. Пусто — берётся название страницы.',
				),
				array(
					'key'          => 'field_tts_page_head_under_catalog',
					'label'        => 'Страница внутри каталога',
					'name'         => 'tts_page_head_under_catalog',
					'type'         => 'true_false',
					'ui'           => 1,
					'instructions' => 'Добавляет в крошки промежуточный шаг «Каталог оборудования».',
					'default_value' => 0,
				),
				array(
					'key'           => 'field_tts_page_head_primary',
					'label'         => 'Главная кнопка',
					'name'          => 'tts_page_head_primary',
					'type'          => 'link',
					'return_format' => 'array',
					'instructions'  => 'Зелёная кнопка со стрелкой. Необязательно.',
				),
				array(
					'key'           => 'field_tts_page_head_secondary',
					'label'         => 'Вторая кнопка',
					'name'          => 'tts_page_head_secondary',
					'type'          => 'link',
					'return_format' => 'array',
					'instructions'  => 'Кнопка с рамкой. Необязательно.',
				),
				array(
					// Файлы фонов лежат в теме и прописаны в стилях: разметка
					// только называет раздел. Загружать своё фото сюда нельзя,
					// иначе фон разъедется с остальными страницами каталога.
					'key'           => 'field_tts_page_head_photo',
					'label'         => 'Фоновое фото',
					'name'          => 'tts_page_head_photo',
					'type'          => 'select',
					'instructions'  => 'Необязательно. Набор фонов темы: шапка становится тёмной, с фотографией под затемнением.',
					'choices'       => array(
						'catalog'     => 'Каталог оборудования',
						'smartdrymix' => 'Заводы сухих смесей',
						'smartbeton'  => 'Бетонные заводы',
						'vpi'         => 'Заводы ВПИ',
						'smartstock'  => 'Цементные терминалы',
						'pkn'         => 'Пневмокамерные насосы',
					),
					'allow_null'    => 1,
					'return_format' => 'value',
				),
				array(
					'key'          => 'field_tts_page_head_stats',
					'label'        => 'Показатели',
					'name'         => 'tts_page_head_stats',
					'type'         => 'repeater',
					'instructions' => 'Строка цифр под лидом. Обычно три-четыре.',
					'layout'       => 'table',
					'button_label' => 'Добавить показатель',
					'sub_fields'   => array(
						array(
							'key'          => 'field_tts_page_head_stat_value',
							'label'        => 'Значение',
							'name'         => 'tts_page_head_stat_value',
							'type'         => 'text',
							'required'     => 1,
							'instructions' => 'Например: «15–40» или «24/7».',
						),
						array(
							'key'          => 'field_tts_page_head_stat_label',
							'label'        => 'Подпись',
							'name'         => 'tts_page_head_stat_label',
							'type'         => 'text',
							'instructions' => 'Например: «м³/ч бетонной смеси».',
						),
					),
				),
			),
		)
	);
}
add_action( 'acf/include_fields', 'tts_block_fields_page_head' );
