<?php
/**
 * Блок «Кадр и текст» (п. 6 ТЗ, блоки 5 и 6: линии ВПИ и бетонные заводы).
 *
 * Один блок на обе секции статической версии: у зеркального варианта
 * колонки в разметке идут в обратном порядке (стили опираются на этот
 * порядок — .feature--mirror .feature__stage { order: -1 } на узких экранах),
 * поэтому части собраны замыканиями и выводятся в нужной последовательности.
 *
 * @var array $block Данные блока от Secure Custom Fields.
 */

defined( 'ABSPATH' ) || exit;

$kicker  = (string) get_field( 'tts_feature_kicker' );
$title   = (string) get_field( 'tts_feature_title' );
$lead    = (string) get_field( 'tts_feature_lead' );
// Пустой repeater в ACF отдаёт не массив, а false — без проверки
// в списке появился бы один пустой пункт.
$points  = tts_rows( get_field( 'tts_feature_points' ) );
$cta     = (array) get_field( 'tts_feature_cta' );
$shot    = get_field( 'tts_feature_shot' );
$caption = (string) get_field( 'tts_feature_caption' );
$partner = get_field( 'tts_feature_partner' );
$mirror  = (bool) get_field( 'tts_feature_mirror' );

$stage = static function () use ( $shot, $caption, $partner ) {
	if ( ! $shot && ! $caption && ! $partner ) {
		return;
	}
	?>
	<div class="feature__stage">
		<?php if ( $shot ) : ?>
		<div class="feature__panel">
			<?php tts_image( $shot, '', 'feature__shot' ); ?>
		</div>
		<?php endif; ?>

		<?php if ( $caption || $partner ) : ?>
		<p class="feature__caption">
			<?php if ( $partner ) : ?>
			<img class="feature__caption-mark" src="<?php echo esc_url( tts_asset( 'img/tts-mark-light.png' )[0] ); ?>" width="1744" height="2004" alt="ТТС">
			<span class="feature__caption-x" aria-hidden="true">×</span>
			<?php tts_image( $partner, '', 'feature__caption-logo' ); ?>
			<?php endif; ?>
			<?php if ( $caption ) : ?>
			<span class="feature__caption-text"><?php echo esc_html( $caption ); ?></span>
			<?php endif; ?>
		</p>
		<?php endif; ?>
	</div>
	<?php
};

$copy = static function () use ( $kicker, $title, $lead, $points, $cta ) {
	?>
	<div class="feature__copy">
		<?php if ( $kicker ) : ?>
		<p class="kicker"><?php echo esc_html( $kicker ); ?></p>
		<?php endif; ?>
		<?php if ( $title ) : ?>
		<h2 class="feature__title"><?php echo esc_html( $title ); ?></h2>
		<?php endif; ?>
		<?php if ( $lead ) : ?>
		<p class="feature__lead"><?php echo esc_html( $lead ); ?></p>
		<?php endif; ?>

		<?php if ( $points ) : ?>
		<ul class="feature__list">
			<?php foreach ( $points as $point ) : ?>
			<li>
				<svg class="feature__mark" viewBox="0 0 12 14" width="12" height="14" aria-hidden="true" focusable="false">
					<path d="M6 0l6 3.5v7L6 14 0 10.5v-7z" fill="currentColor"></path>
				</svg>
				<?php echo esc_html( (string) ( $point['tts_feature_point'] ?? '' ) ); ?>
			</li>
			<?php endforeach; ?>
		</ul>
		<?php endif; ?>

		<?php if ( ! empty( $cta['title'] ) ) : ?>
		<a class="btn btn--solid btn--lg feature__cta" href="<?php echo esc_url( (string) ( $cta['url'] ?? '' ) ); ?>">
			<?php echo esc_html( (string) $cta['title'] ); ?>
			<?php echo tts_arrow(); // phpcs:ignore WordPress.Security.EscapeOutput ?>
		</a>
		<?php endif; ?>
	</div>
	<?php
};
?>
<section <?php echo tts_block_attrs( $block, 'feature' . ( $mirror ? ' feature--mirror' : '' ), $mirror ? 'lines' : 'vpi' ); // phpcs:ignore WordPress.Security.EscapeOutput ?>>
	<div class="shell feature__grid">
		<?php
		if ( $mirror ) {
			$copy();
			$stage();
		} else {
			$stage();
			$copy();
		}
		?>
	</div>
</section>
