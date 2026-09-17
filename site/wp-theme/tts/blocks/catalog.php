<?php
/**
 * Блок «Общий каталог» (п. 8 ТЗ): табы направлений, поиск, два фильтра,
 * счётчик, сброс, состояние «ничего не найдено» и модальное окно позиции.
 *
 * Позиции приходят из раздела «Оборудование» — список печатаем рядом
 * с секцией в JSON, дальше их показывает скрипт каталога.
 *
 * @var array $block
 */

defined( 'ABSPATH' ) || exit;

$title  = (string) get_field( 'tts_catalog_title' );
$lead   = (string) get_field( 'tts_catalog_lead' );
$all    = (string) get_field( 'tts_catalog_all_label' ) ?: 'Всё оборудование';
$config = tts_catalog_config();

$purposes = tts_field_choices( 'tts_equipment_purpose' );
$scales   = tts_field_choices( 'tts_equipment_scale' );

if ( ! is_admin() ) {
	list( $css, $css_ver ) = tts_asset( 'css/catalog.css' );
	wp_enqueue_style( 'tts-catalog', $css, array(), $css_ver );
	list( $js, $js_ver ) = tts_asset( 'js/catalog.js' );
	wp_enqueue_script( 'tts-catalog', $js, array(), $js_ver, array( 'strategy' => 'defer' ) );
}
?>
<section <?php echo tts_block_attrs( $block, 'catalog on-light', 'catalog' ); // phpcs:ignore WordPress.Security.EscapeOutput ?>>
	<div class="shell">
		<?php if ( $title || $lead ) : ?>
		<div class="section-head catalog__intro">
			<?php if ( $title ) : ?>
			<h2 class="section-head__title"><?php echo esc_html( $title ); ?></h2>
			<?php endif; ?>
			<?php if ( $lead ) : ?>
			<p class="section-head__lead"><?php echo esc_html( $lead ); ?></p>
			<?php endif; ?>
		</div>
		<?php endif; ?>

		<div class="tabs" role="group" aria-label="Категории оборудования">
			<button class="tab" type="button" data-category="all" aria-pressed="true"><?php echo esc_html( $all ); ?></button>
			<?php foreach ( $config['labels'] as $slug => $label ) : ?>
			<button class="tab" type="button" data-category="<?php echo esc_attr( $slug ); ?>" aria-pressed="false"><?php echo esc_html( $label ); ?></button>
			<?php endforeach; ?>
		</div>

		<div class="filters">
			<label class="field filters__field filters__field--search">
				<span class="field__label">Поиск</span>
				<input class="field__input" id="catalogSearch" type="search" name="q"
					placeholder="Название, направление, производительность" autocomplete="off">
			</label>

			<label class="field filters__field">
				<span class="field__label">Назначение</span>
				<select class="field__select" id="purposeFilter">
					<option value="all">Любое</option>
					<?php foreach ( $purposes as $value => $label ) : ?>
					<option value="<?php echo esc_attr( $value ); ?>"><?php echo esc_html( $label ); ?></option>
					<?php endforeach; ?>
				</select>
			</label>

			<label class="field filters__field">
				<span class="field__label">Масштаб</span>
				<select class="field__select" id="scaleFilter">
					<option value="all">Любой</option>
					<?php foreach ( $scales as $value => $label ) : ?>
					<option value="<?php echo esc_attr( $value ); ?>"><?php echo esc_html( $label ); ?></option>
					<?php endforeach; ?>
				</select>
			</label>
		</div>

		<div class="results">
			<p class="results__count" id="resultCount" role="status" aria-live="polite">Найдено позиций: <?php echo count( $config['items'] ); ?></p>
			<button class="results__reset" type="button" id="resetFilters">Сбросить фильтры</button>
		</div>

		<div class="catalog__grid" id="catalogGrid"></div>

		<div class="empty" id="emptyState" hidden>
			<h3 class="empty__title">Ничего не найдено</h3>
			<p class="empty__text">Измените запрос или сбросьте фильтры — инженер также может подобрать нестандартную конфигурацию.</p>
			<button class="btn btn--solid" type="button" id="emptyReset">Показать всё оборудование</button>
		</div>
	</div>

	<script type="application/json" id="tts-catalog-config">
		<?php echo wp_json_encode( $config, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES ); ?>
	</script>
</section>

<div class="modal" id="productModal" role="dialog" aria-modal="true" aria-labelledby="modalTitle" hidden>
	<div class="modal__dialog">
		<button class="modal__close" type="button" id="modalClose" aria-label="Закрыть описание">
			<svg viewBox="0 0 24 24" width="20" height="20" aria-hidden="true" focusable="false">
				<path d="M6 6l12 12M18 6L6 18" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="square"></path>
			</svg>
		</button>

		<div class="modal__media" data-modal="media">
			<span class="product__status" data-modal="status"></span>
			<span class="product__code" data-modal="code"></span>
		</div>

		<div class="modal__body">
			<p class="product__category" data-modal="category"></p>
			<h2 class="modal__title" id="modalTitle" data-modal="title"></h2>
			<p class="modal__text" data-modal="text"></p>

			<div class="product__capacity">
				<span>Производительность / вместимость</span>
				<strong data-modal="capacity"></strong>
			</div>

			<h3 class="modal__subtitle">Ключевые особенности</h3>
			<ul class="modal__features" data-modal="features"></ul>

			<div class="modal__actions">
				<button class="btn btn--solid" type="button" data-modal="quote" data-quote="">
					Получить КП
					<?php echo tts_arrow(); // phpcs:ignore WordPress.Security.EscapeOutput ?>
				</button>
				<a class="link-arrow" data-modal="page" href="<?php echo esc_url( tts_url( 'catalog' ) ); ?>">
					Страница направления
					<svg viewBox="0 0 24 24" width="18" height="18" aria-hidden="true" focusable="false">
						<path d="M4 12h15M13 6l6 6-6 6" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="square"></path>
					</svg>
				</a>
			</div>
		</div>
	</div>
</div>
