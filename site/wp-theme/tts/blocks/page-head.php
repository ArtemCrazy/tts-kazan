<?php
/**
 * Блок «Шапка раздела» (п. 14 ТЗ: крошки на всех внутренних страницах,
 * один H1 на страницу).
 *
 * Разметка повторяет статическую версию (tools/pagekit.py, функция page_head):
 * крошки, надзаголовок, H1, лид, две кнопки и строка показателей.
 *
 * @var array $block Данные блока от Secure Custom Fields.
 */

defined( 'ABSPATH' ) || exit;

$kicker    = (string) get_field( 'tts_page_head_kicker' );
$title     = (string) get_field( 'tts_page_head_title' );
$lead      = (string) get_field( 'tts_page_head_lead' );
$crumb     = (string) get_field( 'tts_page_head_crumb' );
$catalog   = (bool) get_field( 'tts_page_head_under_catalog' );
$primary   = (array) get_field( 'tts_page_head_primary' );
$secondary = (array) get_field( 'tts_page_head_secondary' );
$stats     = tts_rows( get_field( 'tts_page_head_stats' ) );
$photo     = (string) get_field( 'tts_page_head_photo' );

// Подпись последней крошки: своя, иначе название страницы. Дублировать
// заголовок в двух полях редактору незачем.
$crumb = $crumb ?: (string) get_the_title();

// Фоновое фото раздела: сам файл прописан в стилях темы, разметка только
// называет раздел — как в статической версии.
$attrs = tts_block_attrs( $block, 'page-head' . ( $photo ? ' photo-bed' : '' ) );
if ( $photo ) {
	$attrs .= ' data-photo="' . esc_attr( $photo ) . '"';
}

$has_action = ! empty( $primary['title'] ) || ! empty( $secondary['title'] );
?>
<section <?php echo $attrs; // phpcs:ignore WordPress.Security.EscapeOutput ?>>
	<div class="shell">
		<nav class="crumbs" aria-label="Хлебные крошки">
			<ol class="crumbs__list">
				<li class="crumbs__item"><a class="crumbs__link" href="<?php echo esc_url( tts_url( 'home' ) ); ?>">Главная</a></li>
				<?php if ( $catalog ) : ?>
				<li class="crumbs__item"><a class="crumbs__link" href="<?php echo esc_url( tts_url( 'catalog' ) ); ?>">Каталог оборудования</a></li>
				<?php endif; ?>
				<?php if ( $crumb ) : ?>
				<li class="crumbs__item" aria-current="page"><?php echo esc_html( $crumb ); ?></li>
				<?php endif; ?>
			</ol>
		</nav>

		<?php if ( $kicker ) : ?>
		<p class="kicker"><?php echo esc_html( $kicker ); ?></p>
		<?php endif; ?>
		<?php if ( $title ) : ?>
		<h1 class="page-head__title"><?php echo esc_html( $title ); ?></h1>
		<?php endif; ?>
		<?php if ( $lead ) : ?>
		<p class="page-head__lead"><?php echo esc_html( $lead ); ?></p>
		<?php endif; ?>

		<?php if ( $has_action ) : ?>
		<div class="page-head__actions">
			<?php
			tts_button( (string) ( $primary['title'] ?? '' ), (string) ( $primary['url'] ?? '' ) );
			tts_button( (string) ( $secondary['title'] ?? '' ), (string) ( $secondary['url'] ?? '' ), 'ghost', 'lg', false );
			?>
		</div>
		<?php endif; ?>

		<?php if ( $stats ) : ?>
		<ul class="page-stats">
			<?php foreach ( $stats as $stat ) : ?>
				<?php
				$value = (string) ( $stat['tts_page_head_stat_value'] ?? '' );
				$label = (string) ( $stat['tts_page_head_stat_label'] ?? '' );
				if ( ! $value && ! $label ) {
					continue;
				}
				?>
			<li class="page-stats__cell">
				<strong class="page-stats__value"><?php echo esc_html( $value ); ?></strong>
				<span class="page-stats__label"><?php echo esc_html( $label ); ?></span>
			</li>
			<?php endforeach; ?>
		</ul>
		<?php endif; ?>
	</div>
</section>
