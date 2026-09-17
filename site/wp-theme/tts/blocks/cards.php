<?php
/**
 * Блок «Карточки» — набор коротких карточек в сетке.
 *
 * Разметка повторяет функцию cards() из tools/pagekit.py: шапка секции,
 * сетка .cards и карточки с меткой (номер или подпись), заголовком,
 * текстом и примечанием. Если у карточки задана ссылка, она превращается
 * в кликабельную .card--link — так же, как в link_cards() генератора.
 *
 * @var array $block Данные блока от Secure Custom Fields.
 */

defined( 'ABSPATH' ) || exit;

$kicker = (string) get_field( 'tts_cards_kicker' );
$title  = (string) get_field( 'tts_cards_title' );
$lead   = (string) get_field( 'tts_cards_lead' );
$items  = tts_rows( get_field( 'tts_cards_items' ) );

// Классы тона секции — как в pagekit.section(): светлый и приглушённый
// идут вместе с on-light, тёмный без него.
$tones = array(
	'light' => 'page-section--light on-light',
	'muted' => 'page-section--muted on-light',
	'dark'  => 'page-section--dark',
);
$tone  = (string) get_field( 'tts_cards_tone' );
$tone  = $tones[ $tone ] ?? $tones['light'];

// Сетка: базовая .cards — три в ряд, модификаторы меняют число колонок.
$grids = array(
	'three' => '',
	'two'   => 'cards--2',
	'four'  => 'cards--4',
);
$grid  = (string) get_field( 'tts_cards_grid' );
$grid  = $grids[ $grid ] ?? '';
?>
<section <?php echo tts_block_attrs( $block, 'page-section ' . $tone ); // phpcs:ignore WordPress.Security.EscapeOutput ?>>
	<div class="shell">
		<?php tts_section_head( $kicker, $title, $lead ); ?>

		<?php if ( $items ) : ?>
		<div class="<?php echo esc_attr( trim( 'cards ' . $grid ) ); ?>">
			<?php
			foreach ( $items as $item ) :
				$card_index = (string) ( $item['tts_cards_item_index'] ?? '' );
				$card_tag   = (string) ( $item['tts_cards_item_tag'] ?? '' );
				$card_title = (string) ( $item['tts_cards_item_title'] ?? '' );
				$card_text  = (string) ( $item['tts_cards_item_text'] ?? '' );
				$card_note  = (string) ( $item['tts_cards_item_note'] ?? '' );
				$card_link  = (array) ( $item['tts_cards_item_link'] ?? array() );
				$card_url   = (string) ( $card_link['url'] ?? '' );

				// Со ссылкой карточка становится ссылкой целиком, без неё — обычный article.
				if ( $card_url ) {
					$open = sprintf(
						'<a class="card card--link" href="%s"%s>',
						esc_url( $card_url ),
						'_blank' === (string) ( $card_link['target'] ?? '' ) ? ' target="_blank" rel="noopener"' : ''
					);
					$close = '</a>';
				} else {
					$open  = '<article class="card">';
					$close = '</article>';
				}
				?>
			<?php echo $open; // phpcs:ignore WordPress.Security.EscapeOutput ?>
				<?php if ( $card_index ) : ?>
				<span class="card__index"><?php echo esc_html( $card_index ); ?></span>
				<?php elseif ( $card_tag ) : ?>
				<span class="card__tag"><?php echo esc_html( $card_tag ); ?></span>
				<?php endif; ?>
				<?php if ( $card_title ) : ?>
				<h3 class="card__title"><?php echo esc_html( $card_title ); ?></h3>
				<?php endif; ?>
				<?php if ( $card_text ) : ?>
				<p class="card__text"><?php echo esc_html( $card_text ); ?></p>
				<?php endif; ?>
				<?php if ( $card_note ) : ?>
				<p class="card__note"><?php echo esc_html( $card_note ); ?></p>
				<?php endif; ?>
			<?php echo $close; // phpcs:ignore WordPress.Security.EscapeOutput ?>
			<?php endforeach; ?>
		</div>
		<?php endif; ?>
	</div>
</section>
