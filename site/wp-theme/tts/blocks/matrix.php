<?php
/**
 * Блок «Таблица сравнения» — сравнение конфигураций оборудования
 * (tools/pagekit.py, функция matrix).
 *
 * Первый столбец таблицы — заголовки строк (th scope="row"), остальные
 * столбцы заполняются ячейками. Каждой ячейке подставляется data-label
 * с заголовком её столбца: на телефоне шапка таблицы скрыта и подпись
 * берётся оттуда.
 *
 * @var array $block
 */

defined( 'ABSPATH' ) || exit;

$kicker  = (string) get_field( 'tts_matrix_kicker' );
$title   = (string) get_field( 'tts_matrix_title' );
$lead    = (string) get_field( 'tts_matrix_lead' );
$columns = tts_rows( get_field( 'tts_matrix_columns' ) );
$rows    = tts_rows( get_field( 'tts_matrix_rows' ) );
$note    = (string) get_field( 'tts_matrix_note' );

// Заголовки столбцов одним плоским списком: из него же берутся подписи ячеек.
$heads = array();
foreach ( $columns as $column ) {
	$head = trim( (string) ( $column['tts_matrix_column'] ?? '' ) );
	if ( '' !== $head ) {
		$heads[] = $head;
	}
}

// Классы тона секции — как в pagekit.section(). Тёмного тона здесь нет:
// таблица сравнения в вёрстке всегда на светлой поверхности.
$tones = array(
	'light' => 'page-section--light on-light',
	'muted' => 'page-section--muted on-light',
);
$tone  = (string) get_field( 'tts_matrix_tone' );
$tone  = $tones[ $tone ] ?? $tones['light'];

// В статике на страницах ВПИ и ПКН таблица стоит внутри секции с карточками,
// а не отдельной секцией: отступы между ними — как между элементами одной секции.
if ( get_field( 'tts_matrix_continue' ) ) {
	$tone .= ' page-section--continue';
}
?>
<section <?php echo tts_block_attrs( $block, 'page-section ' . $tone, 'compare' ); // phpcs:ignore WordPress.Security.EscapeOutput ?>>
	<div class="shell">
		<?php tts_section_head( $kicker, $title, $lead ); ?>

		<?php if ( $heads && $rows ) : ?>
		<div class="matrix__wrap">
			<table class="matrix">
				<?php // Подпись для экранных читалок: видимый заголовок секции — это h2 выше. ?>
				<caption class="visually-hidden"><?php echo esc_html( $title ?: 'Сравнение конфигураций' ); ?></caption>
				<thead>
					<tr>
						<?php foreach ( $heads as $head ) : ?>
						<th scope="col"><?php echo esc_html( $head ); ?></th>
						<?php endforeach; ?>
					</tr>
				</thead>
				<tbody>
					<?php
					foreach ( $rows as $row ) :
						$label = (string) ( $row['tts_matrix_row_label'] ?? '' );
						$cells = tts_rows( $row['tts_matrix_cells'] ?? array() );
						?>
					<tr>
						<th scope="row"><?php echo esc_html( $label ); ?></th>
						<?php foreach ( $cells as $index => $cell ) : ?>
						<td data-label="<?php echo esc_attr( $heads[ $index + 1 ] ?? '' ); ?>"><?php echo esc_html( (string) ( $cell['tts_matrix_cell'] ?? '' ) ); ?></td>
						<?php endforeach; ?>
					</tr>
					<?php endforeach; ?>
				</tbody>
			</table>
		</div>
		<?php endif; ?>

		<?php if ( $note ) : ?>
		<p class="matrix__note"><?php echo esc_html( $note ); ?></p>
		<?php endif; ?>
	</div>
</section>
