<?php
/**
 * Шапка сайта: верхняя строка, логотип, меню с выпадающими списками,
 * кнопка целевого действия и кнопка мобильного меню (п. 5.1 ТЗ).
 */

defined( 'ABSPATH' ) || exit;
?>
<!doctype html>
<html <?php language_attributes(); ?> data-hero="render">
<head>
<meta charset="<?php bloginfo( 'charset' ); ?>">
<meta name="viewport" content="width=device-width, initial-scale=1">
<script>document.documentElement.classList.add('js');</script>
<?php wp_head(); ?>
</head>
<body <?php body_class(); ?>>
<?php wp_body_open(); ?>

<a class="skip-link" href="#content">Перейти к содержанию</a>

<div class="topline">
	<div class="shell topline__inner">
		<span class="topline__item">Инжиниринг для строительной индустрии Казахстана с 2006 года</span>
		<span class="topline__item topline__item--muted">Алматы · проектирование, поставка, монтаж и сервис</span>
	</div>
</div>

<header class="masthead">
	<div class="shell masthead__inner">
		<a class="brand" href="<?php echo esc_url( tts_url( 'home' ) ); ?>">
			<img class="brand__logo brand__logo--knockout" src="<?php echo esc_url( tts_asset( 'img/tts-logo-light.png' )[0] ); ?>" width="1148" height="426" alt="ТТС Инжиниринг">
			<img class="brand__logo brand__logo--ink" src="<?php echo esc_url( tts_asset( 'img/tts-logo.png' )[0] ); ?>" width="1148" height="426" alt="" aria-hidden="true">
			<span class="brand__text">
				<span class="brand__name">ТТС Инжиниринг</span>
				<span class="brand__region">Казахстан</span>
			</span>
		</a>

		<nav class="nav" id="nav" aria-label="Основная навигация">
			<a class="nav__link" href="<?php echo esc_url( tts_url( 'home' ) . '#catalog' ); ?>">Оборудование</a>

			<div class="nav__group">
				<button class="nav__link nav__toggle" type="button" aria-expanded="false" aria-controls="menu-catalog">
					Каталог
					<svg class="nav__chevron" viewBox="0 0 16 16" width="16" height="16" aria-hidden="true" focusable="false">
						<path d="M3 6l5 5 5-5" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="square"></path>
					</svg>
				</button>
				<div class="dropdown" id="menu-catalog" hidden>
					<?php foreach ( tts_directions() as $direction ) : ?>
					<a class="dropdown__link" <?php echo tts_link( $direction[0] ); // phpcs:ignore WordPress.Security.EscapeOutput ?>>
						<span class="dropdown__title"><?php echo esc_html( $direction[1] ); ?></span>
						<span class="dropdown__note"><?php echo esc_html( $direction[2] ); ?></span>
					</a>
					<?php endforeach; ?>
					<a class="dropdown__link dropdown__link--all" <?php echo tts_link( 'catalog' ); // phpcs:ignore WordPress.Security.EscapeOutput ?>>
						<span class="dropdown__title">Весь каталог</span>
						<span class="dropdown__note">23 позиции, поиск и фильтры</span>
					</a>
				</div>
			</div>

			<div class="nav__group">
				<button class="nav__link nav__toggle" type="button" aria-expanded="false" aria-controls="menu-service">
					Сервис
					<svg class="nav__chevron" viewBox="0 0 16 16" width="16" height="16" aria-hidden="true" focusable="false">
						<path d="M3 6l5 5 5-5" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="square"></path>
					</svg>
				</button>
				<div class="dropdown dropdown--narrow" id="menu-service" hidden>
					<a class="dropdown__link" <?php echo tts_link( 'service' ); // phpcs:ignore WordPress.Security.EscapeOutput ?>>
						<span class="dropdown__title">Инженерный сервис</span>
						<span class="dropdown__note">Диагностика, запуск и поддержка 24/7</span>
					</a>
					<a class="dropdown__link" <?php echo tts_link( 'parts' ); // phpcs:ignore WordPress.Security.EscapeOutput ?>>
						<span class="dropdown__title">Запасные части</span>
						<span class="dropdown__note">Подбор комплектующих и автоматики</span>
					</a>
				</div>
			</div>

			<a class="nav__link" href="<?php echo esc_url( tts_url( 'home' ) . '#company' ); ?>">О компании</a>
			<a class="nav__link" href="<?php echo esc_url( tts_url( 'home' ) . '#projects' ); ?>">Проекты</a>
		</nav>

		<a class="btn btn--solid btn--sm masthead__cta" href="<?php echo esc_url( tts_url( 'home' ) . '#contact' ); ?>">Получить расчёт</a>

		<button class="burger" id="burger" type="button" aria-expanded="false" aria-controls="nav" aria-label="Открыть меню">
			<span class="burger__bar"></span>
			<span class="burger__bar"></span>
			<span class="burger__bar"></span>
		</button>
	</div>
</header>

<main id="content">
