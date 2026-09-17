<?php
/**
 * Обычная страница: шапка раздела и содержимое из редактора.
 *
 * Юридические страницы и другие текстовые разделы редактируются штатно
 * в Gutenberg — в шаблоне текста нет (п. 12.1 ТЗ).
 */

defined( 'ABSPATH' ) || exit;

get_header();

while ( have_posts() ) :
	the_post();

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
endwhile;

get_footer();
