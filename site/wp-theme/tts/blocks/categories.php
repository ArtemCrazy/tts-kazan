<?php
/**
 * Блок «Направления каталога» (п. 6 ТЗ, блок 4).
 *
 * Разметка повторяет статическую версию: шапка секции и три карточки
 * направлений. Рендер в карточке — фон из CSS по data-cat (см. style.css,
 * .category__media[data-cat]), поэтому картинка здесь не <img>, а выбор
 * рендера из набора темы.
 *
 * @var array $block Данные блока от Secure Custom Fields.
 */

defined( 'ABSPATH' ) || exit;

$kicker = (string) get_field( 'tts_categories_kicker' );
$title  = (string) get_field( 'tts_categories_title' );
$lead   = (string) get_field( 'tts_categories_lead' );
// Пустой repeater в ACF отдаёт не массив, а false — без проверки
// в сетке появилась бы одна пустая карточка.
$cards  = tts_rows( get_field( 'tts_categories_cards' ) );
?>
<section <?php echo tts_block_attrs( $block, 'categories on-light', 'catalog' ); // phpcs:ignore WordPress.Security.EscapeOutput ?>>
	<div class="shell">
		<?php tts_section_head( $kicker, $title, $lead ); ?>

		<?php if ( $cards ) : ?>
		<div class="categories__grid">
			<?php foreach ( $cards as $i => $card ) : ?>
			<?php
			$render = (string) ( $card['tts_category_render'] ?? '' );
			$alt    = (string) ( $card['tts_category_alt'] ?? '' );
			$link   = (array) ( $card['tts_category_link'] ?? array() );
			// Номер карточки: если редактор не задал свой, считаем по порядку —
			// иначе при перестановке карточек номера разъезжаются.
			$index = (string) ( $card['tts_category_index'] ?? '' );
			$index = $index ?: sprintf( '%02d', $i + 1 );
			?>
			<article class="category">
				<div class="category__media"<?php if ( $render ) : ?> data-cat="<?php echo esc_attr( $render ); ?>" role="img" aria-label="<?php echo esc_attr( $alt ); ?>"<?php endif; ?>>
					<span class="category__index"><?php echo esc_html( $index ); ?></span>
				</div>
				<div class="category__body">
					<h3 class="category__title"><?php echo esc_html( (string) ( $card['tts_category_title'] ?? '' ) ); ?></h3>
					<?php if ( ! empty( $card['tts_category_text'] ) ) : ?>
					<p class="category__text"><?php echo esc_html( (string) $card['tts_category_text'] ); ?></p>
					<?php endif; ?>
					<?php if ( ! empty( $link['title'] ) ) : ?>
					<a class="btn btn--solid category__cta" href="<?php echo esc_url( (string) ( $link['url'] ?? '' ) ); ?>">
						<?php echo esc_html( (string) $link['title'] ); ?>
						<?php echo tts_arrow(); // phpcs:ignore WordPress.Security.EscapeOutput ?>
					</a>
					<?php endif; ?>
				</div>
			</article>
			<?php endforeach; ?>
		</div>
		<?php endif; ?>
	</div>
</section>
