<?php
/**
 * Данные общего каталога (п. 8 ТЗ).
 *
 * Позиции берём из раздела «Оборудование», подписи направлений — из
 * таксономии «Направления». Добавил редактор модель — она сразу появляется
 * и в общем каталоге, и в фильтрах (п. 12.3 ТЗ).
 */

defined( 'ABSPATH' ) || exit;

/** Значения фильтров берём из полей записи, чтобы список был один. */
function tts_field_choices( string $name ): array {
	$field = function_exists( 'acf_get_field' ) ? acf_get_field( $name ) : null;
	return $field['choices'] ?? array();
}

/**
 * Позиции каталога для скрипта.
 *
 * Ключи совпадают с теми, что использует статическая версия: id, cat,
 * purpose, scale, code, status, name, capacity, text, features. Добавлены
 * image (адрес рендера) и page (страница направления).
 */
function tts_catalog_config(): array {
	$statuses = tts_field_choices( 'tts_equipment_status' );
	$items    = array();
	$labels   = array();
	$pages    = array();

	foreach ( get_terms( array( 'taxonomy' => 'direction', 'hide_empty' => false ) ) as $term ) {
		if ( ! $term instanceof WP_Term ) {
			continue;
		}
		$labels[ $term->slug ] = $term->name;
		$page                  = get_field( 'tts_direction_page', 'direction_' . $term->term_id );
		$pages[ $term->slug ]  = array(
			'href'  => $page ? (string) get_permalink( (int) $page ) : tts_url( 'catalog' ),
			'built' => true,
		);
	}

	foreach ( tts_items( 'equipment' ) as $post ) {
		$terms  = wp_get_post_terms( $post->ID, 'direction' );
		$term   = $terms && ! is_wp_error( $terms ) ? $terms[0] : null;
		$photo  = get_field( 'tts_equipment_photo', $post->ID );
		$page   = get_field( 'tts_equipment_direction_page', $post->ID );
		$status = (string) get_field( 'tts_equipment_status', $post->ID );

		$features = array();
		foreach ( tts_rows( get_field( 'tts_equipment_features', $post->ID ) ) as $row ) {
			$feature = (string) ( $row['tts_equipment_feature'] ?? '' );
			if ( $feature ) {
				$features[] = $feature;
			}
		}

		$items[] = array(
			'id'       => (string) $post->ID,
			'cat'      => $term ? $term->slug : '',
			'purpose'  => (string) get_field( 'tts_equipment_purpose', $post->ID ),
			'scale'    => (string) get_field( 'tts_equipment_scale', $post->ID ),
			'code'     => (string) get_field( 'tts_equipment_code', $post->ID ),
			'status'   => $statuses[ $status ] ?? $status,
			'name'     => get_the_title( $post ),
			'capacity' => (string) get_field( 'tts_equipment_capacity', $post->ID ),
			'text'     => (string) get_field( 'tts_equipment_summary', $post->ID ),
			'features' => $features,
			'image'    => $photo ? (string) wp_get_attachment_image_url( (int) $photo, 'large' ) : '',
			'page'     => $page ? (string) get_permalink( (int) $page ) : '',
		);
	}

	return array( 'items' => $items, 'labels' => $labels, 'pages' => $pages );
}
