<?php
/**
 * Подвал: описание компании, ссылки на оборудование, компанию и документы,
 * отдельный блок «Весь каталог» и копирайт с текущим годом (п. 5.2 ТЗ).
 *
 * Контакты и реквизиты выводим только те, что подтвердил заказчик.
 *
 * Где что редактируется:
 *  - ссылки в колонках — «Внешний вид → Меню», области «Подвал: …»;
 *    заголовок колонки — название меню;
 *  - описание, реквизиты, плашка каталога, копирайт — «Настройки сайта → Подвал»;
 *  - телефон, почта, адрес, часы, соцсети — «Настройки сайта → Контакты».
 */

defined( 'ABSPATH' ) || exit;
?>
</main>

<footer class="footer">
	<div class="shell footer__grid">
		<div class="footer__brand">
			<?php list( $logo, $lw, $lh ) = tts_logo( 'light' ); ?>
			<img class="footer__logo" src="<?php echo esc_url( $logo ); ?>" width="<?php echo (int) $lw; ?>" height="<?php echo (int) $lh; ?>" alt="<?php echo esc_attr( tts_setting( 'tts_settings_brand_name' ) ?: 'ТТС Инжиниринг' ); ?>">
			<p class="footer__about"><?php
				echo esc_html(
					tts_setting( 'tts_settings_footer_about' )
					?: 'Заводы, терминалы и технологические линии для строительной индустрии Казахстана.'
				);
			?></p>
			<?php tts_footer_contacts(); ?>
		</div>

		<?php
		tts_footer_menu( 'footer-1' );
		tts_footer_menu( 'footer-2' );

		// Под документами — реквизиты и, если подключена аналитика, настройки cookie.
		$extra = '';
		$legal = tts_setting( 'tts_settings_footer_legal' );
		if ( $legal ) {
			$extra .= '<span class="footer__legal">' . esc_html( $legal ) . '</span>';
		}
		if ( tts_has_optional_analytics() ) {
			$extra .= '<a href="#" data-consent-reset>Настройки cookie</a>';
		}
		tts_footer_menu( 'footer-3', $extra );
		?>
	</div>

	<?php
	// На странице общего каталога плашка ведёт не в каталог, а на главную — как в статике.
	$on_catalog    = tts_is_current( 'catalog' );
	$prefix        = $on_catalog ? 'tts_settings_footer_home_' : 'tts_settings_footer_catalog_';
	$catalog_title = tts_setting( $prefix . 'title' );
	$catalog_note  = tts_setting( $prefix . 'note' );
	$catalog_label = tts_setting( $prefix . 'button' );
	?>
	<?php if ( $catalog_title || $catalog_label ) : ?>
	<div class="shell">
		<div class="footer__catalog">
			<div class="footer__catalog-copy">
				<strong class="footer__catalog-title"><?php echo esc_html( $catalog_title ); ?></strong>
				<?php if ( $catalog_note ) : ?>
				<span class="footer__catalog-note"><?php echo esc_html( $catalog_note ); ?></span>
				<?php endif; ?>
			</div>
			<?php if ( $catalog_label ) : ?>
			<a class="btn btn--ghost footer__catalog-link" <?php echo tts_link( $on_catalog ? 'home' : 'catalog' ); // phpcs:ignore WordPress.Security.EscapeOutput ?>>
				<?php echo esc_html( $catalog_label ); ?>
				<?php echo tts_arrow(); // phpcs:ignore WordPress.Security.EscapeOutput ?>
			</a>
			<?php endif; ?>
		</div>
	</div>
	<?php endif; ?>

	<div class="shell footer__bottom">
		<span>© <?php echo esc_html( gmdate( 'Y' ) ); ?> <?php echo esc_html( tts_setting( 'tts_settings_copyright' ) ?: 'ТТС Инжиниринг Казахстан' ); ?></span>
	</div>
</footer>

<div class="notice" id="notice" role="status" aria-live="polite" hidden></div>

<?php wp_footer(); ?>
</body>
</html>
