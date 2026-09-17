<?php
/**
 * Блок «Пункты с маркерами» — список возможностей.
 *
 * Разметка повторяет функцию points() из tools/pagekit.py: список .points,
 * у каждого пункта заголовок и текст. Маркер рисуется стилями (.point__title),
 * своей разметки под него не нужно.
 *
 * @var array $block Данные блока от Secure Custom Fields.
 */

defined( 'ABSPATH' ) || exit;

$kicker = (string) get_field( 'tts_points_kicker' );
$title  = (string) get_field( 'tts_points_title' );
$lead   = (string) get_field( 'tts_points_lead' );
$items  = tts_rows( get_field( 'tts_points_items' ) );

// Классы тона секции — как в pagekit.section().
$tones = array(
	'light' => 'page-section--light on-light',
	'muted' => 'page-section--muted on-light',
	'dark'  => 'page-section--dark',
);
$tone  = (string) get_field( 'tts_points_tone' );
$tone  = $tones[ $tone ] ?? $tones['light'];
?>
<section <?php echo tts_block_attrs( $block, 'page-section ' . $tone ); // phpcs:ignore WordPress.Security.EscapeOutput ?>>
	<div class="shell">
		<?php tts_section_head( $kicker, $title, $lead ); ?>

		<?php if ( $items ) : ?>
		<ul class="points">
			<?php foreach ( $items as $item ) : ?>
			<li class="point">
				<?php if ( ! empty( $item['tts_points_item_title'] ) ) : ?>
				<h3 class="point__title"><?php echo esc_html( (string) $item['tts_points_item_title'] ); ?></h3>
				<?php endif; ?>
				<?php if ( ! empty( $item['tts_points_item_text'] ) ) : ?>
				<p class="point__text"><?php echo esc_html( (string) $item['tts_points_item_text'] ); ?></p>
				<?php endif; ?>
			</li>
			<?php endforeach; ?>
		</ul>
		<?php endif; ?>
	</div>
</section>
