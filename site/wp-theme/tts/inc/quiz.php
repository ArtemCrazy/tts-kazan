<?php
/**
 * Данные встроенного квиза подбора (п. 7 ТЗ).
 *
 * Собираем их из матрицы рекомендаций в «Настройках сайта» и записей
 * «Оборудование». Ни одного названия модели и ни одной ссылки в коде:
 * добавил редактор строку матрицы — квиз сразу её показывает (п. 12.3 ТЗ).
 */

defined( 'ABSPATH' ) || exit;

/** Подписи типов объекта берём из самого поля, чтобы не держать второй список. */
function tts_quiz_object_labels(): array {
	$field = function_exists( 'acf_get_field' ) ? acf_get_field( 'tts_settings_quiz_object' ) : null;
	return $field['choices'] ?? array();
}

/**
 * Карточка модели для результата квиза. Текст и производительность можно
 * переопределить в строке матрицы, иначе они берутся из каталога.
 */
function tts_quiz_card( int $id, string $text = '', string $capacity = '' ): array {
	$photo = get_field( 'tts_equipment_photo', $id );
	$page  = get_field( 'tts_equipment_direction_page', $id );

	return array(
		'code'     => (string) get_field( 'tts_equipment_code', $id ),
		'name'     => get_the_title( $id ),
		'capacity' => $capacity ?: (string) get_field( 'tts_equipment_capacity', $id ),
		'text'     => $text ?: (string) get_field( 'tts_equipment_summary', $id ),
		'image'    => $photo ? (string) wp_get_attachment_image_url( (int) $photo, 'large' ) : '',
		'page'     => $page ? (string) get_permalink( (int) $page ) : '',
		'quote'    => tts_url( 'home' ) . '#contact',
	);
}

/**
 * Настройки квиза для скрипта: типы объектов, варианты ответов,
 * текст рекомендации и карточки моделей.
 */
function tts_quiz_config(): array {
	$labels = tts_quiz_object_labels();
	$matrix = (array) get_field( 'tts_settings_quiz_matrix', 'option' );

	$directions = array();
	foreach ( $matrix as $index => $row ) {
		$object = (string) ( $row['tts_settings_quiz_object'] ?? '' );
		if ( ! $object ) {
			continue;
		}
		$answer  = (string) ( $row['tts_settings_quiz_capacity'] ?? '' );
		$primary = (int) ( $row['tts_settings_quiz_primary'] ?? 0 );
		$alt     = (int) ( $row['tts_settings_quiz_alt'] ?? 0 );
		if ( ! $primary ) {
			continue; // строка без основной модели показывать нечего
		}

		// Ключ ответа не должен зависеть от текста: редактор может его переписать
		$key = 'answer-' . $index;

		if ( ! isset( $directions[ $object ] ) ) {
			$directions[ $object ] = array(
				'label'      => $labels[ $object ] ?? $object,
				'capacities' => array(),
				'recommend'  => array(),
				'cards'      => array(),
			);
		}

		$directions[ $object ]['capacities'][] = array( $key, $answer );
		$directions[ $object ]['recommend'][ $key ] = array(
			'title' => get_the_title( $primary ),
			'text'  => (string) ( $row['tts_settings_quiz_text'] ?? '' ),
		);

		$cards = array(
			tts_quiz_card(
				$primary,
				trim( (string) ( $row['tts_settings_quiz_primary_text'] ?? '' ) ),
				trim( (string) ( $row['tts_settings_quiz_primary_capacity'] ?? '' ) )
			),
		);
		if ( $alt ) {
			$cards[] = tts_quiz_card(
				$alt,
				trim( (string) ( $row['tts_settings_quiz_alt_text'] ?? '' ) ),
				trim( (string) ( $row['tts_settings_quiz_alt_capacity'] ?? '' ) )
			);
		}
		$directions[ $object ]['cards'][ $key ] = $cards;
	}

	return array( 'directions' => $directions );
}
