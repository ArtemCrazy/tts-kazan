<?php
/**
 * Заявки с сайта (п. 11 ТЗ).
 *
 * Что здесь есть:
 *  - раздел «Заявки» в админке: заявка сохраняется, даже если письмо не ушло;
 *  - приём формы по адресу /wp-json/tts/v1/lead с серверной проверкой полей;
 *  - защита от спама: скрытое поле-ловушка, проверка времени заполнения
 *    и ограничение по адресу отправителя;
 *  - письмо получателю из «Настроек сайта» (отправка идёт через SMTP);
 *  - автоматическая очистка старых заявок, чтобы персональные данные не
 *    лежали в админке дольше согласованного срока.
 *
 * Токенов и паролей здесь нет: адрес получателя и настройки SMTP задаются
 * в админке (п. 11.2 ТЗ — не хранить доступы в коде темы).
 */

defined( 'ABSPATH' ) || exit;

/** Раздел «Заявки»: только для администратора, из поиска исключён. */
function tts_register_leads(): void {
	register_post_type(
		'lead',
		array(
			'labels'              => array(
				'name'          => 'Заявки',
				'singular_name' => 'Заявка',
				'menu_name'     => 'Заявки',
				'all_items'     => 'Все заявки',
				'edit_item'     => 'Заявка',
				'search_items'  => 'Искать заявку',
				'not_found'     => 'Заявок нет',
			),
			'public'              => false,
			'show_ui'             => true,
			'menu_icon'           => 'dashicons-email-alt',
			'menu_position'       => 25,
			'exclude_from_search' => true,
			'publicly_queryable'  => false,
			'capability_type'     => 'post',
			'supports'            => array( 'title' ),
			'map_meta_cap'        => true,
		)
	);
}
add_action( 'init', 'tts_register_leads' );

/** Колонки списка заявок: суть видна без открытия записи. */
function tts_lead_columns(): array {
	return array(
		'cb'            => '<input type="checkbox">',
		'title'         => 'Заявка',
		'tts_phone'     => 'Телефон',
		'tts_direction' => 'Направление',
		'tts_page'      => 'Страница',
		'date'          => 'Когда',
	);
}
add_filter( 'manage_lead_posts_columns', 'tts_lead_columns' );

function tts_lead_column( string $column, int $post_id ): void {
	$map = array( 'tts_phone' => 'phone', 'tts_direction' => 'direction', 'tts_page' => 'page' );
	if ( isset( $map[ $column ] ) ) {
		echo esc_html( (string) get_post_meta( $post_id, 'tts_lead_' . $map[ $column ], true ) );
	}
}
add_action( 'manage_lead_posts_custom_column', 'tts_lead_column', 10, 2 );

/** Поля заявки на экране редактирования: просто читаемый список. */
function tts_lead_details(): void {
	add_meta_box(
		'tts_lead_details',
		'Данные заявки',
		static function ( WP_Post $post ) {
			$labels = array(
				'name'      => 'Имя',
				'phone'     => 'Телефон',
				'comment'   => 'Комментарий',
				'direction' => 'Направление',
				'model'     => 'Модель',
				'urgency'   => 'Срочность или категория',
				'filters'   => 'Фильтры каталога',
				'source'    => 'Откуда отправлено',
				'page'      => 'Страница',
				'utm'       => 'Рекламные метки',
				'gclid'     => 'gclid',
				'consent_at'  => 'Согласие дано',
				'consent_doc' => 'Текст согласия',
			);
			echo '<table class="widefat striped">';
			foreach ( $labels as $key => $label ) {
				$value = (string) get_post_meta( $post->ID, 'tts_lead_' . $key, true );
				printf(
					'<tr><th style="width:180px">%s</th><td>%s</td></tr>',
					esc_html( $label ),
					esc_html( $value ?: '—' )
				);
			}
			echo '</table>';
		},
		'lead',
		'normal'
	);
}
add_action( 'add_meta_boxes', 'tts_lead_details' );

/** Адрес приёма формы. */
function tts_register_lead_route(): void {
	register_rest_route(
		'tts/v1',
		'/lead',
		array(
			'methods'             => 'POST',
			'callback'            => 'tts_handle_lead',
			// Форма открыта всем посетителям, поэтому проверяем не права,
			// а сами данные: ловушку, время заполнения и частоту отправок.
			'permission_callback' => '__return_true',
		)
	);
}
add_action( 'rest_api_init', 'tts_register_lead_route' );

