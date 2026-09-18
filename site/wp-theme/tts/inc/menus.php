<?php
/**
 * Меню шапки и подвала (п. 5.1, 5.2 и 12.3 ТЗ).
 *
 * Пункты меню редактор правит сам: «Внешний вид → Меню». Разметка при этом
 * остаётся той же, что в статической версии, поэтому меню выводим своим
 * кодом, а не стандартным wp_nav_menu().
 *
 * Шапка: пункт без вложенных — обычная ссылка, пункт с вложенными —
 * выпадающий список. Подпись под пунктом выпадающего списка задаётся полем
 * «Подпись под пунктом» у пункта меню.
 *
 * Подвал: три колонки, у каждой своё меню. Заголовок колонки — название меню.
 */

defined( 'ABSPATH' ) || exit;

/** Области меню, которые видит редактор. */
function tts_menu_locations(): array {
	return array(
		'header'   => 'Шапка сайта',
		'footer-1' => 'Подвал: первая колонка',
		'footer-2' => 'Подвал: вторая колонка',
		'footer-3' => 'Подвал: третья колонка',
	);
}

add_action(
	'after_setup_theme',
	static function (): void {
		register_nav_menus( tts_menu_locations() );
	}
);

/** Пункты меню из области, деревом: верхний уровень и вложенные. */
function tts_menu_tree( string $location ): array {
	$locations = get_nav_menu_locations();
	if ( empty( $locations[ $location ] ) ) {
		return array();
	}
	$items = wp_get_nav_menu_items( $locations[ $location ] );
	if ( ! $items ) {
		return array();
	}

	$tree = array();
	foreach ( $items as $item ) {
		if ( ! (int) $item->menu_item_parent ) {
			$tree[ $item->ID ] = array(
				'item'     => $item,
				'children' => array(),
			);
		}
	}
	foreach ( $items as $item ) {
		$parent = (int) $item->menu_item_parent;
		if ( $parent && isset( $tree[ $parent ] ) ) {
			$tree[ $parent ]['children'][] = $item;
		}
	}
	return array_values( $tree );
}

/** Название меню в области — заголовок колонки подвала. */
function tts_menu_title( string $location ): string {
	$locations = get_nav_menu_locations();
	if ( empty( $locations[ $location ] ) ) {
		return '';
	}
	$menu = wp_get_nav_menu_object( $locations[ $location ] );
	return $menu ? (string) $menu->name : '';
}

/**
 * Адрес пункта. Ссылки вида «/#company» считаются от адреса сайта:
 * так меню не ломается при переезде сайта на другой домен или в другую папку.
 */
function tts_menu_url( WP_Post $item ): string {
	$url = (string) $item->url;
	if ( str_starts_with( $url, '/' ) && ! str_starts_with( $url, '//' ) ) {
		return home_url( $url );
	}
	return $url;
}

/** Атрибуты ссылки пункта: адрес, открытие в новой вкладке, текущая страница. */
function tts_menu_attrs( WP_Post $item ): string {
	$url = tts_menu_url( $item );
	$out = 'href="' . esc_url( $url ) . '"';
	if ( '_blank' === $item->target ) {
		$out .= ' target="_blank" rel="noopener"';
	}

	// Текущая страница — только для ссылок без якоря: «О компании» ведёт
	// на блок главной, а не на саму главную.
	if ( ! str_contains( $url, '#' ) ) {
		$here = wp_parse_url( home_url( add_query_arg( array() ) ), PHP_URL_PATH );
		$page = get_queried_object();
		if ( $page instanceof WP_Post ) {
			$here = wp_parse_url( get_permalink( $page ), PHP_URL_PATH );
		} elseif ( is_front_page() ) {
			$here = wp_parse_url( home_url( '/' ), PHP_URL_PATH );
		}
		if ( untrailingslashit( (string) wp_parse_url( $url, PHP_URL_PATH ) ) === untrailingslashit( (string) $here ) ) {
			$out .= ' aria-current="page"';
		}
	}
	return $out;
}

/** Подпись под пунктом выпадающего списка. */
function tts_menu_note( WP_Post $item ): string {
	return function_exists( 'get_field' ) ? (string) get_field( 'tts_menu_note', $item->ID ) : '';
}

