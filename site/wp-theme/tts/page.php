<?php
/**
 * Обычная страница.
 *
 * Страницы бывают двух видов:
 *  - собранные из блоков-секций (каталог, направления, сервис): у них шапка
 *    раздела — это блок «Шапка раздела», и шаблон только выводит содержимое;
 *  - текстовые (юридические документы): шапку рисует шаблон, а содержимое
 *    редактируется обычными блоками Gutenberg внутри колонки документа.
 *
 * Разделение нужно, чтобы H1 на странице был один (п. 14 ТЗ).
 */

defined( 'ABSPATH' ) || exit;

get_header();

while ( have_posts() ) :
	the_post();

	$own_head = has_block( 'acf/tts-page-head', get_post() ) || has_block( 'acf/tts-hero', get_post() );

	if ( $own_head ) {
		the_content();
	} else {
		tts_page_head(
			array(
				'crumb'  => get_the_title(),
				'kicker' => get_post_meta( get_the_ID(), 'tts_kicker', true ),
				'title'  => get_the_title(),
				'lead'   => tts_page_lead(),
			)
		);
		?>
		<section class="page-section page-section--light on-light">
			<div class="shell">
				<div class="doc">
					<?php the_content(); ?>
				</div>
			</div>
		</section>
		<?php
	}
endwhile;

get_footer();
