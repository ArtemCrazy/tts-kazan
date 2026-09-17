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
				$name     = (string) get_the_title( $item );
				$code     = (string) get_field( 'tts_equipment_code', $item->ID );
				$status   = (string) get_field( 'tts_equipment_status', $item->ID );
				$summary  = (string) get_field( 'tts_equipment_summary', $item->ID );
				$capacity = (string) get_field( 'tts_equipment_capacity', $item->ID );
				$features = tts_rows( get_field( 'tts_equipment_features', $item->ID ) );
				$photo    = (int) get_field( 'tts_equipment_photo', $item->ID );
				$render   = $photo ? (string) wp_get_attachment_image_url( $photo, 'large' ) : '';
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
					<span class="model__tag"><?php echo esc_html( $statuses[ $status ] ?? $status ); ?></span>
					<?php endif; ?>
					<?php if ( $code ) : ?>
					<span class="model__code"><?php echo esc_html( $code ); ?></span>
					<?php endif; ?>
				</div>

				<h3 class="model__title"><?php echo esc_html( $name ); ?></h3>

				<?php if ( $summary ) : ?>
				<p class="model__text"><?php echo esc_html( $summary ); ?></p>
				<?php endif; ?>

				<?php if ( $features ) : ?>
				<ul class="points">
					<?php foreach ( $features as $feature ) : ?>
						<?php $text = (string) ( $feature['tts_equipment_feature'] ?? '' ); ?>
						<?php if ( $text ) : ?>
					<li class="point"><p class="point__title"><?php echo esc_html( $text ); ?></p></li>
						<?php endif; ?>
					<?php endforeach; ?>
				</ul>
				<?php endif; ?>

				<?php if ( $capacity ) : ?>
				<div class="model__specs">
					<div>
						<strong class="model__value"><?php echo esc_html( $capacity ); ?></strong>
						<?php // Подпись показателя одна на весь сайт — та же, что в карточке общего каталога. ?>
						<span class="model__label">Производительность / вместимость</span>
					</div>
				</div>
				<?php endif; ?>

				<?php if ( $cta ) : ?>
				<?php // data-model подставляет модель в форму заявки (assets/js/model-pick.js). ?>
				<a class="btn btn--solid model__link" href="#contact" data-model="<?php echo esc_attr( $name ); ?>">
					<?php echo esc_html( $cta ); ?>
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
