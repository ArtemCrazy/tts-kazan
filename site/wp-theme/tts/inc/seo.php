<?php
/**
 * Разметка Schema.org (п. 14 ТЗ).
 *
 * Выводим только то, что реально есть на странице и подтверждено заказчиком:
 *  - BreadcrumbList — по тем же крошкам, что показаны посетителю;
 *  - FAQPage — только по видимым вопросам блока «Вопросы и ответы»;
 *  - Organization — только если реквизиты заполнены в «Настройках сайта».
 *
 * Фиктивных цен, наличия и незаполненных полей в разметке быть не должно:
 * это прямое требование п. 14 ТЗ.
 *
 * Заголовки, описания, canonical, карту сайта и Open Graph делает плагин
 * Rank Math — здесь мы их не дублируем.
 */

defined( 'ABSPATH' ) || exit;

/** Собранные на странице данные для разметки. */
function &tts_schema_store(): array {
	static $store = array( 'crumbs' => array(), 'faq' => array() );
	return $store;
}

/** Крошки страницы: вызывается из шапки раздела. */
function tts_schema_crumbs( array $crumbs ): void {
	$store           = &tts_schema_store();
	$store['crumbs'] = $crumbs;
}

/** Видимый вопрос из блока FAQ. */
function tts_schema_faq( string $question, string $answer ): void {
	$store         = &tts_schema_store();
	$store['faq'][] = array( $question, $answer );
}

/** Организация — по подтверждённым данным из настроек. */
function tts_schema_organization(): ?array {
	if ( ! function_exists( 'get_field' ) ) {
		return null;
	}

	$phone = tts_setting( 'tts_settings_phone' );
	$email = tts_setting( 'tts_settings_email' );
	$address = tts_setting( 'tts_settings_address' );
	if ( ! $phone && ! $email && ! $address ) {
		return null; // реквизиты ещё не переданы — разметку не выдумываем
	}

	$data = array(
		'@type' => 'Organization',
		'name'  => get_bloginfo( 'name' ),
		'url'   => home_url( '/' ),
	);

	$data['logo'] = tts_logo( 'dark' )[0];
	if ( $phone ) {
		$data['telephone'] = $phone;
	}
	if ( $email ) {
		$data['email'] = $email;
	}
	if ( $address ) {
		$data['address'] = array( '@type' => 'PostalAddress', 'streetAddress' => $address );
	}

	$socials = array();
	foreach ( tts_rows( get_field( 'tts_settings_socials', 'option' ) ) as $row ) {
		$url = (string) ( $row['tts_settings_social_url'] ?? '' );
		if ( $url ) {
			$socials[] = $url;
		}
	}
	if ( $socials ) {
		$data['sameAs'] = $socials;
	}

	return $data;
}

/** Вывод разметки в конце страницы, когда всё содержимое уже собрано. */
function tts_schema_output(): void {
	if ( is_admin() ) {
		return;
	}

	$store = tts_schema_store();
	$graph = array();

	$organization = tts_schema_organization();
	if ( $organization ) {
		$graph[] = $organization;
	}

	if ( count( $store['crumbs'] ) > 1 ) {
		$items = array();
		foreach ( $store['crumbs'] as $index => $crumb ) {
			$item = array(
				'@type'    => 'ListItem',
				'position' => $index + 1,
				'name'     => $crumb['name'],
			);
			if ( ! empty( $crumb['url'] ) ) {
				$item['item'] = $crumb['url'];
			}
			$items[] = $item;
		}
		$graph[] = array( '@type' => 'BreadcrumbList', 'itemListElement' => $items );
	}

	if ( $store['faq'] ) {
		$questions = array();
		foreach ( $store['faq'] as list( $question, $answer ) ) {
			$questions[] = array(
				'@type'          => 'Question',
				'name'           => $question,
				'acceptedAnswer' => array(
					'@type' => 'Answer',
					'text'  => wp_strip_all_tags( $answer ),
				),
			);
		}
		$graph[] = array( '@type' => 'FAQPage', 'mainEntity' => $questions );
	}

	if ( ! $graph ) {
		return;
	}

	printf(
		'<script type="application/ld+json">%s</script>' . "\n",
		wp_json_encode(
			array( '@context' => 'https://schema.org', '@graph' => $graph ),
			JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES
		)
	);
}
add_action( 'wp_footer', 'tts_schema_output', 20 );

/*
 * Своя разметка Rank Math нам не подходит: он объявляет страницы статьями
 * (Article), подставляет автором служебный логин (Person) и добавляет
 * организацию без подтверждённых реквизитов. По п. 14 ТЗ разметка — только
 * по реальным данным, поэтому выводим свою (выше), а его отключаем.
 * Заголовки, описания, canonical и Open Graph Rank Math выводит по-прежнему.
 */
add_filter( 'rank_math/json_ld', '__return_empty_array', 99 );
