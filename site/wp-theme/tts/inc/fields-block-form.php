<?php
/**
 * Поля блока «Форма заявки».
 *
 * Получатель заявок, тема письма и срок хранения — не здесь, а в
 * «Настройках сайта»: они общие для всех форм сайта.
 */

defined( 'ABSPATH' ) || exit;

/** Поля блока формы. */
function tts_block_fields_form(): void {
	if ( ! function_exists( 'acf_add_local_field_group' ) ) {
		return;
	}

	acf_add_local_field_group(
		array(
			'key'      => 'group_tts_block_form',
			'title'    => 'Форма заявки',
			'location' => tts_block_location( 'form' ),
			'fields'   => array(
				array(
					'key'   => 'field_tts_form_kicker',
					'label' => 'Надзаголовок',
					'name'  => 'tts_form_kicker',
					'type'  => 'text',
				),
				array(
					'key'      => 'field_tts_form_title',
					'label'    => 'Заголовок',
					'name'     => 'tts_form_title',
					'type'     => 'text',
					'required' => 1,
				),
				array(
					'key'       => 'field_tts_form_lead',
					'label'     => 'Описание',
					'name'      => 'tts_form_lead',
					'type'      => 'textarea',
					'rows'      => 3,
					'new_lines' => '',
				),
				array(
					'key'          => 'field_tts_form_office',
					'label'        => 'Название офиса',
					'name'         => 'tts_form_office',
					'type'         => 'text',
					'instructions' => 'Строка над контактами рядом с формой.',
				),
				array(
					'key'          => 'field_tts_form_details',
					'label'        => 'Контакты рядом с формой',
					'name'         => 'tts_form_details',
					'type'         => 'repeater',
					'button_label' => 'Добавить строку',
					'instructions' => 'По строке на телефон, адрес, режим работы. Публикуем только подтверждённые данные.',
					'layout'       => 'table',
					'sub_fields'   => array(
						array(
							'key'      => 'field_tts_form_detail',
							'label'    => 'Строка',
							'name'     => 'tts_form_detail',
							'type'     => 'text',
							'required' => 1,
						),
					),
				),
				array(
					'key'   => 'field_tts_form_card_title',
					'label' => 'Заголовок над полями',
					'name'  => 'tts_form_card_title',
					'type'  => 'text',
				),
				array(
					'key'       => 'field_tts_form_note',
					'label'     => 'Пояснение над полями',
					'name'      => 'tts_form_note',
					'type'      => 'textarea',
					'rows'      => 2,
					'new_lines' => '',
				),
				array(
					'key'          => 'field_tts_form_models_label',
					'label'        => 'Подпись списка моделей',
					'name'         => 'tts_form_models_label',
					'type'         => 'text',
					'instructions' => 'Например, «Модель», «Конфигурация», «Вместимость». Пусто — списка моделей в форме нет.',
				),
				array(
					'key'           => 'field_tts_form_models_direction',
					'label'         => 'Модели какого направления',
					'name'          => 'tts_form_models_direction',
					'type'          => 'taxonomy',
					'taxonomy'      => 'direction',
					'field_type'    => 'select',
					'return_format' => 'id',
					'add_term'      => 0,
					'save_terms'    => 0,
					'load_terms'    => 0,
					'allow_null'    => 1,
					'instructions'  => 'В список попадут модели направления с заполненным «Названием для заявки». '
						. 'Кнопка «Подобрать» в карточке выбирает модель в этом списке.',
				),
				array(
					'key'   => 'field_tts_form_direction_label',
					'label' => 'Подпись поля выбора',
					'name'  => 'tts_form_direction_label',
					'type'  => 'text',
				),
				array(
					'key'          => 'field_tts_form_directions',
					'label'        => 'Варианты выбора',
					'name'         => 'tts_form_directions',
					'type'         => 'repeater',
					'button_label' => 'Добавить вариант',
					'instructions' => 'Если оставить пустым, поля выбора в форме не будет.',
					'layout'       => 'table',
					'sub_fields'   => array(
						array(
							'key'      => 'field_tts_form_direction',
							'label'    => 'Вариант',
							'name'     => 'tts_form_direction',
							'type'     => 'text',
							'required' => 1,
						),
					),
				),
				array(
					'key'          => 'field_tts_form_second_label',
					'label'        => 'Подпись второго списка',
					'name'         => 'tts_form_second_label',
					'type'         => 'text',
					'instructions' => 'Например, «Срочность» или «Категория». Пусто — второго списка не будет.',
				),
				array(
					'key'          => 'field_tts_form_second_options',
					'label'        => 'Варианты второго списка',
					'name'         => 'tts_form_second_options',
					'type'         => 'repeater',
					'button_label' => 'Добавить вариант',
					'layout'       => 'table',
					'sub_fields'   => array(
						array(
							'key'      => 'field_tts_form_second_option',
							'label'    => 'Вариант',
							'name'     => 'tts_form_second_option',
							'type'     => 'text',
							'required' => 1,
						),
					),
				),
				array(
					'key'           => 'field_tts_form_picked',
					'label'         => 'Поле «Выбранное оборудование»',
					'name'          => 'tts_form_picked',
					'type'          => 'true_false',
					'instructions'  => 'Нужно на страницах с каталогом: кнопка «Получить КП» подставляет '
						. 'в это поле выбранную модель, а фильтры уходят в заявку служебно.',
					'ui'            => 1,
					'default_value' => 0,
				),
				array(
					'key'   => 'field_tts_form_comment_label',
					'label' => 'Подпись поля комментария',
					'name'  => 'tts_form_comment_label',
					'type'  => 'text',
				),
				array(
					'key'   => 'field_tts_form_comment_hint',
					'label' => 'Подсказка в поле комментария',
					'name'  => 'tts_form_comment_hint',
					'type'  => 'text',
				),
				array(
					'key'   => 'field_tts_form_submit',
					'label' => 'Текст кнопки',
					'name'  => 'tts_form_submit',
					'type'  => 'text',
				),
				array(
					'key'   => 'field_tts_form_done_title',
					'label' => 'Заголовок после отправки',
					'name'  => 'tts_form_done_title',
					'type'  => 'text',
				),
				array(
					'key'       => 'field_tts_form_done_text',
					'label'     => 'Текст после отправки',
					'name'      => 'tts_form_done_text',
					'type'      => 'textarea',
					'rows'      => 2,
					'new_lines' => '',
				),
				array(
					'key'   => 'field_tts_form_again',
					'label' => 'Текст кнопки «Заполнить ещё раз»',
					'name'  => 'tts_form_again',
					'type'  => 'text',
				),
				array(
					'key'           => 'field_tts_form_source',
					'label'         => 'Тип формы',
					'name'          => 'tts_form_source',
					'type'          => 'select',
					'instructions'  => 'Попадает в заявку и в аналитику — видно, с какой формы пришло обращение.',
					'choices'       => array(
						'general' => 'Общая заявка',
						'catalog' => 'Каталог или страница направления',
						'service' => 'Инженерный сервис',
						'parts'   => 'Запасные части',
					),
					'default_value' => 'general',
					'allow_null'    => 0,
					'return_format' => 'value',
				),
			),
		)
	);
}
add_action( 'acf/include_fields', 'tts_block_fields_form' );
