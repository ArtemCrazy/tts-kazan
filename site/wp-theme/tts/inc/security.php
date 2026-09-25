<?php
/**
 * Защита сайта: то, что WordPress по умолчанию оставляет открытым.
 *
 * Найдено аудитом перед запуском (LAUNCH_AUDIT.md):
 *  - список пользователей в REST API и архивы авторов выдавали логин
 *    администратора — половину пары для подбора пароля;
 *  - xmlrpc.php позволяет перебирать пароли пачками в обход лимита попыток;
 *  - не отдавались базовые защитные заголовки.
 */

defined( 'ABSPATH' ) || exit;

/**
 * Список пользователей в REST API — только для тех, кто вошёл и может
 * смотреть авторов. Гостю отдаём ошибку, а не логины.
 */
function tts_restrict_users_endpoint( $result ) {
	if ( ! empty( $result ) ) {
		return $result;
	}
	$route = $GLOBALS['wp']->query_vars['rest_route'] ?? '';
	if ( is_string( $route ) && str_starts_with( ltrim( $route, '/' ), 'wp/v2/users' ) && ! current_user_can( 'list_users' ) ) {
		return new WP_Error(
			'rest_user_cannot_view',
			'Список пользователей закрыт.',
			array( 'status' => rest_authorization_required_code() )
		);
	}
	return $result;
}
add_filter( 'rest_authentication_errors', 'tts_restrict_users_endpoint', 20 );

/** Архивы авторов сайту не нужны: по ним тоже виден логин. */
function tts_disable_author_archives(): void {
	if ( is_admin() || ! is_author() ) {
		return;
	}
	wp_safe_redirect( home_url( '/' ), 301 );
	exit;
}
add_action( 'template_redirect', 'tts_disable_author_archives' );

/** Ссылка на архив автора в разметке тоже ни к чему. */
add_filter( 'author_link', static fn() => home_url( '/' ) );

/** XML-RPC не используется: ни приложений, ни пингбеков. */
add_filter( 'xmlrpc_enabled', '__return_false' );
add_filter( 'xmlrpc_methods', '__return_empty_array' );

/** Ссылки на отключённый xmlrpc в шапке страницы только путают проверки. */
function tts_drop_xmlrpc_links(): void {
	remove_action( 'wp_head', 'rsd_link' );
	remove_action( 'wp_head', 'wlwmanifest_link' );
}
add_action( 'init', 'tts_drop_xmlrpc_links' );

/**
 * RSS-ленты: блога на сайте нет, ленты пустые и только плодят лишние адреса
 * для поисковиков. Любой запрос ленты уводим на главную.
 */
function tts_disable_feeds(): void {
	wp_safe_redirect( home_url( '/' ), 301 );
	exit;
}
foreach ( array( 'do_feed', 'do_feed_rdf', 'do_feed_rss', 'do_feed_rss2', 'do_feed_atom',
	'do_feed_rss2_comments', 'do_feed_atom_comments' ) as $tts_feed_hook ) {
	add_action( $tts_feed_hook, 'tts_disable_feeds', 1 );
}
remove_action( 'wp_head', 'feed_links', 2 );

/** Версию WordPress в разметке не показываем. */
add_filter( 'the_generator', '__return_empty_string' );

/**
 * Защитные заголовки. HSTS не задаём из темы: его правильнее включать
 * на стороне сервера и только когда HTTPS работает наверняка.
 */
function tts_security_headers(): void {
	if ( headers_sent() ) {
		return;
	}
	header_remove( 'X-Powered-By' );
	header( 'X-Content-Type-Options: nosniff' );
	header( 'X-Frame-Options: SAMEORIGIN' );
	header( 'Referrer-Policy: strict-origin-when-cross-origin' );
	header( 'Permissions-Policy: geolocation=(), microphone=(), camera=(), interest-cohort=()' );
}
add_action( 'send_headers', 'tts_security_headers' );
