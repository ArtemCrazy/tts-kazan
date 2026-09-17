<?php
/**
 * Адреса разделов сайта.
 *
 * В вёрстке разделы называются короткими ключами (vpi, service, privacy).
 * Здесь ключ превращается в адрес страницы WordPress, чтобы шапка и подвал
 * не зависели от того, как страницы называются в админке.
 */

defined( 'ABSPATH' ) || exit;

/** Ключ раздела -> путь страницы (п. 4.1 ТЗ: адреса согласованы). */
function tts_paths(): array {
	return array(
		'home'        => '/',
		'catalog'     => '/catalog/',
		'smartdrymix' => '/catalog/smartdrymix/',
		'smartbeton'  => '/catalog/smartbeton/',
		'vpi'         => '/catalog/vpi/',
		'smartstock'  => '/catalog/smartstock/',
		'pkn'         => '/catalog/pkn/',
		'service'     => '/service/',
		'parts'       => '/parts/',
		'privacy'     => '/privacy-policy/',
		'personal'    => '/personal-data/',
		'cookie'      => '/cookie/',
	);
}

/** Полный адрес раздела. */
function tts_url( string $key ): string {
	$paths = tts_paths();
	return home_url( $paths[ $key ] ?? '/' );
}

/** Признак текущей страницы — для aria-current в меню. */
function tts_is_current( string $key ): bool {
	$page = get_queried_object();
	if ( ! $page instanceof WP_Post ) {
		return 'home' === $key && is_front_page();
	}
	return untrailingslashit( wp_parse_url( tts_url( $key ), PHP_URL_PATH ) )
		=== untrailingslashit( wp_parse_url( get_permalink( $page ), PHP_URL_PATH ) );
}

/** Атрибуты ссылки на раздел. */
function tts_link( string $key ): string {
	$out = 'href="' . esc_url( tts_url( $key ) ) . '"';
	if ( tts_is_current( $key ) ) {
		$out .= ' aria-current="page"';
	}
	return $out;
}

/** Направления каталога для меню и подвала. Позже возьмём из раздела «Направления». */
function tts_directions(): array {
	return array(
		array( 'smartdrymix', 'Заводы сухих смесей', 'SmartDryMix 5–50+ т/ч' ),
		array( 'smartbeton', 'Бетонные заводы', 'Товарный бетон, ЖБИ и дороги' ),
		array( 'vpi', 'Заводы ВПИ', 'Готовые линии QUNFENG + ТТС' ),
		array( 'smartstock', 'Цементные терминалы', 'SmartStock 1000–5000 тонн' ),
		array( 'pkn', 'Пневмокамерные насосы', 'ПКН 10–60 т/ч, подача до 250 м' ),
	);
}

/** Стрелка в кнопках — та же, что в статической вёрстке. */
function tts_arrow(): string {
	return '<svg class="btn__arrow" viewBox="0 0 24 24" width="24" height="24" aria-hidden="true" '
		. 'focusable="false"><path d="M4 12h15M13 6l6 6-6 6" fill="none" stroke="currentColor" '
		. 'stroke-width="2" stroke-linecap="square"></path></svg>';
}
