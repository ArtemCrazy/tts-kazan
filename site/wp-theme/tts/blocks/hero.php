<?php
/**
 * Блок «Первый экран» (п. 6 ТЗ, блок 1).
 *
 * Разметка повторяет статическую версию: водяной знак, фото площадки,
 * заголовок с выделенной частью, две кнопки, снимок оборудования и три
 * преимущества под ним.
 *
 * @var array $block Данные блока от Secure Custom Fields.
 */

defined( 'ABSPATH' ) || exit;

$title     = (string) get_field( 'tts_hero_title' );
$accent    = (string) get_field( 'tts_hero_accent' );
$lead      = (string) get_field( 'tts_hero_lead' );
$primary   = (array) get_field( 'tts_hero_primary' );
$secondary = (array) get_field( 'tts_hero_secondary' );
$photo     = get_field( 'tts_hero_photo' );
$tag       = (string) get_field( 'tts_hero_tag' );
$proofs    = tts_rows( get_field( 'tts_hero_proofs' ) );
?>
<section <?php echo tts_block_attrs( $block, 'hero', 'hero' ); // phpcs:ignore WordPress.Security.EscapeOutput ?>>
	<div class="hero__wash" aria-hidden="true"></div>
	<img class="hero__watermark hero__watermark--knockout" src="<?php echo esc_url( tts_asset( 'img/tts-mark-light.png' )[0] ); ?>" alt="" aria-hidden="true" width="1744" height="2004">
	<img class="hero__watermark hero__watermark--ink" src="<?php echo esc_url( tts_asset( 'img/tts-mark.png' )[0] ); ?>" alt="" aria-hidden="true" width="512" height="588">
	<div class="hero__photo" aria-hidden="true"></div>
	<div class="hero__scrim" aria-hidden="true"></div>

	<div class="shell hero__grid">
		<div class="hero__copy">
			<h1 class="hero__title reveal" style="--reveal-delay:.05s">
				<?php echo esc_html( $title ); ?>
				<?php if ( $accent ) : ?>
				<span class="marker"><?php echo esc_html( $accent ); ?></span>
				<?php endif; ?>
			</h1>

			<?php if ( $lead ) : ?>
			<p class="hero__lead reveal" style="--reveal-delay:.16s"><?php echo esc_html( $lead ); ?></p>
			<?php endif; ?>

			<div class="hero__actions reveal" style="--reveal-delay:.24s">
				<?php
				tts_button( (string) ( $primary['title'] ?? '' ), (string) ( $primary['url'] ?? '' ) );
				tts_button( (string) ( $secondary['title'] ?? '' ), (string) ( $secondary['url'] ?? '' ), 'ghost', 'lg', false );
				?>
			</div>
		</div>

		<div class="hero__stage reveal" style="--reveal-delay:.12s">
			<?php
			tts_image(
				$photo,
				'img/hero-plant-tts.png',
				'hero__plant',
				'Бетонный завод ТТС: силосы цемента, смесительный узел, конвейер и склад заполнителей',
				true,
				true
			);
			?>
			<?php if ( $tag ) : ?>
			<p class="tag">
				<svg class="tag__mark" viewBox="0 0 12 14" width="12" height="14" aria-hidden="true" focusable="false">
					<path d="M6 0l6 3.5v7L6 14 0 10.5v-7z" fill="currentColor"></path>
				</svg>
				<?php echo esc_html( $tag ); ?>
			</p>
			<?php endif; ?>
		</div>
	</div>

	<?php if ( $proofs ) : ?>
	<div class="shell">
		<ul class="proofs reveal" style="--reveal-delay:.34s">
			<?php foreach ( $proofs as $proof ) : ?>
			<li class="proofs__item">
				<svg class="proofs__mark" viewBox="0 0 12 14" width="12" height="14" aria-hidden="true" focusable="false">
					<path d="M6 0l6 3.5v7L6 14 0 10.5v-7z" fill="currentColor"></path>
				</svg>
				<?php if ( ! empty( $proof['tts_hero_proof_icon'] ) ) : ?>
				<img class="proofs__icon" src="<?php echo esc_url( tts_asset( 'icons/' . $proof['tts_hero_proof_icon'] . '.svg' )[0] ); ?>" width="64" height="64" alt="">
				<?php endif; ?>
				<?php echo esc_html( (string) ( $proof['tts_hero_proof_text'] ?? '' ) ); ?>
			</li>
			<?php endforeach; ?>
		</ul>
	</div>
	<?php endif; ?>
</section>
