<?php
/**
 * Страница «не найдено» (п. 4 ТЗ, системные страницы).
 * Разметка совпадает со статической версией сайта.
 */

defined( 'ABSPATH' ) || exit;

get_header();
?>
<section class="page-head">
	<div class="shell">
		<span class="error__code">404</span>
		<h1 class="page-head__title">Страница не найдена</h1>
		<p class="page-head__lead">Возможно, адрес набран с опечаткой или раздел переехал. Ниже — основные разделы сайта.</p>

		<div class="page-head__actions">
			<a class="btn btn--solid btn--lg" href="<?php echo esc_url( tts_url( 'home' ) ); ?>">
				На главную
				<?php echo tts_arrow(); // phpcs:ignore WordPress.Security.EscapeOutput ?>
			</a>
			<a class="btn btn--ghost btn--lg" href="<?php echo esc_url( tts_url( 'catalog' ) ); ?>">Открыть каталог</a>
		</div>
	</div>
</section>

<section class="page-section page-section--light on-light">
	<div class="shell">
		<div class="section-head">
			<p class="kicker section-head__kicker">Разделы сайта</p>
			<h2 class="section-head__title">Куда можно перейти</h2>
			<p class="section-head__lead">Пять направлений оборудования, общий каталог и сервисные разделы.</p>
		</div>

		<div class="cards cards--4">
			<?php foreach ( tts_directions() as $direction ) : ?>
			<a class="card card--link" <?php echo tts_link( $direction[0] ); // phpcs:ignore WordPress.Security.EscapeOutput ?>>
				<h3 class="card__title"><?php echo esc_html( $direction[1] ); ?></h3>
				<p class="card__text"><?php echo esc_html( $direction[2] ); ?></p>
			</a>
			<?php endforeach; ?>
			<a class="card card--link" <?php echo tts_link( 'catalog' ); // phpcs:ignore WordPress.Security.EscapeOutput ?>>
				<h3 class="card__title">Весь каталог</h3>
				<p class="card__text">23 позиции с поиском и фильтрами</p>
			</a>
			<a class="card card--link" <?php echo tts_link( 'service' ); // phpcs:ignore WordPress.Security.EscapeOutput ?>>
				<h3 class="card__title">Инженерный сервис</h3>
				<p class="card__text">Аудит, монтаж, пусконаладка и поддержка</p>
			</a>
			<a class="card card--link" <?php echo tts_link( 'parts' ); // phpcs:ignore WordPress.Security.EscapeOutput ?>>
				<h3 class="card__title">Запасные части</h3>
				<p class="card__text">Комплектующие и автоматика</p>
			</a>
		</div>
	</div>
</section>
<?php
get_footer();
