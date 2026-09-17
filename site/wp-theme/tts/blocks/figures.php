<?php
/**
 * Блок «Показатели» — сетка цифр с подписями.
 *
 * Разметка повторяет функцию figures() из tools/pagekit.py: сетка .figures
 * из ячеек со значением и подписью. Ячейки идут по две в ряд, на мобильном —
 * в одну колонку (это уже задано стилями).
 *
 * @var array $block Данные блока от Secure Custom Fields.
 */

defined( 'ABSPATH' ) || exit;

$kicker = (string) get_field( 'tts_figures_kicker' );
$title  = (string) get_field( 'tts_figures_title' );
$lead   = (string) get_field( 'tts_figures_lead' );
$items  = tts_rows( get_field( 'tts_figures_items' ) );

// Классы тона секции — как в pagekit.section().
$tones = array(
	'light' => 'page-section--light on-light',
	'muted' => 'page-section--muted on-light',
	'dark'  => 'page-section--dark',
);
$tone  = (string) get_field( 'tts_figures_tone' );
$tone  = $tones[ $tone ] ?? $tones['light'];
?>
<section <?php echo tts_block_attrs( $block, 'page-section ' . $tone ); // phpcs:ignore WordPress.Security.EscapeOutput ?>>
	<div class="shell">
		<?php tts_section_head( $kicker, $title, $lead ); ?>

		<?php if ( $items ) : ?>
		<div class="figures">
			<?php foreach ( $items as $item ) : ?>
			<div class="figures__cell">
				<?php if ( ! empty( $item['tts_figures_item_value'] ) ) : ?>
				<strong class="figures__value"><?php echo esc_html( (string) $item['tts_figures_item_value'] ); ?></strong>
				<?php endif; ?>
				<?php if ( ! empty( $item['tts_figures_item_label'] ) ) : ?>
				<span class="figures__label"><?php echo esc_html( (string) $item['tts_figures_item_label'] ); ?></span>
				<?php endif; ?>
			</div>
			<?php endforeach; ?>
		</div>
		<?php endif; ?>
	</div>
</section>
