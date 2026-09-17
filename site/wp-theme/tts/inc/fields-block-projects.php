<?php
/**
 * Поля блока «Проекты». Разметка — blocks/projects.php.
 *
 * Данные самих карточек лежат у записей раздела «Проекты» (inc/fields.php),
 * здесь только поля секции.
 */

defined( 'ABSPATH' ) || exit;

/** Проекты. */
function tts_block_fields_projects(): void {
	if ( ! function_exists( 'acf_add_local_field_group' ) ) {
		return;
	}

	acf_add_local_field_group(
		array(
			'key'      => 'group_tts_block_projects',
			'title'    => 'Проекты',
			'location' => tts_block_location( 'projects' ),
			'fields'   => array(
				array(
					'key'   => 'field_tts_projects_kicker',
					'label' => 'Надзаголовок',
					'name'  => 'tts_projects_kicker',
					'type'  => 'text',
				),
				array(
					'key'      => 'field_tts_projects_title',
					'label'    => 'Заголовок',
					'name'     => 'tts_projects_title',
					'type'     => 'text',
					'required' => 1,
				),
				array(
					'key'       => 'field_tts_projects_lead',
					'label'     => 'Описание',
					'name'      => 'tts_projects_lead',
					'type'      => 'textarea',
					'rows'      => 3,
					'new_lines' => '',
				),
				array(
					'key'           => 'field_tts_projects_count',
					'label'         => 'Сколько проектов показать',
					'name'          => 'tts_projects_count',
					'type'          => 'number',
					'instructions'  => 'Карточки берутся из раздела «Проекты» в заданном там порядке. 0 — показать все.',
					'default_value' => 3,
					'min'           => 0,
					'step'          => 1,
				),
				array(
					'key'           => 'field_tts_projects_button',
					'label'         => 'Кнопка под карточками',
					'name'          => 'tts_projects_button',
					'type'          => 'link',
					'return_format' => 'array',
					'instructions'  => 'Обычно ведёт на форму заявки. Без текста кнопка не показывается.',
				),
			),
		)
	);
}
add_action( 'acf/include_fields', 'tts_block_fields_projects' );
