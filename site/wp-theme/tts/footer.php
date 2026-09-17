<?php
/**
 * Подвал: описание компании, ссылки на оборудование, компанию и документы,
 * отдельный блок «Весь каталог» и копирайт с текущим годом (п. 5.2 ТЗ).
 *
 * Контакты и реквизиты выводим только те, что подтвердил заказчик.
 */

defined( 'ABSPATH' ) || exit;
?>
</main>

<footer class="footer">
	<div class="shell footer__grid">
		<div class="footer__brand">
			<img class="footer__logo" src="<?php echo esc_url( tts_asset( 'img/tts-logo-light.png' )[0] ); ?>" width="1148" height="426" alt="ТТС Инжиниринг">
			<p class="footer__about">Заводы, терминалы и технологические линии для строительной индустрии Казахстана.</p>
		</div>

		<nav class="footer__col" aria-label="Оборудование">
			<h3 class="footer__heading">Оборудование</h3>
			<?php foreach ( tts_directions() as $direction ) : ?>
			<a <?php echo tts_link( $direction[0] ); // phpcs:ignore WordPress.Security.EscapeOutput ?>><?php echo esc_html( $direction[1] ); ?></a>
			<?php endforeach; ?>
		</nav>

		<nav class="footer__col" aria-label="Компания">
			<h3 class="footer__heading">Компания</h3>
			<a href="<?php echo esc_url( tts_url( 'home' ) . '#company' ); ?>">О компании</a>
			<a href="<?php echo esc_url( tts_url( 'home' ) . '#projects' ); ?>">Проекты</a>
			<a <?php echo tts_link( 'service' ); // phpcs:ignore WordPress.Security.EscapeOutput ?>>Инженерный сервис</a>
			<a <?php echo tts_link( 'parts' ); // phpcs:ignore WordPress.Security.EscapeOutput ?>>Запасные части</a>
		</nav>

		<nav class="footer__col" aria-label="Документы">
			<h3 class="footer__heading">Документы</h3>
			<a <?php echo tts_link( 'privacy' ); // phpcs:ignore WordPress.Security.EscapeOutput ?>>Политика конфиденциальности</a>
			<a <?php echo tts_link( 'personal' ); // phpcs:ignore WordPress.Security.EscapeOutput ?>>Согласие на обработку персональных данных</a>
			<a <?php echo tts_link( 'cookie' ); // phpcs:ignore WordPress.Security.EscapeOutput ?>>Политика использования файлов cookie</a>
			<span class="footer__legal">БИН 191141028147</span>
		</nav>
	</div>

	<div class="shell">
		<div class="footer__catalog">
			<div class="footer__catalog-copy">
				<strong class="footer__catalog-title">Весь каталог оборудования</strong>
				<span class="footer__catalog-note">23 конфигурации в пяти направлениях — с поиском и фильтрами.</span>
			</div>
			<a class="btn btn--ghost footer__catalog-link" <?php echo tts_link( 'catalog' ); // phpcs:ignore WordPress.Security.EscapeOutput ?>>
				Открыть весь каталог
				<?php echo tts_arrow(); // phpcs:ignore WordPress.Security.EscapeOutput ?>
			</a>
		</div>
	</div>

	<div class="shell footer__bottom">
		<span>© <?php echo esc_html( gmdate( 'Y' ) ); ?> ТТС Инжиниринг Казахстан</span>
	</div>
</footer>

<div class="notice" id="notice" role="status" aria-live="polite" hidden></div>

<?php wp_footer(); ?>
</body>
</html>
