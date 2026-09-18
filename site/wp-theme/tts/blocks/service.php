<?php
/**
 * Блок «Сервис и комплектующие» (п. 6 ТЗ, блок 7).
 *
 * Разметка повторяет статическую версию: шапка секции, строка показателей
 * и две большие карточки — инженерный сервис и запасные части.
 *
 * @var array $block Данные блока от Secure Custom Fields.
 */

defined( 'ABSPATH' ) || exit;

$kicker = (string) get_field( 'tts_service_block_kicker' );
$title  = (string) get_field( 'tts_service_block_title' );
$lead   = (string) get_field( 'tts_service_block_lead' );
$trust  = tts_rows( get_field( 'tts_service_block_trust' ) );
$cards  = tts_rows( get_field( 'tts_service_block_cards' ) );
?>
<section <?php echo tts_block_attrs( $block, 'service on-light', 'service' ); // phpcs:ignore WordPress.Security.EscapeOutput ?>>
	<div class="shell">
		<?php tts_section_head( $kicker, $title, $lead ); ?>

		<?php if ( $trust ) : ?>
		<ul class="trust">
			<?php foreach ( $trust as $cell ) : ?>
			<li class="trust__cell">
				<svg class="trust__mark" viewBox="0 0 12 14" width="12" height="14" aria-hidden="true" focusable="false">
					<path d="M6 0l6 3.5v7L6 14 0 10.5v-7z" fill="currentColor"></path>
				</svg>
				<strong class="trust__value"><?php echo esc_html( (string) ( $cell['tts_service_block_trust_value'] ?? '' ) ); ?></strong>
				<span class="trust__label"><?php echo esc_html( (string) ( $cell['tts_service_block_trust_label'] ?? '' ) ); ?></span>
			</li>
			<?php endforeach; ?>
		</ul>
		<?php endif; ?>

		<?php if ( $cards ) : ?>
		<div class="service__grid">
			<?php
			foreach ( $cards as $index => $card ) :
				$link    = (array) ( $card['tts_service_block_card_link'] ?? array() );
				$request = (array) ( $card['tts_service_block_card_request'] ?? array() );
				// Пункты редактор пишет по одному в строке.
				$points  = array_filter( array_map( 'trim', (array) preg_split( '/\R+/u', (string) ( $card['tts_service_block_card_points'] ?? '' ) ) ) );
				// Кнопка ведёт на страницу раздела, поэтому без адреса её не показываем.
				$has_link = ! empty( $link['title'] ) && ! empty( $link['url'] );
				?>
			<article class="service-card">
				<div class="service-card__top">
					<?php // Номер карточки считаем по порядку, чтобы он не разъезжался при перестановке. ?>
					<span class="service-card__index"><?php echo esc_html( sprintf( '%02d', $index + 1 ) ); ?></span>
					<?php if ( ! empty( $card['tts_service_block_card_tag'] ) ) : ?>
					<span class="service-card__tag"><?php echo esc_html( (string) $card['tts_service_block_card_tag'] ); ?></span>
					<?php endif; ?>
				</div>

				<?php if ( ! empty( $card['tts_service_block_card_title'] ) ) : ?>
				<h3 class="service-card__title"><?php echo esc_html( (string) $card['tts_service_block_card_title'] ); ?></h3>
				<?php endif; ?>

				<?php if ( ! empty( $card['tts_service_block_card_text'] ) ) : ?>
				<p class="service-card__text"><?php echo esc_html( (string) $card['tts_service_block_card_text'] ); ?></p>
				<?php endif; ?>

				<?php if ( $points ) : ?>
				<ul class="service-card__points">
					<?php foreach ( $points as $point ) : ?>
					<li><?php echo esc_html( $point ); ?></li>
					<?php endforeach; ?>
				</ul>
				<?php endif; ?>

				<?php if ( $has_link || ! empty( $request['title'] ) ) : ?>
				<div class="service-card__actions">
					<?php if ( $has_link ) : ?>
					<a class="btn btn--<?php echo esc_attr( (string) ( $card['tts_service_block_card_style'] ?? 'solid' ) ); ?>" href="<?php echo esc_url( (string) ( $link['url'] ?? '' ) ); ?>">
						<?php echo esc_html( (string) $link['title'] ); ?>
						<?php echo tts_arrow(); // phpcs:ignore WordPress.Security.EscapeOutput ?>
					</a>
					<?php endif; ?>

					<?php if ( ! empty( $request['title'] ) ) : ?>
					<a class="link-arrow" href="<?php echo esc_url( (string) ( $request['url'] ?? '' ) ?: '#contact' ); ?>">
						<?php echo esc_html( (string) $request['title'] ); ?>
						<svg viewBox="0 0 24 24" width="18" height="18" aria-hidden="true" focusable="false">
							<path d="M4 12h15M13 6l6 6-6 6" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="square"></path>
						</svg>
					</a>
					<?php endif; ?>
				</div>
				<?php endif; ?>
			</article>
			<?php endforeach; ?>
		</div>
		<?php endif; ?>
	</div>
</section>
