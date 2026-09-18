<?php
/**
 * Поля блока «Таблица сравнения». Разметка — blocks/matrix.php.
 */

defined( 'ABSPATH' ) || exit;

/** Таблица сравнения конфигураций. */
function tts_block_fields_matrix(): void {
	if ( ! function_exists( 'acf_add_local_field_group' ) ) {
		return;
	}

	acf_add_local_field_group(
		array(
			'key'      => 'group_tts_block_matrix',
			'title'    => 'Таблица сравнения',
			'location' => tts_block_location( 'matrix' ),
			'fields'   => array(
				array(
					'key'          => 'field_tts_matrix_kicker',
					'label'        => 'Надзаголовок',
					'name'         => 'tts_matrix_kicker',
					'type'         => 'text',
					'instructions' => 'Например: «Сравнение конфигураций».',
				),
				array(
					'key'   => 'field_tts_matrix_title',
					'label' => 'Заголовок',
					'name'  => 'tts_matrix_title',
					'type'  => 'text',
				),
				array(
					'key'       => 'field_tts_matrix_lead',
					'label'     => 'Лид',
					'name'      => 'tts_matrix_lead',
					'type'      => 'textarea',
					'rows'      => 3,
					'new_lines' => '',
				),
				array(
					// Тёмного тона нет: таблица сравнения в вёрстке всегда
					// лежит на светлой поверхности.
					'key'           => 'field_tts_matrix_tone',
					'label'         => 'Фон секции',
					'name'          => 'tts_matrix_tone',
					'type'          => 'select',
					'instructions'  => 'Чередуйте фон у соседних секций, чтобы страница не выглядела монотонной.',
					'choices'       => array(
						'light' => 'Светлый',
						'muted' => 'Приглушённый',
					),
					'default_value' => 'light',
					'allow_null'    => 0,
					'return_format' => 'value',
				),
				array(
					'key'           => 'field_tts_matrix_continue',
					'label'         => 'Продолжение предыдущей секции',
					'name'          => 'tts_matrix_continue',
					'type'          => 'true_false',
					'instructions'  => 'Таблица встаёт сразу под карточками предыдущей секции, без своих отступов — '
						. 'как на страницах ВПИ и ПКН. Фон выберите тот же, что у предыдущей секции.',
					'ui'            => 1,
					'default_value' => 0,
				),
				array(
					'key'          => 'field_tts_matrix_columns',
					'label'        => 'Столбцы',
					'name'         => 'tts_matrix_columns',
					'type'         => 'repeater',
					'instructions' => 'Шапка таблицы. Первый столбец — это название строки («Комплектация»), остальные заполняются ячейками. На телефоне шапка скрывается, и эти же заголовки становятся подписями ячеек.',
					'layout'       => 'table',
					'button_label' => 'Добавить столбец',
					'min'          => 2,
					'sub_fields'   => array(
						array(
							'key'      => 'field_tts_matrix_column',
							'label'    => 'Заголовок столбца',
							'name'     => 'tts_matrix_column',
							'type'     => 'text',
							'required' => 1,
						),
					),
				),
				array(
					'key'          => 'field_tts_matrix_rows',
					'label'        => 'Строки',
					'name'         => 'tts_matrix_rows',
					'type'         => 'repeater',
					'instructions' => 'Ячейки идут по порядку столбцов, начиная со второго. Лишние ячейки останутся без подписи на телефоне.',
					'layout'       => 'row',
					'button_label' => 'Добавить строку',
					'sub_fields'   => array(
						array(
							'key'          => 'field_tts_matrix_row_label',
							'label'        => 'Название строки',
							'name'         => 'tts_matrix_row_label',
							'type'         => 'text',
							'required'     => 1,
							'instructions' => 'Первая ячейка строки. Например: «SmartBeton 30 S + QS 1000–1200».',
						),
						array(
							'key'          => 'field_tts_matrix_cells',
							'label'        => 'Ячейки',
							'name'         => 'tts_matrix_cells',
							'type'         => 'repeater',
							'layout'       => 'table',
							'button_label' => 'Добавить ячейку',
							'sub_fields'   => array(
								array(
									'key'   => 'field_tts_matrix_cell',
									'label' => 'Значение',
									'name'  => 'tts_matrix_cell',
									'type'  => 'text',
								),
							),
						),
					),
				),
				array(
					'key'          => 'field_tts_matrix_note',
					'label'        => 'Примечание под таблицей',
					'name'         => 'tts_matrix_note',
					'type'         => 'textarea',
					'rows'         => 3,
					'new_lines'    => '',
					'instructions' => 'Оговорки и уточнения: что подтверждается после инженерной сверки и т. п.',
				),
			),
		)
	);
}
add_action( 'acf/include_fields', 'tts_block_fields_matrix' );
