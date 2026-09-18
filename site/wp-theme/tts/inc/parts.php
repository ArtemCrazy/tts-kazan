<?php
/**
 * Повторяющиеся куски разметки страницы: шапка раздела с хлебными крошками
 * и обёртка секции. Классы те же, что в статической вёрстке.
 */

defined( 'ABSPATH' ) || exit;

/**
 * Шапка раздела: крошки, надзаголовок, H1 и лид (п. 14 ТЗ — крошки на всех
 * внутренних страницах, один H1 на страницу).
 *
 * @param array $spec kicker, title, lead, crumb, under_catalog.
 */
function tts_page_head( array $spec ): void {
	$crumbs = array(
		'<a class="crumbs__link" href="' . esc_url( tts_url( 'home' ) ) . '">Главная</a>',
	);
	$schema = array( array( 'name' => 'Главная', 'url' => tts_url( 'home' ) ) );
	if ( ! empty( $spec['under_catalog'] ) ) {
		$crumbs[] = '<a class="crumbs__link" href="' . esc_url( tts_url( 'catalog' ) )
			. '">Каталог оборудования</a>';
		$schema[] = array( 'name' => 'Каталог оборудования', 'url' => tts_url( 'catalog' ) );
	}
	$schema[] = array( 'name' => $spec['crumb'], 'url' => '' );
	tts_schema_crumbs( $schema );
	?>
	<section class="page-head">
		<div class="shell">
			<nav class="crumbs" aria-label="Хлебные крошки">
				<ol class="crumbs__list">
					<?php foreach ( $crumbs as $crumb ) : ?>
					<li class="crumbs__item"><?php echo $crumb; // phpcs:ignore WordPress.Security.EscapeOutput ?></li>
					<?php endforeach; ?>
					<li class="crumbs__item" aria-current="page"><?php echo esc_html( $spec['crumb'] ); ?></li>
				</ol>
			</nav>

			<?php if ( ! empty( $spec['kicker'] ) ) : ?>
			<p class="kicker"><?php echo esc_html( $spec['kicker'] ); ?></p>
			<?php endif; ?>
			<h1 class="page-head__title"><?php echo esc_html( $spec['title'] ); ?></h1>
			<?php if ( ! empty( $spec['lead'] ) ) : ?>
			<p class="page-head__lead"><?php echo esc_html( $spec['lead'] ); ?></p>
			<?php endif; ?>
		</div>
	</section>
	<?php
}

/**
 * Подписи ячеек в таблицах редактора.
 *
 * На телефоне таблица складывается в карточки и шапка скрывается, поэтому
 * каждой ячейке нужна своя подпись. Редактор про это знать не должен —
 * подставляем подписи сами, из шапки таблицы.
 */
function tts_table_labels( string $content, array $block ): string {
	if ( 'core/table' !== ( $block['blockName'] ?? '' ) || ! str_contains( $content, '<thead' ) ) {
		return $content;
	}

	preg_match( '~<thead>.*?</thead>~s', $content, $head );
	if ( ! $head ) {
		return $content;
	}
	preg_match_all( '~<th[^>]*>(.*?)</th>~s', $head[0], $cells );
	$labels = array_map(
		static fn( $cell ) => trim( wp_strip_all_tags( $cell ) ),
		$cells[1] ?? array()
	);
	if ( ! $labels ) {
		return $content;
	}

	return preg_replace_callback(
		'~<tbody>.*?</tbody>~s',
		static function ( $body ) use ( $labels ) {
			$index = 0;
			return preg_replace_callback(
				'~<td(\s[^>]*)?>~',
				static function ( $cell ) use ( $labels, &$index ) {
					$label = $labels[ $index % count( $labels ) ] ?? '';
					++$index;
					$attrs = $cell[1] ?? '';
					return '<td' . $attrs . ' data-label="' . esc_attr( $label ) . '">';
				},
				$body[0]
			);
		},
		$content
	);
}
add_filter( 'render_block', 'tts_table_labels', 10, 2 );

