<?php
/**
 * Блок «Две колонки: показатели и пункты».
 *
 * Разметка повторяет функцию split() из tools/pagekit.py: тёмная секция,
 * внутри .shell.split из двух колонок. Слева надзаголовок, заголовок,
 * абзац и сетка .figures; справа надзаголовок, заголовок и список .points.
 * На узких экранах колонки встают друг под другом — это задано стилями.
 *
 * Секция всегда тёмная: в статической вёрстке (site/4/catalog/*) эта секция
 * встречается только в тёмном тоне, поэтому поля выбора фона у блока нет.
 *
 * @var array $block Данные блока от Secure Custom Fields.
 */

defined( 'ABSPATH' ) || exit;

// Левая колонка: шапка и показатели.
$kicker  = (string) get_field( 'tts_split_kicker' );
$title   = (string) get_field( 'tts_split_title' );
$lead    = (string) get_field( 'tts_split_lead' );
$figures = tts_rows( get_field( 'tts_split_figures' ) );

// Правая колонка: шапка и пункты.
$points_kicker = (string) get_field( 'tts_split_points_kicker' );
$points_title  = (string) get_field( 'tts_split_points_title' );
$points        = tts_rows( get_field( 'tts_split_points' ) );
?>
<section <?php echo tts_block_attrs( $block, 'page-section page-section--dark' ); // phpcs:ignore WordPress.Security.EscapeOutput ?>>
	<div class="shell split">
		<div>
			<?php if ( $kicker ) : ?>
			<p class="kicker"><?php echo esc_html( $kicker ); ?></p>
			<?php endif; ?>
			<?php if ( $title ) : ?>
			<h2 class="split__title"><?php echo esc_html( $title ); ?></h2>
			<?php endif; ?>
			<?php if ( $lead ) : ?>
			<p class="split__text"><?php echo esc_html( $lead ); ?></p>
			<?php endif; ?>

			<?php if ( $figures ) : ?>
			<div class="figures">
				<?php foreach ( $figures as $item ) : ?>
				<div class="figures__cell">
					<?php if ( ! empty( $item['tts_split_figure_value'] ) ) : ?>
					<strong class="figures__value"><?php echo esc_html( (string) $item['tts_split_figure_value'] ); ?></strong>
					<?php endif; ?>
					<?php if ( ! empty( $item['tts_split_figure_label'] ) ) : ?>
					<span class="figures__label"><?php echo esc_html( (string) $item['tts_split_figure_label'] ); ?></span>
					<?php endif; ?>
				</div>
				<?php endforeach; ?>
			</div>
			<?php endif; ?>
		</div>
		<div>
			<?php if ( $points_kicker ) : ?>
			<p class="kicker"><?php echo esc_html( $points_kicker ); ?></p>
			<?php endif; ?>
			<?php if ( $points_title ) : ?>
			<h3 class="split__title"><?php echo esc_html( $points_title ); ?></h3>
			<?php endif; ?>

			<?php if ( $points ) : ?>
			<ul class="points">
				<?php foreach ( $points as $item ) : ?>
				<li class="point">
					<?php if ( ! empty( $item['tts_split_point_title'] ) ) : ?>
					<h4 class="point__title"><?php echo esc_html( (string) $item['tts_split_point_title'] ); ?></h4>
					<?php endif; ?>
					<?php if ( ! empty( $item['tts_split_point_text'] ) ) : ?>
					<p class="point__text"><?php echo esc_html( (string) $item['tts_split_point_text'] ); ?></p>
					<?php endif; ?>
				</li>
				<?php endforeach; ?>
			</ul>
			<?php endif; ?>
		</div>
	</div>
</section>
