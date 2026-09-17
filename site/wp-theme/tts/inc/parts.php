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

/** Краткое описание страницы для лида: берём из поля SEO или из выдержки. */
function tts_page_lead( ?WP_Post $post = null ): string {
	$post = $post ?: get_post();
	if ( ! $post ) {
		return '';
	}
	$lead = get_post_meta( $post->ID, 'tts_lead', true );
	return $lead ?: get_the_excerpt( $post );
}
