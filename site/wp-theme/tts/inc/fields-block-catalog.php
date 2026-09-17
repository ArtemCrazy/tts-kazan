<?php
/**
 * Поля блока «Общий каталог».
 *
 * Позиции, направления и значения фильтров берутся из раздела
 * «Оборудование», поэтому здесь только тексты вокруг каталога.
 */

defined( 'ABSPATH' ) || exit;

/** Поля блока каталога. */
function tts_block_fields_catalog(): void {
	if ( ! function_exists( 'acf_add_local_field_group' ) ) {
		return;
	}

	acf_add_local_field_group(
		array(
			'key'      => 'group_tts_block_catalog',
			'title'    => 'Общий каталог',
			'location' => tts_block_location( 'catalog' ),
			'fields'   => array(
				array(
					'key'          => 'field_tts_catalog_title',
					'label'        => 'Заголовок',
					'name'         => 'tts_catalog_title',
					'type'         => 'text',
					'instructions' => 'Над табами направлений.',
				),
				array(
					'key'       => 'field_tts_catalog_lead',
					'label'     => 'Описание',
					'name'      => 'tts_catalog_lead',
					'type'      => 'textarea',
					'rows'      => 3,
					'new_lines' => '',
				),
				array(
					'key'          => 'field_tts_catalog_all_label',
					'label'        => 'Подпись таба «всё оборудование»',
					'name'         => 'tts_catalog_all_label',
					'type'         => 'text',
					'instructions' => 'Остальные табы — это направления из раздела «Оборудование».',
				),
			),
		)
	);
}
add_action( 'acf/include_fields', 'tts_block_fields_catalog' );
