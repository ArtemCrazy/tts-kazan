<?php
/**
 * Поля блока «Подбор оборудования».
 *
 * Здесь только подписи и тексты вокруг квиза. Сами варианты ответов и
 * рекомендации — в «Настройках сайта», в матрице подбора: так они не
 * дублируются между страницами.
 */

defined( 'ABSPATH' ) || exit;

/** Поля блока квиза. */
function tts_block_fields_quiz(): void {
	if ( ! function_exists( 'acf_add_local_field_group' ) ) {
		return;
	}

	acf_add_local_field_group(
		array(
			'key'      => 'group_tts_block_quiz',
			'title'    => 'Подбор оборудования',
			'location' => tts_block_location( 'quiz' ),
			'fields'   => array(
				array(
					'key'   => 'field_tts_quiz_kicker',
					'label' => 'Надзаголовок',
					'name'  => 'tts_quiz_kicker',
					'type'  => 'text',
				),
				array(
					'key'      => 'field_tts_quiz_title',
					'label'    => 'Заголовок',
					'name'     => 'tts_quiz_title',
					'type'     => 'text',
					'required' => 1,
				),
				array(
					'key'       => 'field_tts_quiz_lead',
					'label'     => 'Описание',
					'name'      => 'tts_quiz_lead',
					'type'      => 'textarea',
					'rows'      => 3,
					'new_lines' => '',
				),
				array(
					'key'          => 'field_tts_quiz_label_object',
					'label'        => 'Подпись поля «Тип объекта»',
					'name'         => 'tts_quiz_label_object',
					'type'         => 'text',
					'instructions' => 'Варианты в этом поле берутся из матрицы подбора в «Настройках сайта».',
				),
				array(
					'key'   => 'field_tts_quiz_label_capacity',
					'label' => 'Подпись поля «Производительность»',
					'name'  => 'tts_quiz_label_capacity',
					'type'  => 'text',
				),
				array(
					'key'   => 'field_tts_quiz_label_stage',
					'label' => 'Подпись поля «Стадия проекта»',
					'name'  => 'tts_quiz_label_stage',
					'type'  => 'text',
				),
				array(
					'key'          => 'field_tts_quiz_stages',
					'label'        => 'Стадии проекта',
					'name'         => 'tts_quiz_stages',
					'type'         => 'repeater',
					'button_label' => 'Добавить стадию',
					'instructions' => 'Варианты третьего вопроса.',
					'layout'       => 'table',
					'sub_fields'   => array(
						array(
							'key'      => 'field_tts_quiz_stage',
							'label'    => 'Стадия',
							'name'     => 'tts_quiz_stage',
							'type'     => 'text',
							'required' => 1,
						),
					),
				),
				array(
					'key'   => 'field_tts_quiz_submit',
					'label' => 'Текст кнопки',
					'name'  => 'tts_quiz_submit',
					'type'  => 'text',
				),
				array(
					'key'   => 'field_tts_quiz_result_kicker',
					'label' => 'Надзаголовок результата',
					'name'  => 'tts_quiz_result_kicker',
					'type'  => 'text',
				),
				array(
					'key'           => 'field_tts_quiz_cta',
					'label'         => 'Кнопка в результате',
					'name'          => 'tts_quiz_cta',
					'type'          => 'link',
					'return_format' => 'array',
					'instructions'  => 'Обычно ведёт к форме заявки.',
				),
				array(
					'key'   => 'field_tts_quiz_matches_title',
					'label' => 'Заголовок над карточками',
					'name'  => 'tts_quiz_matches_title',
					'type'  => 'text',
				),
				array(
					'key'       => 'field_tts_quiz_matches_note',
					'label'     => 'Пояснение над карточками',
					'name'      => 'tts_quiz_matches_note',
					'type'      => 'textarea',
					'rows'      => 2,
					'new_lines' => '',
				),
			),
		)
	);
}
add_action( 'acf/include_fields', 'tts_block_fields_quiz' );
