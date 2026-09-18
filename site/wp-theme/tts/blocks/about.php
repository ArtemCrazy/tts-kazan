<?php
/**
 * Блок «О компании» (п. 6 ТЗ, блок 6).
 *
 * Разметка повторяет статическую версию: слева надзаголовок, заголовок,
 * абзацы о компании и кнопка, справа — таблица показателей.
 *
 * @var array $block Данные блока от Secure Custom Fields.
 */

defined( 'ABSPATH' ) || exit;

$kicker = (string) get_field( 'tts_about_kicker' );
$title  = (string) get_field( 'tts_about_title' );
$text   = (string) get_field( 'tts_about_text' );
$button = (array) get_field( 'tts_about_button' );
$stats  = tts_rows( get_field( 'tts_about_stats' ) );

// Абзацы редактор разделяет переводом строки: каждому нужен свой класс,
// поэтому текст разбираем сами, а не отдаём одним куском.
$paragraphs = array_filter( array_map( 'trim', (array) preg_split( '/\R+/u', $text ) ) );
?>
<section <?php echo tts_block_attrs( $block, 'about on-light', 'company' ); // phpcs:ignore WordPress.Security.EscapeOutput ?>>
	<div class="shell about__grid">
		<div class="about__copy">
			<?php if ( $kicker ) : ?>
			<p class="kicker"><?php echo esc_html( $kicker ); ?></p>
			<?php endif; ?>

			<?php if ( $title ) : ?>
			<h2 class="about__title"><?php echo esc_html( $title ); ?></h2>
			<?php endif; ?>

			<?php foreach ( $paragraphs as $paragraph ) : ?>
			<p class="about__text"><?php echo esc_html( $paragraph ); ?></p>
			<?php endforeach; ?>

			<?php if ( ! empty( $button['title'] ) ) : ?>
			<a class="btn btn--solid btn--lg about__link" href="<?php echo esc_url( (string) ( $button['url'] ?? '' ) ?: '#projects' ); ?>">
				<?php echo esc_html( (string) $button['title'] ); ?>
				<?php echo tts_arrow(); // phpcs:ignore WordPress.Security.EscapeOutput ?>
			</a>
			<?php endif; ?>
		</div>

		<?php if ( $stats ) : ?>
		<ul class="stats">
			<?php foreach ( $stats as $stat ) : ?>
			<li class="stats__cell">
				<strong class="stats__value<?php echo empty( $stat['tts_about_stat_words'] ) ? '' : ' stats__value--text'; ?>"><?php echo esc_html( (string) ( $stat['tts_about_stat_value'] ?? '' ) ); ?></strong>
				<span class="stats__label"><?php echo esc_html( (string) ( $stat['tts_about_stat_label'] ?? '' ) ); ?></span>
			</li>
			<?php endforeach; ?>
		</ul>
		<?php endif; ?>
	</div>
</section>