/** Краткое описание страницы для лида: берём из поля SEO или из выдержки. */
function tts_page_lead( ?WP_Post $post = null ): string {
	$post = $post ?: get_post();
	if ( ! $post ) {
		return '';
	}
	$lead = get_post_meta( $post->ID, 'tts_lead', true );
	return $lead ?: get_the_excerpt( $post );
}

/**
 * Логотип из «Настроек сайта → Шапка». Если файл не загружен — логотип темы.
 * Вариантов два: светлый для тёмного фона и тёмный для светлого.
 *
 * @return array{0:string,1:int,2:int} адрес, ширина, высота
 */
function tts_logo( string $variant ): array {
	$field = 'light' === $variant ? 'tts_settings_logo_light' : 'tts_settings_logo_dark';
	$id    = function_exists( 'get_field' ) ? (int) get_field( $field, 'option' ) : 0;
	if ( $id ) {
		$image = wp_get_attachment_image_src( $id, 'full' );
		if ( $image ) {
			return array( (string) $image[0], (int) $image[1], (int) $image[2] );
		}
	}
	$file = 'light' === $variant ? 'img/tts-logo-light.png' : 'img/tts-logo.png';
	return array( tts_asset( $file )[0], 1148, 426 );
}

/**
 * Куда ведёт кнопка в шапке: к форме на этой же странице, а если формы
 * на странице нет — к форме на главной. Так же сделано в статической версии.
 */
function tts_contact_url(): string {
	$post = get_queried_object();
	if ( $post instanceof WP_Post && has_block( 'acf/tts-form', $post ) ) {
		return '#contact';
	}
	return tts_url( 'home' ) . '#contact';
}

/**
 * Контакты в подвале — только заполненные в «Настройках сайта → Контакты».
 * Пока заказчик их не подтвердил, поля пустые и блока на сайте нет.
 */
function tts_footer_contacts(): void {
	$phone   = tts_setting( 'tts_settings_phone' );
	$email   = tts_setting( 'tts_settings_email' );
	$address = tts_setting( 'tts_settings_address' );
	$hours   = tts_setting( 'tts_settings_hours' );
	$socials = array();
	if ( function_exists( 'get_field' ) ) {
		foreach ( tts_rows( get_field( 'tts_settings_socials', 'option' ) ) as $row ) {
			$title = trim( (string) ( $row['tts_settings_social_title'] ?? '' ) );
			$url   = trim( (string) ( $row['tts_settings_social_url'] ?? '' ) );
			if ( $title && $url ) {
				$socials[] = array( $title, $url );
			}
		}
	}
	if ( ! $phone && ! $email && ! $address && ! $hours && ! $socials ) {
		return;
	}
	?>
	<address class="footer__contacts">
		<?php if ( $phone ) : ?>
		<a href="tel:<?php echo esc_attr( preg_replace( '/[^\d+]/', '', $phone ) ); ?>"><?php echo esc_html( $phone ); ?></a>
		<?php endif; ?>
		<?php if ( $email ) : ?>
		<a href="mailto:<?php echo esc_attr( antispambot( $email ) ); ?>"><?php echo esc_html( antispambot( $email ) ); ?></a>
		<?php endif; ?>
		<?php if ( $address ) : ?>
		<span><?php echo esc_html( $address ); ?></span>
		<?php endif; ?>
		<?php if ( $hours ) : ?>
		<span><?php echo esc_html( $hours ); ?></span>
		<?php endif; ?>
		<?php if ( $socials ) : ?>
		<span class="footer__socials">
			<?php foreach ( $socials as list( $title, $url ) ) : ?>
			<a href="<?php echo esc_url( $url ); ?>" target="_blank" rel="noopener"><?php echo esc_html( $title ); ?></a>
			<?php endforeach; ?>
		</span>
		<?php endif; ?>
	</address>
	<?php
}
