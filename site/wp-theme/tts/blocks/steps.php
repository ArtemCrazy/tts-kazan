<?php
/**
 * Блок «Этапы работы» — нумерованная цепочка шагов.
 *
 * Разметка повторяет функцию steps() из tools/pagekit.py: список .steps,
 * модификатор колонок подбирается по числу этапов (три или четыре),
 * номер шага считается автоматически — отдельного поля под него нет.
 *
 * @var array $block Данные блока от Secure Custom Fields.
 */

defined( 'ABSPATH' ) || exit;

$kicker = (string) get_field( 'tts_steps_kicker' );
$title  = (string) get_field( 'tts_steps_title' );
$lead   = (string) get_field( 'tts_steps_lead' );
$items  = tts_rows( get_field( 'tts_steps_items' ) );

// Классы тона секции — как в pagekit.section().
$tones = array(
	'light' => 'page-section--light on-light',
	'muted' => 'page-section--muted on-light',
	'dark'  => 'page-section--dark',
);
$tone  = (string) get_field( 'tts_steps_tone' );
$tone  = $tones[ $tone ] ?? $tones['light'];

// Три и четыре этапа раскладываются в один ряд, остальные количества — по сетке по умолчанию.
$grids = array(
	3 => ' steps--3',
	4 => ' steps--4',
);
$grid  = $grids[ count( $items ) ] ?? '';
?>
<section <?php echo tts_block_attrs( $block, 'page-section ' . $tone ); // phpcs:ignore WordPress.Security.EscapeOutput ?>>
	<div class="shell">
		<?php tts_section_head( $kicker, $title, $lead ); ?>

		<?php if ( $items ) : ?>
		<ol class="<?php echo esc_attr( 'steps' . $grid ); ?>">
			<?php foreach ( $items as $index => $item ) : ?>
			<li class="step">
				<span class="step__index"><?php echo esc_html( sprintf( '%02d', $index + 1 ) ); ?></span>
				<?php if ( ! empty( $item['tts_steps_item_title'] ) ) : ?>
				<h3 class="step__title"><?php echo esc_html( (string) $item['tts_steps_item_title'] ); ?></h3>
				<?php endif; ?>
				<?php if ( ! empty( $item['tts_steps_item_text'] ) ) : ?>
				<p class="step__text"><?php echo esc_html( (string) $item['tts_steps_item_text'] ); ?></p>
				<?php endif; ?>
			</li>
			<?php endforeach; ?>
		</ol>
		<?php endif; ?>
	</div>
</section>