/** Телефон Казахстана и России: маску не навязываем, считаем цифры (п. 11.2 ТЗ). */
function tts_valid_phone( string $phone ): bool {
	$digits = preg_replace( '/\D+/', '', $phone );
	return strlen( (string) $digits ) >= 10 && strlen( (string) $digits ) <= 15;
}

/** Приём заявки. */
function tts_handle_lead( WP_REST_Request $request ) {
	$data = array_map(
		static fn( $value ) => is_string( $value ) ? trim( wp_unslash( $value ) ) : $value,
		(array) $request->get_json_params()
	);

	// Ловушка: настоящий посетитель это поле не видит и не заполняет.
	if ( ! empty( $data['company'] ) ) {
		return new WP_REST_Response( array( 'ok' => true ), 200 );
	}

	// Слишком быстрая отправка — почти всегда робот.
	$spent = (int) ( $data['spent'] ?? 0 );
	if ( $spent > 0 && $spent < 2 ) {
		return new WP_REST_Response(
			array( 'ok' => false, 'errors' => array( 'form' => 'Отправка не прошла, попробуйте ещё раз.' ) ),
			422
		);
	}

	$errors = array();
	$name    = (string) ( $data['name'] ?? '' );
	$phone   = (string) ( $data['phone'] ?? '' );
	$consent = ! empty( $data['consent'] );

	if ( mb_strlen( $name ) < 2 ) {
		$errors['name'] = 'Укажите имя';
	}
	if ( ! tts_valid_phone( $phone ) ) {
		$errors['phone'] = 'Проверьте номер';
	}
	if ( ! $consent ) {
		$errors['consent'] = 'Нужно согласие';
	}
	if ( $errors ) {
		return new WP_REST_Response( array( 'ok' => false, 'errors' => $errors ), 422 );
	}

	// Ограничение частоты: не больше пяти заявок за десять минут с одного адреса.
	$fingerprint = 'tts_lead_' . md5( (string) ( $_SERVER['REMOTE_ADDR'] ?? '' ) );
	$recent      = (int) get_transient( $fingerprint );
	if ( $recent >= 5 ) {
		return new WP_REST_Response(
			array( 'ok' => false, 'errors' => array( 'form' => 'Слишком много заявок подряд. Напишите нам на почту.' ) ),
			429
		);
	}
	set_transient( $fingerprint, $recent + 1, 10 * MINUTE_IN_SECONDS );

	$fields = array(
		'name'      => $name,
		'phone'     => $phone,
		'comment'   => (string) ( $data['comment'] ?? '' ),
		'direction' => (string) ( $data['direction'] ?? '' ),
		'model'     => (string) ( $data['model'] ?? '' ),
		'urgency'   => (string) ( $data['urgency'] ?? '' ),
		'filters'   => (string) ( $data['filters'] ?? '' ),
		'source'    => (string) ( $data['source'] ?? '' ),
		'page'      => (string) ( $data['page'] ?? '' ),
		'utm'       => (string) ( $data['utm'] ?? '' ),
		'gclid'     => (string) ( $data['gclid'] ?? '' ),
	);

	$id = wp_insert_post(
		array(
			'post_type'   => 'lead',
			'post_status' => 'publish',
			'post_title'  => sprintf(
				'%s — %s%s',
				$name,
				$phone,
				$fields['direction'] ? ' · ' . $fields['direction'] : ''
			),
		),
		true
	);
	if ( is_wp_error( $id ) ) {
		return new WP_REST_Response(
			array( 'ok' => false, 'errors' => array( 'form' => 'Не удалось сохранить заявку.' ) ),
			500
		);
	}
	foreach ( $fields as $key => $value ) {
		if ( '' !== $value ) {
			update_post_meta( $id, 'tts_lead_' . $key, sanitize_textarea_field( $value ) );
		}
	}

	// Фиксируем согласие: когда дано и на какую редакцию текста. Без этого
	// подтвердить согласие субъекта нечем (аудит перед запуском, forms.consent-log).
	update_post_meta( $id, 'tts_lead_consent_at', current_time( 'mysql' ) );
	$page = get_page_by_path( 'personal-data' );
	update_post_meta(
		$id,
		'tts_lead_consent_doc',
		$page ? get_permalink( $page ) . ' (редакция от ' . get_the_modified_date( 'd.m.Y', $page ) . ')' : 'согласие на обработку персональных данных'
	);

	tts_send_lead_mail( (int) $id, $fields );

	return new WP_REST_Response( array( 'ok' => true ), 200 );
}

