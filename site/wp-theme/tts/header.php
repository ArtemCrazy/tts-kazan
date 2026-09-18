<?php
/**
 * Шапка сайта: верхняя строка, логотип, меню с выпадающими списками,
 * кнопка целевого действия и кнопка мобильного меню (п. 5.1 ТЗ).
 *
 * Пункты меню — «Внешний вид → Меню», область «Шапка сайта» (inc/menus.php).
 * Логотип, верхняя строка и текст кнопки — «Настройки сайта → Шапка».
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
		<?php
		$left  = tts_setting( 'tts_settings_topline_left' )
			?: 'Инжиниринг для строительной индустрии Казахстана с 2006 года';
		// Правая подпись бывает своя у страницы (поле «Верхняя строка, справа»).
		$page  = get_queried_object();
		$own   = $page instanceof WP_Post && function_exists( 'get_field' )
			? trim( (string) get_field( 'tts_page_topline', $page->ID ) ) : '';
		$right = $own ?: ( tts_setting( 'tts_settings_topline_right' )
			?: 'Поставка, монтаж и сервис по Казахстану' );
		?>
		<span class="topline__item"><?php echo esc_html( $left ); ?></span>
		<span class="topline__item topline__item--muted"><?php echo esc_html( $right ); ?></span>
	</div>
</div>

<header class="masthead">
	<div class="shell masthead__inner">
		<a class="brand" href="<?php echo esc_url( tts_url( 'home' ) ); ?>">
			<?php
			list( $knockout, $kw, $kh ) = tts_logo( 'light' );
			list( $ink, $iw, $ih )      = tts_logo( 'dark' );
			$brand                      = tts_setting( 'tts_settings_brand_name' ) ?: 'ТТС Инжиниринг';
			?>
			<img class="brand__logo brand__logo--knockout" src="<?php echo esc_url( $knockout ); ?>" width="<?php echo (int) $kw; ?>" height="<?php echo (int) $kh; ?>" alt="<?php echo esc_attr( $brand ); ?>">
			<img class="brand__logo brand__logo--ink" src="<?php echo esc_url( $ink ); ?>" width="<?php echo (int) $iw; ?>" height="<?php echo (int) $ih; ?>" alt="" aria-hidden="true">
			<span class="brand__text">
				<span class="brand__name"><?php echo esc_html( $brand ); ?></span>
				<span class="brand__region"><?php echo esc_html( tts_setting( 'tts_settings_brand_region' ) ?: 'Казахстан' ); ?></span>
			</span>
		</a>

		<nav class="nav" id="nav" aria-label="Основная навигация">
			<?php tts_header_menu(); ?>
		</nav>

		<a class="btn btn--solid btn--sm masthead__cta" href="<?php echo esc_url( tts_contact_url() ); ?>"><?php echo esc_html( tts_setting( 'tts_settings_header_button' ) ?: 'Получить расчёт' ); ?></a>

		<button class="burger" id="burger" type="button" aria-expanded="false" aria-controls="nav" aria-label="Открыть меню">
			<span class="burger__bar"></span>
			<span class="burger__bar"></span>
			<span class="burger__bar"></span>
		</button>
	</div>
</header>

<main id="content">
