<?php
/**
 * Тема ТТС Инжиниринг Казахстан.
 *
 * Стили и скрипты — те же файлы, что в статической версии сайта: они лежат
 * в assets/ внутри темы и обновляются скриптом tools/wp/deploy-theme.py.
 * Метка версии считается по времени изменения файла, иначе браузер посетителя
 * отдаёт старую копию и правки «не появляются».
 */

defined( 'ABSPATH' ) || exit;

require_once get_theme_file_path( 'inc/urls.php' );
require_once get_theme_file_path( 'inc/parts.php' );
require_once get_theme_file_path( 'inc/cpt.php' );

require_once get_theme_file_path( 'inc/blocks.php' );
require_once get_theme_file_path( 'inc/quiz.php' );
require_once get_theme_file_path( 'inc/lead.php' );
require_once get_theme_file_path( 'inc/catalog.php' );
require_once get_theme_file_path( 'inc/analytics.php' );
require_once get_theme_file_path( 'inc/seo.php' );

// Поля появляются только вместе с плагином Secure Custom Fields.
// Файлы полей блоков подключаем все: каждый блок описывает свои поля сам.
require_once get_theme_file_path( 'inc/fields.php' );
foreach ( (array) glob( get_theme_file_path( 'inc/fields-block*.php' ) ) as $part ) {
	require_once $part;
}

/** Возможности темы. */
function tts_setup(): void {
	add_theme_support( 'title-tag' );
	add_theme_support( 'post-thumbnails' );
	add_theme_support( 'html5', array( 'search-form', 'gallery', 'caption', 'style', 'script' ) );
	add_theme_support( 'responsive-embeds' );
	add_theme_support( 'align-wide' );
	// Редактор показывает блоки в оформлении сайта, а не в стандартном.
	add_theme_support( 'editor-styles' );
	add_editor_style( 'assets/css/style.css' );
	add_editor_style( 'assets/css/page.css' );
}
add_action( 'after_setup_theme', 'tts_setup' );

/** Адрес и метка версии файла в assets. */
function tts_asset( string $path ): array {
	$file = get_theme_file_path( 'assets/' . $path );
	return array(
		get_theme_file_uri( 'assets/' . $path ),
		file_exists( $file ) ? (string) filemtime( $file ) : '1',
	);
}

/** Стили и скрипты страницы. */
function tts_assets(): void {
	$base = array( 'fonts/montserrat.css', 'fonts/actay.css', 'css/style.css', 'css/page.css' );
	foreach ( $base as $i => $path ) {
		list( $url, $ver ) = tts_asset( $path );
		wp_enqueue_style( 'tts-base-' . $i, $url, array(), $ver );
	}

	list( $url, $ver ) = tts_asset( 'js/app.js' );
	wp_enqueue_script( 'tts-app', $url, array(), $ver, array( 'strategy' => 'defer' ) );
}
add_action( 'wp_enqueue_scripts', 'tts_assets' );

/**
 * Дополнительные стили и скрипты для конкретной страницы.
 * Шаблон вызывает это до get_header(), поэтому каталог и квиз не грузятся
 * там, где они не нужны (п. 15.1 ТЗ: не подключать лишнее).
 */
function tts_need( string ...$names ): void {
	add_action(
		'wp_enqueue_scripts',
		static function () use ( $names ) {
			foreach ( $names as $name ) {
				$is_css = str_ends_with( $name, '.css' );
				list( $url, $ver ) = tts_asset( ( $is_css ? 'css/' : 'js/' ) . $name );
				$handle = 'tts-' . sanitize_key( $name );
				if ( $is_css ) {
					wp_enqueue_style( $handle, $url, array( 'tts-base-3' ), $ver );
				} else {
					wp_enqueue_script( $handle, $url, array(), $ver, array( 'strategy' => 'defer' ) );
				}
			}
		},
		20
	);
}

/** Шрифты и значки грузятся заранее: иначе первый экран мигает системным шрифтом. */
function tts_preload_fonts(): void {
	foreach ( array( 'fonts/montserrat-cyrillic.woff2', 'fonts/actay-wide.woff2' ) as $path ) {
		list( $url ) = tts_asset( $path );
		printf(
			'<link rel="preload" href="%s" as="font" type="font/woff2" crossorigin>' . "\n",
			esc_url( $url )
		);
	}
	list( $icon )  = tts_asset( 'img/favicon.png' );
	list( $touch ) = tts_asset( 'img/apple-touch-icon.png' );
	printf( '<link rel="icon" href="%s" type="image/png">' . "\n", esc_url( $icon ) );
	printf( '<link rel="apple-touch-icon" href="%s">' . "\n", esc_url( $touch ) );
}
add_action( 'wp_head', 'tts_preload_fonts', 2 );

/**
 * Разметка страниц построена на своих классах, и стандартные стили блочной
 * темы ей только мешают. Оставляем те, что нужны содержимому редактора.
 */
function tts_trim_default_styles(): void {
	wp_dequeue_style( 'wp-block-library-theme' );
	wp_dequeue_style( 'classic-theme-styles' );
	wp_dequeue_style( 'global-styles' );
}
add_action( 'wp_enqueue_scripts', 'tts_trim_default_styles', 100 );
