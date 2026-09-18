<?php
/**
 * Блоки Gutenberg для секций страниц (п. 12.1 ТЗ: структурированные блоки
 * и поля, а не один кусок HTML).
 *
 * Каждый блок — это секция страницы: редактор добавляет её, заполняет поля
 * и переставляет блоки местами. Разметка блока лежит в blocks/<имя>.php,
 * поля — в inc/fields-blocks.php.
 */

defined( 'ABSPATH' ) || exit;

/**
 * Состав блоков читаем с диска: рядом с каждым blocks/<имя>.php лежит
 * blocks/<имя>.json с подписью, описанием и значком. Так новый блок
 * добавляется двумя файлами и общий список править не нужно.
 */
function tts_block_list(): array {
	$blocks = array();
	foreach ( (array) glob( get_theme_file_path( 'blocks/*.json' ) ) as $meta ) {
		$name = basename( $meta, '.json' );
		if ( ! file_exists( get_theme_file_path( 'blocks/' . $name . '.php' ) ) ) {
			continue; // описание без разметки — блока нет
		}
		$data = json_decode( (string) file_get_contents( $meta ), true );
		if ( ! is_array( $data ) || empty( $data['title'] ) ) {
			continue;
		}
		$blocks[ $name ] = array(
			$data['title'],
			$data['description'] ?? '',
			$data['icon'] ?? 'block-default',
		);
	}
	ksort( $blocks );
	return $blocks;
}

/** Своя категория блоков, чтобы редактор не искал наши секции среди чужих. */
function tts_block_category( array $categories ): array {
	return array_merge(
		array(
			array(
				'slug'  => 'tts',
				'title' => 'Секции сайта ТТС',
				'icon'  => null,
			),
		),
		$categories
	);
}
add_filter( 'block_categories_all', 'tts_block_category' );

/** Регистрация блоков. */
function tts_register_blocks(): void {
	if ( ! function_exists( 'acf_register_block_type' ) ) {
		return;
	}

	foreach ( tts_block_list() as $name => list( $title, $description, $icon ) ) {
		acf_register_block_type(
			array(
				'name'            => 'tts-' . $name,
				'title'           => $title,
				'description'     => $description,
				'category'        => 'tts',
				'icon'            => $icon,
				'keywords'        => array( 'ТТС', $title ),
				'render_template' => 'blocks/' . $name . '.php',
				// В редакторе блок сразу показывается так, как выглядит на сайте.
				'mode'            => 'preview',
				'supports'        => array(
					'align'  => false,
					'anchor' => true, // нужны якоря вида #catalog и #contact
					'jsx'    => false,
				),
			)
		);
	}
}
add_action( 'acf/init', 'tts_register_blocks' );

/**
 * Якорь секции: редактор может задать свой, иначе берём тот, что нужен
 * ссылкам в шапке и подвале.
 */
function tts_block_anchor( array $block, string $fallback = '' ): string {
	$anchor = $block['anchor'] ?? '';
	return $anchor ?: $fallback;
}

/** Атрибуты секции блока: якорь и классы редактора. */
function tts_block_attrs( array $block, string $classes, string $fallback = '' ): string {
	$anchor = tts_block_anchor( $block, $fallback );
	$out    = 'class="' . esc_attr( trim( $classes . ' ' . ( $block['className'] ?? '' ) ) ) . '"';
	if ( $anchor ) {
		$out .= ' id="' . esc_attr( $anchor ) . '"';
	}
	return $out;
}

/**
 * Шапка секции: надзаголовок, заголовок и лид.
 * Классы совпадают со статической вёрсткой, чтобы стили подходили без правок.
 */
function tts_section_head( string $kicker, string $title, string $lead = '', string $prefix = 'section-head' ): void {
	if ( ! $kicker && ! $title && ! $lead ) {
		return;
	}
	?>
	<div class="<?php echo esc_attr( $prefix ); ?>">
		<?php if ( $kicker ) : ?>
		<p class="kicker <?php echo esc_attr( $prefix ); ?>__kicker"><?php echo esc_html( $kicker ); ?></p>
		<?php endif; ?>
		<?php if ( $title ) : ?>
		<h2 class="<?php echo esc_attr( $prefix ); ?>__title"><?php echo esc_html( $title ); ?></h2>
		<?php endif; ?>
		<?php if ( $lead ) : ?>
		<p class="<?php echo esc_attr( $prefix ); ?>__lead"><?php echo esc_html( $lead ); ?></p>
		<?php endif; ?>
	</div>
	<?php
}

/**
 * Строки повторяющегося поля.
 *
 * Пустой repeater отдаёт false, а приведение false к массиву даёт массив
 * из одного пустого элемента — в вёрстке это лишний пункт списка.
 */
function tts_rows( $value ): array {
	return array_values( array_filter( (array) $value, 'is_array' ) );
}

/** Кнопка со стрелкой — та же разметка, что в статической версии. */
function tts_button( string $label, string $url, string $style = 'solid', string $size = 'lg', bool $arrow = true ): void {
	if ( ! $label ) {
		return;
	}
	printf(
		'<a class="btn btn--%s btn--%s" href="%s">%s%s</a>',
		esc_attr( $style ),
		esc_attr( $size ),
		esc_url( $url ?: '#contact' ),
		esc_html( $label ),
		$arrow ? tts_arrow() : '' // phpcs:ignore WordPress.Security.EscapeOutput
	);
}

/**
 * Картинка из поля с запасным вариантом из assets темы.
 * Размеры выводим всегда: без них страница «прыгает» при загрузке (CLS).
 */
function tts_image( $attachment, string $fallback = '', string $class = '', string $alt = '', bool $eager = false, bool $picture = false, bool $lazy = true ): void {
	// В статике крупные снимки обёрнуты в <picture>, и в сетке панели обёртка
	// даёт другой размер, чем голая картинка. Где так было — повторяем.
	if ( $picture ) {
		// Картинки в <picture> в статике грузятся сразу, без loading="lazy":
		// ленивая картинка в обёртке нулевой ширины может не загрузиться вовсе.
		echo '<picture>';
		tts_image( $attachment, $fallback, $class, $alt, $eager, false, false );
		echo '</picture>';
		return;
	}
	if ( $attachment ) {
		echo wp_get_attachment_image( // phpcs:ignore WordPress.Security.EscapeOutput
			(int) $attachment,
			'full',
			false,
			array(
				'class'         => $class,
				// Картинку первого экрана грузим сразу: это она определяет
				// скорость показа страницы (LCP в п. 15.1 ТЗ).
				'loading'       => ( $eager || ! $lazy ) ? 'eager' : 'lazy',
				// Высокий приоритет — только у картинки первого экрана
				'fetchpriority' => $eager ? 'high' : 'auto',
				'alt'     => $alt ?: trim( (string) get_post_meta( (int) $attachment, '_wp_attachment_image_alt', true ) ),
			)
		);
		return;
	}
	if ( ! $fallback ) {
		return;
	}
	printf(
		'<img class="%s" src="%s" alt="%s" loading="%s">',
		esc_attr( $class ),
		esc_url( tts_asset( $fallback )[0] ),
		esc_attr( $alt ),
		( $eager || ! $lazy ) ? 'eager' : 'lazy'
	);
}
