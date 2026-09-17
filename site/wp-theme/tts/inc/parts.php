<?php
/**
 * Повторяющиеся куски разметки страницы: шапка раздела с хлебными крошками
 * и обёртка секции. Классы те же, что в статической вёрстке.
 */

defined( 'ABSPATH' ) || exit;

/**
 * Шапка раздела: крошки, надзаголовок, H1 и лид (п. 14 ТЗ — крошки на всех
 * внутренних страницах, один H1 на страницу).
 *
 * @param array $spec kicker, title, lead, crumb, under_catalog.
 */
function tts_page_head( array $spec ): void {
	$crumbs = array(
		'<a class="crumbs__link" href="' . esc_url( tts_url( 'home' ) ) . '">Главная</a>',
	);
	if ( ! empty( $spec['under_catalog'] ) ) {
		$crumbs[] = '<a class="crumbs__link" href="' . esc_url( tts_url( 'catalog' ) )
			. '">Каталог оборудования</a>';
	}
	?>
	<section class="page-head">
		<div class="shell">
			<nav class="crumbs" aria-label="Хлебные крошки">
				<ol class="crumbs__list">
					<?php foreach ( $crumbs as $crumb ) : ?>
					<li class="crumbs__item"><?php echo $crumb; // phpcs:ignore WordPress.Security.EscapeOutput ?></li>
					<?php endforeach; ?>
					<li class="crumbs__item" aria-current="page"><?php echo esc_html( $spec['crumb'] ); ?></li>
				</ol>
			</nav>

			<?php if ( ! empty( $spec['kicker'] ) ) : ?>
			<p class="kicker"><?php echo esc_html( $spec['kicker'] ); ?></p>
			<?php endif; ?>
			<h1 class="page-head__title"><?php echo esc_html( $spec['title'] ); ?></h1>
			<?php if ( ! empty( $spec['lead'] ) ) : ?>
			<p class="page-head__lead"><?php echo esc_html( $spec['lead'] ); ?></p>
			<?php endif; ?>
		</div>
	</section>
	<?php
}

/**
 * Подписи ячеек в таблицах редактора.
 *
 * На телефоне таблица складывается в карточки и шапка скрывается, поэтому
 * каждой ячейке нужна своя подпись. Редактор про это знать не должен —
 * подставляем подписи сами, из шапки таблицы.
 */
function tts_table_labels( string $content, array $block ): string {
	if ( 'core/table' !== ( $block['blockName'] ?? '' ) || ! str_contains( $content, '<thead' ) ) {
		return $content;
	}

	preg_match( '~<thead>.*?</thead>~s', $content, $head );
	if ( ! $head ) {
		return $content;
	}
	preg_match_all( '~<th[^>]*>(.*?)</th>~s', $head[0], $cells );
	$labels = array_map(
		static fn( $cell ) => trim( wp_strip_all_tags( $cell ) ),
		$cells[1] ?? array()
	);
	if ( ! $labels ) {
		return $content;
	}

	return preg_replace_callback(
		'~<tbody>.*?</tbody>~s',
		static function ( $body ) use ( $labels ) {
			$index = 0;
			return preg_replace_callback(
				'~<td(\s[^>]*)?>~',
				static function ( $cell ) use ( $labels, &$index ) {
					$label = $labels[ $index % count( $labels ) ] ?? '';
					++$index;
					$attrs = $cell[1] ?? '';
					return '<td' . $attrs . ' data-label="' . esc_attr( $label ) . '">';
				},
				$body[0]
			);
		},
		$content
	);
}
add_filter( 'render_block', 'tts_table_labels', 10, 2 );

/** Краткое описание страницы для лида: берём из поля SEO или из выдержки. */
function tts_page_lead( ?WP_Post $post = null ): string {
	$post = $post ?: get_post();
	if ( ! $post ) {
		return '';
	}
	$lead = get_post_meta( $post->ID, 'tts_lead', true );
	return $lead ?: get_the_excerpt( $post );
}
