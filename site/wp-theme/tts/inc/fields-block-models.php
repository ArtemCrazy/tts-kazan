<?php
/**
 * Поля блока «Модели оборудования». Разметка — blocks/models.php.
 */

defined( 'ABSPATH' ) || exit;

/**
 * Подписи статусов моделей для карточки.
 *
 * Берём из самого поля, а не вторым списком: копия уже один раз разъехалась
 * с полем, и подпись пропала (см. комментарий в inc/cpt.php).
 *
 * @return array<string,string>
 */
function tts_equipment_status_labels(): array {
	$field = function_exists( 'acf_get_field' ) ? acf_get_field( 'tts_equipment_status' ) : null;
	return (array) ( $field['choices'] ?? array() );
}

/** Модели оборудования. */
function tts_block_fields_models(): void {
	if ( ! function_exists( 'acf_add_local_field_group' ) ) {
		return;
	}

	acf_add_local_field_group(
		array(
			'key'      => 'group_tts_block_models',
			'title'    => 'Модели оборудования',
			'location' => tts_block_location( 'models' ),
			'fields'   => array(
				array(
					'key'          => 'field_tts_models_kicker',
					'label'        => 'Надзаголовок',
					'name'         => 'tts_models_kicker',
					'type'         => 'text',
					'instructions' => 'Например: «Комплектации».',
				),
				array(
					'key'   => 'field_tts_models_title',
					'label' => 'Заголовок',
					'name'  => 'tts_models_title',
					'type'  => 'text',
				),
				array(
					'key'       => 'field_tts_models_lead',
					'label'     => 'Лид',
					'name'      => 'tts_models_lead',
					'type'      => 'textarea',
					'rows'      => 3,
					'new_lines' => '',
				),
				array(
					'key'           => 'field_tts_models_direction',
					'label'         => 'Направление',
					'name'          => 'tts_models_direction',
					'type'          => 'taxonomy',
					'instructions'  => 'Показываются модели с этим направлением. Пусто — всё оборудование. Тексты, фото и порядок карточек ведутся в разделе «Оборудование».',
					'taxonomy'      => 'direction',
					'field_type'    => 'select',
					'add_term'      => 0,
					'save_terms'    => 0,
					'load_terms'    => 0,
					'allow_null'    => 1,
					'multiple'      => 0,
					'return_format' => 'id',
				),
				array(
					'key'           => 'field_tts_models_limit',
					'label'         => 'Сколько показывать',
					'name'          => 'tts_models_limit',
					'type'          => 'number',
					'instructions'  => '0 — показать все модели направления.',
					'default_value' => 0,
					'min'           => 0,
					'step'          => 1,
				),
				array(
					// В вёрстке есть только один модификатор сетки — models--3.
					// Без него карточки идут по две в ряд.
					'key'           => 'field_tts_models_grid',
					'label'         => 'Сетка',
					'name'          => 'tts_models_grid',
					'type'          => 'select',
					'instructions'  => 'Пусто — две карточки в ряд.',
					'choices'       => array(
						'models--3' => 'Три в ряд',
					),
					'allow_null'    => 1,
					'return_format' => 'value',
				),
				array(
					// Тёмного тона нет: карточка модели — светлая поверхность,
					// на тёмном фоне её в вёрстке не бывает.
					'key'           => 'field_tts_models_tone',
					'label'         => 'Фон секции',
					'name'          => 'tts_models_tone',
					'type'          => 'select',
					'instructions'  => 'Чередуйте фон у соседних секций, чтобы страница не выглядела монотонной.',
					'choices'       => array(
						'light' => 'Светлый',
						'muted' => 'Приглушённый',
					),
					'default_value' => 'muted',
					'allow_null'    => 0,
					'return_format' => 'value',
				),
				array(
					'key'           => 'field_tts_models_cta',
					'label'         => 'Подпись кнопки в карточке',
					'name'          => 'tts_models_cta',
					'type'          => 'text',
					'instructions'  => 'Кнопка подставляет модель в форму заявки. Пусто — кнопки в карточках не будет.',
					'default_value' => 'Запросить комплектацию',
				),
			),
		)
	);
}
add_action( 'acf/include_fields', 'tts_block_fields_models' );
