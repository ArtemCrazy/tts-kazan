<?php
/**
 * Блок «Модели оборудования» — карточки моделей на странице направления
 * (tools/pagekit.py, функция models).
 *
 * Свои тексты блок не хранит: карточки собираются из раздела «Оборудование».
 * Блок только выбирает направление и вид сетки, поэтому правка модели
 * в админке сразу видна и здесь, и в общем каталоге (п. 12.2 ТЗ).
 *
 * @var array $block
 */

defined( 'ABSPATH' ) || exit;

$kicker = (string) get_field( 'tts_models_kicker' );
$title  = (string) get_field( 'tts_models_title' );
$lead   = (string) get_field( 'tts_models_lead' );
$limit  = (int) get_field( 'tts_models_limit' );
$grid   = (string) get_field( 'tts_models_grid' );
$cta    = (string) get_field( 'tts_models_cta' );

// Классы тона секции — как в pagekit.section(). Тёмного тона здесь нет:
// карточка модели — светлая поверхность, на тёмном фоне её в вёрстке нет.
$tones = array(
	'light' => 'page-section--light on-light',
	'muted' => 'page-section--muted on-light',
);
$tone  = (string) get_field( 'tts_models_tone' );
$tone  = $tones[ $tone ] ?? $tones['muted'];

// Поле таксономии в разных сборках отдаёт то число, то массив из одного
// значения — приводим к идентификатору термина сами.
$direction = get_field( 'tts_models_direction' );
$direction = is_array( $direction ) ? (int) reset( $direction ) : (int) $direction;

$args = array();
if ( $direction ) {
	$args['tax_query'] = array(
		array(
			'taxonomy' => 'direction',
			'field'    => 'term_id',
			'terms'    => $direction,
		),
	);
}
if ( $limit > 0 ) {
	$args['posts_per_page'] = $limit;
}

// Редактор может выбрать конкретные модели — тогда направление не при чём:
// на странице ВПИ, например, показывают три основные комплектации из семи.
$chosen = array_map( 'intval', (array) get_field( 'tts_models_chosen' ) );
$chosen = array_filter( $chosen );

if ( $chosen ) {
	$items = get_posts(
		array(
			'post_type'      => 'equipment',
			'post__in'       => $chosen,
			'orderby'        => 'post__in',
			'posts_per_page' => -1,
		)
	);
} else {
	$items = tts_items( 'equipment', $args );
}
$statuses = tts_equipment_status_labels();

/*
 * Подпись первого показателя в статике своя на каждой странице направления
 * (site/4/catalog/<направление>/index.html): у бетонных заводов «фактическая
 * производительность», у ВПИ «бетонной смеси», у терминалов «полезная
 * вместимость». Отдельного поля под неё нет — берём по назначению модели,
 * значения которого фиксированы списком в inc/fields.php.
 */
$spec_labels        = array(
	'dry-mix'        => 'производительность',
	'concrete'       => 'фактическая производительность',
	'vpi'            => 'бетонной смеси',
	'cement-storage' => 'полезная вместимость',
	'pneumo'         => 'производительность',
);
$spec_label_default = 'производительность';