/** Главное меню шапки. */
function tts_header_menu(): void {
	$chevron = '<svg class="nav__chevron" viewBox="0 0 16 16" width="16" height="16" aria-hidden="true" focusable="false">'
		. '<path d="M3 6l5 5 5-5" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="square"></path></svg>';

	foreach ( tts_menu_tree( 'header' ) as $node ) {
		$item = $node['item'];

		if ( ! $node['children'] ) {
			printf(
				'<a class="nav__link" %s>%s</a>' . "\n",
				tts_menu_attrs( $item ), // phpcs:ignore WordPress.Security.EscapeOutput
				esc_html( $item->title )
			);
			continue;
		}

		// Короткий список (до трёх пунктов) — узкая панель, как «Сервис» в статике.
		$panel = 'menu-' . $item->ID;
		$class = count( $node['children'] ) <= 3 ? 'dropdown dropdown--narrow' : 'dropdown';
		?>
		<div class="nav__group">
			<button class="nav__link nav__toggle" type="button" aria-expanded="false" aria-controls="<?php echo esc_attr( $panel ); ?>">
				<?php echo esc_html( $item->title ); ?>
				<?php echo $chevron; // phpcs:ignore WordPress.Security.EscapeOutput ?>
			</button>
			<div class="<?php echo esc_attr( $class ); ?>" id="<?php echo esc_attr( $panel ); ?>" hidden>
				<?php foreach ( $node['children'] as $child ) : ?>
				<?php
				// «Весь каталог» внизу списка выделен линией — класс задаётся у пункта меню.
				$link_class = in_array( 'dropdown__link--all', (array) $child->classes, true )
					? 'dropdown__link dropdown__link--all' : 'dropdown__link';
				$note = tts_menu_note( $child );
				?>
				<a class="<?php echo esc_attr( $link_class ); ?>" <?php echo tts_menu_attrs( $child ); // phpcs:ignore WordPress.Security.EscapeOutput ?>>
					<span class="dropdown__title"><?php echo esc_html( $child->title ); ?></span>
					<?php if ( $note ) : ?>
					<span class="dropdown__note"><?php echo esc_html( $note ); ?></span>
					<?php endif; ?>
				</a>
				<?php endforeach; ?>
			</div>
		</div>
		<?php
	}
}

/** Колонка подвала: заголовок — название меню, ниже ссылки. */
function tts_footer_menu( string $location, string $extra = '' ): void {
	$title = tts_menu_title( $location );
	$tree  = tts_menu_tree( $location );
	if ( ! $tree && ! $extra ) {
		return;
	}
	?>
	<nav class="footer__col" aria-label="<?php echo esc_attr( $title ?: 'Разделы' ); ?>">
		<?php if ( $title ) : ?>
		<h3 class="footer__heading"><?php echo esc_html( $title ); ?></h3>
		<?php endif; ?>
		<?php foreach ( $tree as $node ) : ?>
		<a <?php echo tts_menu_attrs( $node['item'] ); // phpcs:ignore WordPress.Security.EscapeOutput ?>><?php echo esc_html( $node['item']->title ); ?></a>
		<?php endforeach; ?>
		<?php echo $extra; // phpcs:ignore WordPress.Security.EscapeOutput ?>
	</nav>
	<?php
}

/** Все вложенные пункты меню шапки: из них собирается список разделов на странице 404. */
function tts_menu_sections(): array {
	$out = array();
	foreach ( tts_menu_tree( 'header' ) as $node ) {
		foreach ( $node['children'] as $child ) {
			$out[] = $child;
		}
	}
	return $out;
}

/** Поле «Подпись под пунктом» у пунктов меню. */
add_action(
	'acf/include_fields',
	static function (): void {
		if ( ! function_exists( 'acf_add_local_field_group' ) ) {
			return;
		}
		acf_add_local_field_group(
			array(
				'key'      => 'group_tts_menu_item',
				'title'    => 'Пункт меню',
				'fields'   => array(
					array(
						'key'          => 'field_tts_menu_note',
						'label'        => 'Подпись под пунктом',
						'name'         => 'tts_menu_note',
						'type'         => 'text',
						'instructions' => 'Серая строка под названием в выпадающем меню шапки. Например: «SmartDryMix 5–50+ т/ч».',
					),
				),
				'location' => array(
					array(
						array(
							'param'    => 'nav_menu_item',
							'operator' => '==',
							'value'    => 'location/header',
						),
					),
				),
				'active'   => true,
			)
		);
	}
);
