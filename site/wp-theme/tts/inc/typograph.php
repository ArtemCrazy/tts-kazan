<?php
/**
 * Типограф: не даёт коротким словам болтаться в конце строки.
 *
 * Это перенос tools/typograph.py из статической версии. Там типограф
 * прогонялся по готовым страницам, и строки нигде не заканчивались на «и»,
 * «от» или «для». В WordPress текст приходит из админки без этой обработки,
 * поэтому переносы строк отличались от утверждённой вёрстки. Здесь та же
 * обработка делается на лету для каждой страницы — и для текста, который
 * редактор впишет потом.
 *
 * Правила совпадают со статической версией один в один: тот же список слов,
 * тире не отрывается от предыдущего слова, внутрь script и style не лезем.
 */

defined( 'ABSPATH' ) || exit;

/** Служебные слова, которые нельзя оставлять в конце строки (как в typograph.py). */
function tts_typo_short(): array {
	return array(
		'а', 'и', 'но', 'да', 'же', 'ли', 'бы', 'не', 'ни', 'то',
		'в', 'во', 'на', 'за', 'к', 'ко', 'о', 'об', 'от', 'по', 'до',
		'из', 'с', 'со', 'у',
	);
}

/**
 * Склеить служебные слова со следующим, тире — с предыдущим.
 *
 * Неразрывный пробел здесь считается пробелом, как \s в Python: без этого
 * повторная обработка склеивала бы уже склеенное.
 */
function tts_typo_text( string $text ): string {
	if ( '' === trim( $text ) ) {
		return $text;
	}

	$nbsp  = "\u{00A0}";
	$space = '[\s\x{00A0}]';
	$word  = '[^\s\x{00A0}<>&]+';

	// тире не отрывается от предшествующего слова
	$text = (string) preg_replace( '/([^\s\x{00A0}])[ ]+(—|–)(?=' . $space . ')/u', '$1' . $nbsp . '$2', $text );

	$short = array_flip( tts_typo_short() );

	return (string) preg_replace_callback(
		'/(?<![^\s\x{00A0}>(«"])(' . $word . ')[ ]+(?=' . $word . ')/u',
		static function ( array $m ) use ( $short, $nbsp ): string {
			$bare = trim( mb_strtolower( $m[1] ), '«"(' );
			return isset( $short[ $bare ] ) ? $m[1] . $nbsp : $m[0];
		},
		$text
	);
}

/** Пройти по текстовым узлам страницы, не трогая теги и содержимое script/style. */
function tts_typograph( string $html ): string {
	if ( '' === $html || false === strpos( $html, '<' ) ) {
		return $html;
	}

	$out  = '';
	$pos  = 0;
	$skip = null;

	if ( ! preg_match_all( '/<[^>]+>/', $html, $tags, PREG_OFFSET_CAPTURE ) ) {
		return $html;
	}

	foreach ( $tags[0] as $tag ) {
		list( $markup, $offset ) = $tag;
		$chunk = substr( $html, $pos, $offset - $pos );
		$out  .= $skip ? $chunk : tts_typo_text( $chunk );
		$out  .= $markup;
		$pos   = $offset + strlen( $markup );

		$name    = preg_match( '/^<\/?\s*([a-zA-Z0-9-]+)/', $markup, $found ) ? strtolower( $found[1] ) : '';
		$closing = 0 === strpos( $markup, '</' );
		if ( $skip ) {
			if ( $closing && $name === $skip ) {
				$skip = null;
			}
		} elseif ( ! $closing && in_array( $name, array( 'script', 'style', 'textarea' ), true ) ) {
			$skip = $name;
		}
	}

	$tail = substr( $html, $pos );
	return $out . ( $skip ? $tail : tts_typo_text( $tail ) );
}

/** Обработка всей страницы перед отправкой посетителю. */
function tts_typograph_start(): void {
	if ( is_admin() || is_feed() || wp_doing_ajax() || ( defined( 'REST_REQUEST' ) && REST_REQUEST ) ) {
		return;
	}
	ob_start( 'tts_typograph' );
}
add_action( 'template_redirect', 'tts_typograph_start', 0 );