/**
 * Отправитель писем сайта.
 *
 * По умолчанию WordPress пишет от wordpress@домен — такие письма чаще
 * попадают в спам, а ответить на них некуда. Берём ящик сайта: он на том же
 * сервере, поэтому проходит по SPF и подписывается DKIM хостинга.
 */
function tts_mail_from(): string {
	$box = tts_setting( 'tts_settings_mail_from' );
	return $box ?: 'info@' . wp_parse_url( home_url(), PHP_URL_HOST );
}
add_filter( 'wp_mail_from', 'tts_mail_from' );

function tts_mail_from_name(): string {
	return tts_setting( 'tts_settings_brand_name' ) ?: get_bloginfo( 'name' );
}
add_filter( 'wp_mail_from_name', 'tts_mail_from_name' );

/** Письмо о заявке получателю из настроек. */
function tts_send_lead_mail( int $id, array $fields ): void {
	$to = function_exists( 'get_field' ) ? (string) get_field( 'tts_settings_lead_email', 'option' ) : '';
	if ( ! $to ) {
		// Пока получатель не подтверждён заказчиком, письма не отправляем:
		// заявка всё равно лежит в разделе «Заявки».
		update_post_meta( $id, 'tts_lead_mail', 'получатель не задан' );
		return;
	}

	$labels = array(
		'name'      => 'Имя',
		'phone'     => 'Телефон',
		'comment'   => 'Комментарий',
		'direction' => 'Направление',
		'model'     => 'Модель',
		'urgency'   => 'Срочность или категория',
		'filters'   => 'Фильтры каталога',
		'source'    => 'Откуда отправлено',
		'page'      => 'Страница',
		'utm'       => 'Рекламные метки',
		'gclid'     => 'gclid',
	);
	$lines = array();
	foreach ( $labels as $key => $label ) {
		if ( ! empty( $fields[ $key ] ) ) {
			$lines[] = $label . ': ' . $fields[ $key ];
		}
	}
	$lines[] = '';
	// get_edit_post_link() вне админки возвращает пустоту, поэтому собираем адрес сами.
	$lines[] = 'Заявка в админке: ' . admin_url( 'post.php?post=' . $id . '&action=edit' );

	$subject = (string) get_field( 'tts_settings_lead_subject', 'option' )
		?: 'Заявка с сайта ТТС Инжиниринг';

	$sent = wp_mail(
		array_map( 'trim', explode( ',', $to ) ),
		$subject,
		implode( "\n", $lines )
	);
	update_post_meta( $id, 'tts_lead_mail', $sent ? 'отправлено' : 'ошибка отправки' );
}

/**
 * Очистка старых заявок: персональные данные не должны лежать в админке
 * дольше срока, который заказчик указал в настройках (п. 11.2 ТЗ).
 */
function tts_schedule_lead_cleanup(): void {
	if ( ! wp_next_scheduled( 'tts_clean_leads' ) ) {
		wp_schedule_event( time() + HOUR_IN_SECONDS, 'daily', 'tts_clean_leads' );
	}
}
add_action( 'init', 'tts_schedule_lead_cleanup' );

function tts_clean_leads(): void {
	$days = function_exists( 'get_field' ) ? (int) get_field( 'tts_settings_lead_days', 'option' ) : 0;
	if ( $days < 1 ) {
		return; // срок не задан — ничего не удаляем
	}

	$old = get_posts(
		array(
			'post_type'      => 'lead',
			'posts_per_page' => 100,
			'date_query'     => array( array( 'before' => $days . ' days ago' ) ),
			'fields'         => 'ids',
		)
	);
	foreach ( $old as $id ) {
		wp_delete_post( (int) $id, true );
	}
}
add_action( 'tts_clean_leads', 'tts_clean_leads' );
