<?php
/**
 * Блок «Проекты» (п. 6 ТЗ, блок 9).
 *
 * Сами проекты редактор ведёт в разделе «Проекты», блок только выбирает,
 * сколько карточек показать. Свои поля блока — шапка секции и кнопка.
 *
 * @var array $block      Данные блока от Secure Custom Fields.
 * @var bool  $is_preview Блок рисуется в редакторе, а не на сайте.
 */

defined( 'ABSPATH' ) || exit;

$kicker = (string) get_field( 'tts_projects_kicker' );
$title  = (string) get_field( 'tts_projects_title' );
$lead   = (string) get_field( 'tts_projects_lead' );
$count  = (int) get_field( 'tts_projects_count' );
$button = (array) get_field( 'tts_projects_button' );

$items = tts_items( 'project', $count > 0 ? array( 'posts_per_page' => $count ) : array() );

// Подписи «Задача» и «Решение» берём из самих полей записи (как колонки
// списков в inc/cpt.php): иначе они разъедутся с тем, что видит редактор.
$terms = array();
foreach ( array( 'tts_project_task', 'tts_project_solution' ) as $name ) {
	$field = function_exists( 'acf_get_field' ) ? acf_get_field( $name ) : null;
	$terms[ $name ] = (string) ( $field['label'] ?? '' );
}
?>
<section <?php echo tts_block_attrs( $block, 'projects', 'projects' ); // phpcs:ignore WordPress.Security.EscapeOutput ?>>
	<div class="shell">
		<?php tts_section_head( $kicker, $title, $lead ); ?>

		<?php if ( $items ) : ?>
		<div class="projects__grid">
			<?php
			foreach ( $items as $item ) :
				$city    = (string) get_field( 'tts_project_city', $item->ID );
				$photo   = (int) get_field( 'tts_project_photo', $item->ID );
				$figures = tts_rows( get_field( 'tts_project_figures', $item->ID ) );
				$src     = $photo ? (string) wp_get_attachment_image_url( $photo, 'large' ) : '';
				// Подпись для незрячих — «Альт. текст» снимка из медиатеки.
				$alt     = $photo ? trim( (string) get_post_meta( $photo, '_wp_attachment_image_alt', true ) ) : '';
				// Без описания снимок считается оформлением и для чтения вслух скрывается.
				$photo_attrs = $alt
					? ' role="img" aria-label="' . esc_attr( $alt ) . '"'
					: ' aria-hidden="true"';
				?>
			<article class="project">
				<div class="project__body">
					<?php if ( $src ) : ?>
					<?php // Снимок остаётся фоном контейнера: в вёрстке он обрезается по кадру карточки (background-size: cover). ?>
					<div class="project__photo" style="background-image:url(<?php echo esc_url( $src ); ?>)"<?php echo $photo_attrs; // phpcs:ignore WordPress.Security.EscapeOutput ?>></div>
					<?php endif; ?>

					<?php if ( $city ) : ?>
					<p class="project__meta"><?php echo esc_html( $city ); ?></p>
					<?php endif; ?>

					<h3 class="project__title"><?php echo esc_html( get_the_title( $item ) ); ?></h3>

					<?php
					$brief = array();
					foreach ( $terms as $name => $term ) {
						$value = (string) get_field( $name, $item->ID );
						if ( $term && $value ) {
							$brief[] = array( $term, $value );
						}
					}
					?>
					<?php if ( $brief ) : ?>
					<dl class="project__brief">
						<?php foreach ( $brief as list( $term, $value ) ) : ?>
						<dt class="project__term"><?php echo esc_html( $term ); ?></dt>
						<dd class="project__text"><?php echo esc_html( $value ); ?></dd>
						<?php endforeach; ?>
					</dl>
					<?php endif; ?>
				</div>

				<?php if ( $figures ) : ?>
				<div class="project__figures">
					<?php foreach ( $figures as $figure ) : ?>
					<div>
						<strong class="project__value"><?php echo esc_html( (string) ( $figure['tts_project_figure_value'] ?? '' ) ); ?></strong>
						<span class="project__label"><?php echo esc_html( (string) ( $figure['tts_project_figure_label'] ?? '' ) ); ?></span>
					</div>
					<?php endforeach; ?>
				</div>
				<?php endif; ?>
			</article>
			<?php endforeach; ?>
		</div>
		<?php elseif ( ! empty( $is_preview ) ) : ?>
		<?php // Пустую секцию посетитель не увидит, а редактору нужна подсказка. ?>
		<p class="doc__text">Проектов пока нет. Добавьте их в разделе «Проекты».</p>
		<?php endif; ?>

		<?php if ( ! empty( $button['title'] ) ) : ?>
		<a class="btn btn--solid btn--lg projects__link" href="<?php echo esc_url( (string) ( $button['url'] ?? '' ) ?: '#contact' ); ?>">
			<?php echo esc_html( (string) $button['title'] ); ?>
			<?php echo tts_arrow(); // phpcs:ignore WordPress.Security.EscapeOutput ?>
		</a>
		<?php endif; ?>
	</div>
</section>