// Кнопка «Подобрать» подставляет модель в форму заявки — скрипт нужен
// только на страницах, где этот блок стоит (п. 15.1 ТЗ).
if ( ! is_admin() ) {
	list( $pick_url, $pick_ver ) = tts_asset( 'js/model-pick.js' );
	wp_enqueue_script( 'tts-model-pick', $pick_url, array(), $pick_ver, array( 'strategy' => 'defer' ) );
}
?>
<section <?php echo tts_block_attrs( $block, 'page-section ' . $tone, 'models' ); // phpcs:ignore WordPress.Security.EscapeOutput ?>>
	<div class="shell">
		<?php tts_section_head( $kicker, $title, $lead ); ?>

		<?php if ( $items ) : ?>
		<div class="<?php echo esc_attr( trim( 'models ' . $grid ) ); ?>">
			<?php
			foreach ( $items as $item ) :
				$name   = (string) get_the_title( $item );
				$code   = (string) get_field( 'tts_equipment_code', $item->ID );
				$status = (string) get_field( 'tts_equipment_status', $item->ID );
				$photo  = (int) get_field( 'tts_equipment_photo', $item->ID );
				$render = $photo ? (string) wp_get_attachment_image_url( $photo, 'large' ) : '';

				// На странице направления у карточки свои тексты: название
				// комплектации и описание состава. Пусто — берём то, что
				// показывает общий каталог.
				$heading = (string) get_field( 'tts_equipment_title_long', $item->ID );
				$heading = $heading ?: $name;
				$text    = (string) get_field( 'tts_equipment_summary_long', $item->ID );
				$text    = $text ?: (string) get_field( 'tts_equipment_summary', $item->ID );

				// Два показателя вместо одного: первый — производительность
				// модели, второй задаётся в записи.
				$specs    = array();
				$capacity = (string) get_field( 'tts_equipment_capacity', $item->ID );
				$purpose  = (string) get_field( 'tts_equipment_purpose', $item->ID );
				if ( $capacity ) {
					$specs[] = array( $capacity, $spec_labels[ $purpose ] ?? $spec_label_default );
				}
				$spec2_value = (string) get_field( 'tts_equipment_spec2_value', $item->ID );
				$spec2_label = (string) get_field( 'tts_equipment_spec2_label', $item->ID );
				if ( $spec2_value || $spec2_label ) {
					$specs[] = array( $spec2_value, $spec2_label );
				}

				// Подпись кнопки: своя у модели («Подобрать Т10») или общая из блока.
				$button = (string) get_field( 'tts_equipment_cta', $item->ID );
				$button = $button ?: $cta;
				?>
			<article class="model">
				<?php if ( $render ) : ?>
				<?php
				/*
				 * Полоса с рендером. В статической версии картинка приходит
				 * в переменную --render списком из style.css, здесь — фото
				 * из медиатеки. Класс и его стили те же.
				 */
				?>
				<div class="model__media" style="--render:url('<?php echo esc_url( $render ); ?>')"></div>
				<?php endif; ?>

				<div class="model__top">
					<?php if ( $status ) : ?>
					<?php
					// На странице направления у карточки свой ярлык («Стартовая линия»),
					// и только если его не задали — показываем статус позиции.
					$tag = (string) get_field( 'tts_equipment_tag', $item->ID );
					$tag = $tag ?: ( $statuses[ $status ] ?? $status );
					?>
					<span class="model__tag"><?php echo esc_html( $tag ); ?></span>
					<?php endif; ?>
					<?php if ( $code ) : ?>
					<span class="model__code"><?php echo esc_html( $code ); ?></span>
					<?php endif; ?>
				</div>

				<h3 class="model__title"><?php echo esc_html( $heading ); ?></h3>

				<?php if ( $text ) : ?>
				<p class="model__text"><?php echo esc_html( $text ); ?></p>
				<?php endif; ?>

				<?php // Особенностей в карточке страницы направления нет — они в модальном окне каталога. ?>
				<?php if ( $specs ) : ?>
				<div class="model__specs">
					<?php foreach ( $specs as $spec ) : ?>
					<div>
						<strong class="model__value"><?php echo esc_html( $spec[0] ); ?></strong>
						<span class="model__label"><?php echo esc_html( $spec[1] ); ?></span>
					</div>
					<?php endforeach; ?>
				</div>
				<?php endif; ?>

				<?php if ( $button ) : ?>
				<?php // data-model подставляет модель в форму заявки (assets/js/model-pick.js). ?>
				<?php
				// В заявку уходит полное название комплектации, если оно задано:
				// инженер должен видеть модель вместе с прессом.
				$lead_name = (string) get_field( 'tts_equipment_lead_name', $item->ID );
				$lead_name = $lead_name ?: $heading;
				?>
				<a class="btn btn--solid model__link" href="#contact" data-model="<?php echo esc_attr( $lead_name ); ?>">
					<?php echo esc_html( $button ); ?>
					<?php echo tts_arrow(); // phpcs:ignore WordPress.Security.EscapeOutput ?>
				</a>
				<?php endif; ?>
			</article>
			<?php endforeach; ?>
		</div>
		<?php else : ?>
		<p class="doc__text">Моделей для этого направления пока нет. Добавьте их в разделе «Оборудование».</p>
		<?php endif; ?>
	</div>
</section>
