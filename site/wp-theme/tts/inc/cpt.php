<?php
/**
 * Разделы админки: Оборудование, Проекты, Вопросы и ответы, Услуги сервиса
 * и таксономия Направления (п. 12.2 ТЗ).
 *
 * Сущности заводим в теме, а не плагином: они описывают контент именно этого
 * сайта и без темы смысла не имеют. Поля к ним — в inc/fields.php.
 *
 * Порядок карточек редактор задаёт штатным полем «Порядок» (menu_order),
 * поэтому у типов записей включён page-attributes, а свои поля порядка
 * мы не плодим (п. 12.3 ТЗ: без неиспользуемых полей).
 */

defined( 'ABSPATH' ) || exit;

/** Общие настройки всех наших разделов. */
function tts_post_type_base(): array {
	return array(
		'public'              => true,
		'show_ui'             => true,
		'show_in_rest'        => true, // нужно для редактора Gutenberg
		'has_archive'         => false,
		'hierarchical'        => false,
		'supports'            => array( 'title', 'page-attributes', 'revisions' ),
	);
}

/** Подписи раздела по-русски: WordPress требует их полным набором. */
function tts_labels( string $single, string $many, string $new ): array {
	return array(
		'name'               => $many,
		'singular_name'      => $single,
		'menu_name'          => $many,
		'all_items'          => 'Все ' . mb_strtolower( $many ),
		'add_new'            => 'Добавить',
		'add_new_item'       => $new,
		'edit_item'          => 'Редактировать',
		'new_item'           => $new,
		'view_item'          => 'Посмотреть',
		'search_items'       => 'Искать',
		'not_found'          => 'Ничего не найдено',
		'not_found_in_trash' => 'В корзине ничего нет',
	);
}

/** Регистрация разделов и направлений. */
function tts_register_content(): void {
	register_post_type(
		'equipment',
		tts_post_type_base() + array(
			'labels'             => tts_labels( 'Оборудование', 'Оборудование', 'Новая модель' ),
			'menu_icon'          => 'dashicons-building',
			'menu_position'      => 20,
			// Модели показываются карточками в каталоге и в модальном окне,
			// отдельных адресов у них нет — иначе в поиск уйдут пустые страницы.
			'publicly_queryable' => false,
			'exclude_from_search' => true,
			'taxonomies'         => array( 'direction' ),
		)
	);

	register_post_type(
		'project',
		tts_post_type_base() + array(
			'labels'             => tts_labels( 'Проект', 'Проекты', 'Новый проект' ),
			'menu_icon'          => 'dashicons-location-alt',
			'menu_position'      => 21,
			'publicly_queryable' => false,
			'exclude_from_search' => true,
		)
	);

	// Вопросы и услуги показываются внутри страниц, отдельных адресов им не нужно.
	register_post_type(
		'faq',
		tts_post_type_base() + array(
			'labels'             => tts_labels( 'Вопрос', 'Вопросы и ответы', 'Новый вопрос' ),
			'menu_icon'          => 'dashicons-editor-help',
			'menu_position'      => 22,
			'publicly_queryable' => false,
			'exclude_from_search' => true,
		)
	);

	register_post_type(
		'service_item',
		tts_post_type_base() + array(
			'labels'             => tts_labels( 'Услуга', 'Услуги сервиса', 'Новая услуга' ),
			'menu_icon'          => 'dashicons-admin-tools',
			'menu_position'      => 23,
			'publicly_queryable' => false,
			'exclude_from_search' => true,
		)
	);

	register_taxonomy(
		'direction',
		array( 'equipment' ),
		array(
			'labels'            => array(
				'name'          => 'Направления',
				'singular_name' => 'Направление',
				'all_items'     => 'Все направления',
				'add_new_item'  => 'Добавить направление',
				'edit_item'     => 'Редактировать направление',
				'search_items'  => 'Искать направление',
				'not_found'     => 'Направлений нет',
			),
			'public'             => false,
			'show_ui'            => true,
			'hierarchical'       => true, // как рубрики: удобнее выбирать галочкой, а не вводить текст
			'show_admin_column'  => true,
			'show_in_rest'       => true,
		)
	);
}
add_action( 'init', 'tts_register_content' );

/**
 * В списках оборудования и проектов редактор должен видеть, что где лежит,
 * без открытия каждой записи.
 */
function tts_equipment_columns( array $columns ): array {
	$out = array( 'cb' => $columns['cb'], 'title' => 'Модель' );
	$out['tts_equipment_code']     = 'Код';
	$out['tts_equipment_capacity'] = 'Производительность / вместимость';
	$out['tts_equipment_status']   = 'Статус';
	$out['taxonomy-direction']     = 'Направление';
	$out['menu_order']             = 'Порядок';
	return $out;
}
add_filter( 'manage_equipment_posts_columns', 'tts_equipment_columns' );

function tts_equipment_column( string $column, int $post_id ): void {
	$statuses = array( 'stock' => 'В наличии', 'order' => 'Под заказ', 'request' => 'По запросу' );
	if ( 'tts_equipment_code' === $column || 'tts_equipment_capacity' === $column ) {
		echo esc_html( (string) get_post_meta( $post_id, $column, true ) );
	} elseif ( 'tts_equipment_status' === $column ) {
		$value = (string) get_post_meta( $post_id, 'tts_equipment_status', true );
		echo esc_html( $statuses[ $value ] ?? $value );
	} elseif ( 'menu_order' === $column ) {
		echo esc_html( (string) get_post_field( 'menu_order', $post_id ) );
	}
}
add_action( 'manage_equipment_posts_custom_column', 'tts_equipment_column', 10, 2 );

/** По умолчанию список сортируется по дате — нам нужен порядок редактора. */
function tts_order_admin_lists( WP_Query $query ): void {
	if ( ! is_admin() || ! $query->is_main_query() ) {
		return;
	}
	$types = array( 'equipment', 'project', 'faq', 'service_item' );
	if ( in_array( $query->get( 'post_type' ), $types, true ) && ! $query->get( 'orderby' ) ) {
		$query->set( 'orderby', 'menu_order title' );
		$query->set( 'order', 'ASC' );
	}
}
add_action( 'pre_get_posts', 'tts_order_admin_lists' );

/**
 * Выборка записей раздела в порядке, который задал редактор.
 *
 * @param string $type  Тип записи.
 * @param array  $args  Дополнительные условия выборки.
 * @return WP_Post[]
 */
function tts_items( string $type, array $args = array() ): array {
	return get_posts(
		array_merge(
			array(
				'post_type'      => $type,
				'posts_per_page' => -1,
				'orderby'        => array( 'menu_order' => 'ASC', 'title' => 'ASC' ),
			),
			$args
		)
	);
}
