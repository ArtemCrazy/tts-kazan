<?php
/**
 * Запасной шаблон: WordPress берёт его, когда нет более подходящего
 * (например, для поиска). Оформление то же, что у остальных страниц.
 */

defined( 'ABSPATH' ) || exit;

get_header();

tts_page_head(
	array(
		'crumb'  => is_search() ? 'Поиск' : 'Материалы',
		'kicker' => 'Сайт',
		'title'  => is_search() ? 'Результаты поиска' : 'Материалы',
		'lead'   => is_search()
			? sprintf( 'Запрос: «%s».', get_search_query() )
			: '',
	)
);
?>
<section class="page-section page-section--light on-light">
	<div class="shell">
		<?php if ( have_posts() ) : ?>
		<div class="cards cards--3">
			<?php
			while ( have_posts() ) :
				the_post();
				?>
			<a class="card card--link" href="<?php the_permalink(); ?>">
				<h2 class="card__title"><?php the_title(); ?></h2>
				<p class="card__text"><?php echo esc_html( wp_trim_words( get_the_excerpt(), 20 ) ); ?></p>
			</a>
			<?php endwhile; ?>
		</div>
		<?php else : ?>
		<p class="doc__text">Ничего не найдено. Попробуйте другой запрос или откройте
			<a href="<?php echo esc_url( tts_url( 'catalog' ) ); ?>">каталог оборудования</a>.</p>
		<?php endif; ?>
	</div>
</section>
<?php
get_footer();
