<?php
/**
 * Блок «Вопросы и ответы» (п. 6 ТЗ, блок 10).
 *
 * Сами вопросы редактор ведёт в разделе «Вопросы и ответы», а блок только
 * выбирает, для какой страницы их показать. Так один и тот же вопрос
 * не приходится дублировать на разных страницах.
 *
 * @var array $block
 */

defined( 'ABSPATH' ) || exit;

$kicker = (string) get_field( 'tts_faq_block_kicker' );
$title  = (string) get_field( 'tts_faq_block_title' );
$place  = (string) get_field( 'tts_faq_block_place' ) ?: 'home';

$items = tts_items(
	'faq',
	array(
		'meta_query' => array(
			'relation' => 'AND',
			array(
				'key'     => 'tts_faq_places',
				'value'   => '"' . $place . '"',
				'compare' => 'LIKE',
			),
			array(
				'relation' => 'OR',
				array(
					'key'     => 'tts_faq_visible',
					'value'   => '1',
					'compare' => '=',
				),
				array(
					'key'     => 'tts_faq_visible',
					'compare' => 'NOT EXISTS',
				),
			),
		),
	)
);
?>
<section <?php echo tts_block_attrs( $block, 'faq on-light', 'faq' ); // phpcs:ignore WordPress.Security.EscapeOutput ?>>
	<span class="faq__watermark" aria-hidden="true"></span>
	<div class="shell faq__grid">
		<div class="faq__intro">
			<?php if ( $kicker ) : ?>
			<p class="kicker"><?php echo esc_html( $kicker ); ?></p>
			<?php endif; ?>
			<?php if ( $title ) : ?>
			<h2 class="faq__title"><?php echo esc_html( $title ); ?></h2>
			<?php endif; ?>
		</div>

		<?php if ( $items ) : ?>
		<div class="faq__list">
			<?php foreach ( $items as $index => $item ) : ?>
			<details class="faq__item"<?php echo 0 === $index ? ' open' : ''; ?>>
				<summary class="faq__question"><?php echo esc_html( get_the_title( $item ) ); ?></summary>
				<div class="faq__panel">
					<?php
					$answer = (string) get_field( 'tts_faq_answer', $item->ID );
					// В поле лежит редактор: абзацы уже обёрнуты, добавляем только класс.
					echo wp_kses_post( str_replace( '<p>', '<p class="faq__answer">', $answer ) );
					?>
				</div>
			</details>
			<?php endforeach; ?>
		</div>
		<?php else : ?>
		<div class="faq__list">
			<p class="doc__text">Для этой страницы вопросов пока нет. Добавьте их в разделе «Вопросы и ответы».</p>
		</div>
		<?php endif; ?>
	</div>
</section>
