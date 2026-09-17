<?php
/**
 * Аналитика и согласие на cookie (п. 13 и 17 ТЗ).
 *
 * Идентификаторы задаются в «Настройках сайта». Пустое поле означает, что
 * счётчик не подключается — на тестовом контуре так и нужно.
 *
 * Необязательная аналитика грузится только после согласия посетителя:
 * до этого баннер показан, а счётчики не подключены.
 */

defined( 'ABSPATH' ) || exit;

/** Значение настройки сайта. */
function tts_setting( string $name ): string {
	if ( ! function_exists( 'get_field' ) ) {
		return '';
	}
	return trim( (string) get_field( $name, 'option' ) );
}

/** Есть ли что подключать после согласия. */
function tts_has_optional_analytics(): bool {
	foreach ( array( 'tts_settings_gtm_id', 'tts_settings_metrika_id', 'tts_settings_roistat_key' ) as $name ) {
		if ( tts_setting( $name ) ) {
			return true;
		}
	}
	return false;
}

/**
 * Базовый слой данных и отправка событий по клику на телефон и почту
 * (события phone_click и email_click из п. 13 ТЗ).
 */
function tts_datalayer(): void {
	if ( is_admin() ) {
		return;
	}
	?>
	<script>
	window.dataLayer = window.dataLayer || [];
	document.addEventListener('click', function (event) {
		var link = event.target.closest('a[href^="tel:"], a[href^="mailto:"]');
		if (!link) return;
		window.dataLayer.push({
			event: link.getAttribute('href').indexOf('tel:') === 0 ? 'phone_click' : 'email_click',
			page_url: location.href,
			page_title: document.title
		});
	});
	</script>
	<?php
}
add_action( 'wp_head', 'tts_datalayer', 5 );

/**
 * Счётчики. Подключаются одним скриптом после согласия: так до ответа
 * посетителя необязательная аналитика не загружается.
 */
function tts_analytics(): void {
	if ( is_admin() || ! tts_has_optional_analytics() ) {
		return;
	}

	$gtm     = tts_setting( 'tts_settings_gtm_id' );
	$metrika = tts_setting( 'tts_settings_metrika_id' );
	$roistat = tts_setting( 'tts_settings_roistat_key' );
	?>
	<script>
	(function () {
		var settings = <?php echo wp_json_encode(
			array(
				'gtm'     => $gtm,
				'metrika' => $metrika,
				'roistat' => $roistat,
			)
		); ?>;

		function script(src, attrs) {
			var node = document.createElement('script');
			node.async = true;
			node.src = src;
			for (var key in attrs) node.setAttribute(key, attrs[key]);
			document.head.appendChild(node);
		}

		window.ttsAnalytics = function () {
			if (window.ttsAnalyticsLoaded) return;
			window.ttsAnalyticsLoaded = true;

			if (settings.gtm) {
				window.dataLayer.push({ 'gtm.start': Date.now(), event: 'gtm.js' });
				script('https://www.googletagmanager.com/gtm.js?id=' + settings.gtm);
			}

			if (settings.metrika) {
				script('https://mc.yandex.ru/metrika/tag.js');
				window.ym = window.ym || function () { (window.ym.a = window.ym.a || []).push(arguments); };
				window.ym.l = Date.now();
				window.ym(settings.metrika, 'init', { webvisor: true, clickmap: true, trackLinks: true, accurateTrackBounce: true });
			}

			if (settings.roistat) {
				window.roistatProjectId = settings.roistat;
				script('https://cloud.roistat.com/dist/module.js');
			}
		};

		// Согласие уже есть — подключаем сразу, иначе ждём ответа в баннере.
		try {
			if (localStorage.getItem('ttsConsent') === 'all') window.ttsAnalytics();
		} catch (error) { /* хранилище недоступно — считаем, что согласия нет */ }
	})();
	</script>
	<?php
}
add_action( 'wp_head', 'tts_analytics', 6 );

/**
 * Баннер согласия (п. 17 ТЗ): два действия, выбор сохраняется, изменить
 * его можно ссылкой в подвале.
 */
function tts_cookie_banner(): void {
	if ( is_admin() || ! tts_has_optional_analytics() ) {
		return;
	}
	?>
	<div class="consent-bar" id="consentBar" role="region" aria-label="Использование cookie" hidden>
		<p class="consent-bar__text">
			Мы используем cookie, чтобы сайт работал и чтобы понимать, какие разделы полезны.
			Подробнее — в <a class="consent-bar__link" href="<?php echo esc_url( tts_url( 'cookie' ) ); ?>">политике использования файлов cookie</a>.
		</p>
		<div class="consent-bar__actions">
			<button class="btn btn--ghost btn--sm" type="button" data-consent="necessary">Только необходимые</button>
			<button class="btn btn--solid btn--sm" type="button" data-consent="all">Принять</button>
		</div>
	</div>
	<script>
	(function () {
		var bar = document.getElementById('consentBar');
		if (!bar) return;

		function saved() {
			try { return localStorage.getItem('ttsConsent'); } catch (error) { return null; }
		}

		function decide(choice) {
			try { localStorage.setItem('ttsConsent', choice); } catch (error) { /* не страшно */ }
			bar.hidden = true;
			if (choice === 'all' && window.ttsAnalytics) window.ttsAnalytics();
		}

		if (!saved()) bar.hidden = false;

		bar.addEventListener('click', function (event) {
			var button = event.target.closest('[data-consent]');
			if (button) decide(button.dataset.consent);
		});

		// Ссылка в подвале позволяет вернуться к выбору (п. 17 ТЗ)
		document.addEventListener('click', function (event) {
			if (event.target.closest('[data-consent-reset]')) {
				event.preventDefault();
				try { localStorage.removeItem('ttsConsent'); } catch (error) { /* не страшно */ }
				bar.hidden = false;
			}
		});
	})();
	</script>
	<?php
}
add_action( 'wp_footer', 'tts_cookie_banner' );
