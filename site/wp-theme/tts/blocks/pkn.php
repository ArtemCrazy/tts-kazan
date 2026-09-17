<?php
/**
 * Блок «Пневмокамерные насосы» (п. 6 ТЗ, блок 8).
 *
 * Разметка повторяет статическую версию: слева снимок насоса с подписью,
 * справа краткие характеристики и переход в каталог ПКН.
 *
 * @var array $block Данные блока от Secure Custom Fields.
 */

defined( 'ABSPATH' ) || exit;

$photo   = get_field( 'tts_pkn_photo' );
$caption = (string) get_field( 'tts_pkn_caption' );
$kicker  = (string) get_field( 'tts_pkn_kicker' );
$title   = (string) get_field( 'tts_pkn_title' );
$lead    = (string) get_field( 'tts_pkn_lead' );
$specs   = tts_rows( get_field( 'tts_pkn_specs' ) );
$note    = (string) get_field( 'tts_pkn_note' );
$button  = (array) get_field( 'tts_pkn_button' );
?>
<section <?php echo tts_block_attrs( $block, 'pkn on-light', 'pkn' ); // phpcs:ignore WordPress.Security.EscapeOutput ?>>
	<div class="shell pkn__grid">
		<div class="pkn__stage">
			<div class="pkn__panel">
				<?php
				// В статической версии здесь <picture> с webp: в WordPress
				// варианты размеров и форматов подставляет сама медиатека.
				tts_image( $photo, 'img/pkn-pump.jpg', 'pkn__shot' );
				?>
				<?php if ( $caption ) : ?>
				<p class="pkn__caption"><?php echo esc_html( $caption ); ?></p>
				<?php endif; ?>
			</div>
		</div>

		<div class="pkn__copy">
			<?php if ( $kicker ) : ?>
			<p class="kicker"><?php echo esc_html( $kicker ); ?></p>
			<?php endif; ?>

			<?php if ( $title ) : ?>
			<h2 class="pkn__title"><?php echo esc_html( $title ); ?></h2>
			<?php endif; ?>

			<?php if ( $lead ) : ?>
			<p class="pkn__lead"><?php echo esc_html( $lead ); ?></p>
			<?php endif; ?>

			<?php if ( $specs ) : ?>
			<ul class="specs">
				<?php foreach ( $specs as $spec ) : ?>
				<li class="specs__cell">
					<strong class="specs__value"><?php echo esc_html( (string) ( $spec['tts_pkn_spec_value'] ?? '' ) ); ?></strong>
					<span class="specs__label"><?php echo esc_html( (string) ( $spec['tts_pkn_spec_label'] ?? '' ) ); ?></span>
				</li>
				<?php endforeach; ?>
			</ul>
			<?php endif; ?>

			<?php if ( $note ) : ?>
			<p class="pkn__note"><?php echo esc_html( $note ); ?></p>
			<?php endif; ?>

			<?php tts_button( (string) ( $button['title'] ?? '' ), (string) ( $button['url'] ?? '' ) ); ?>
		</div>
	</div>
</section>
