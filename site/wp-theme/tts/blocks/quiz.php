<?php
/**
 * Блок «Подбор оборудования» — встроенный квиз с главной (п. 7 ТЗ).
 *
 * Варианты ответов и рекомендации редактор ведёт в «Настройках сайта»,
 * в матрице подбора: строка матрицы = ответ про производительность плюс
 * основная и альтернативная модель из каталога. Поэтому здесь нет ни одного
 * названия модели — всё приходит из записей «Оборудование».
 *
 * Данные для скрипта печатаем рядом с секцией в JSON: так квиз работает
 * и на статической версии сайта, и в WordPress одним и тем же файлом.
 *
 * @var array $block
 */

defined( 'ABSPATH' ) || exit;

$kicker = (string) get_field( 'tts_quiz_kicker' );
$title  = (string) get_field( 'tts_quiz_title' );
$lead   = (string) get_field( 'tts_quiz_lead' );
$submit = (string) get_field( 'tts_quiz_submit' ) ?: 'Показать решение';
$stages = tts_rows( get_field( 'tts_quiz_stages' ) );
$cta    = (array) get_field( 'tts_quiz_cta' );

$labels = array(
	'object'   => (string) get_field( 'tts_quiz_label_object' ) ?: 'Тип объекта',
	'capacity' => (string) get_field( 'tts_quiz_label_capacity' ) ?: 'Производительность / хранение',
	'stage'    => (string) get_field( 'tts_quiz_label_stage' ) ?: 'Стадия проекта',
);

$config = tts_quiz_config();

// Скрипт квиза нужен только там, где стоит этот блок (п. 15.1 ТЗ).
if ( ! is_admin() ) {
	list( $url, $ver ) = tts_asset( 'js/quiz.js' );
	wp_enqueue_script( 'tts-quiz', $url, array(), $ver, array( 'strategy' => 'defer' ) );
}
?>
<section <?php echo tts_block_attrs( $block, 'quiz on-light', 'quiz' ); // phpcs:ignore WordPress.Security.EscapeOutput ?>>
	<div class="shell quiz__grid">
		<div>
			<?php if ( $kicker ) : ?>
			<p class="kicker"><?php echo esc_html( $kicker ); ?></p>
			<?php endif; ?>
			<?php if ( $title ) : ?>
			<h2 class="quiz__title"><?php echo esc_html( $title ); ?></h2>
			<?php endif; ?>
			<?php if ( $lead ) : ?>
			<p class="quiz__lead"><?php echo esc_html( $lead ); ?></p>
			<?php endif; ?>
		</div>

		<div class="quiz__fields">
			<label class="field">
				<span class="field__label"><?php echo esc_html( $labels['object'] ); ?></span>
				<select class="field__select" id="quizDirection">
					<?php foreach ( $config['directions'] as $key => $direction ) : ?>
					<option value="<?php echo esc_attr( $key ); ?>"<?php selected( (string) get_field( 'tts_quiz_default_object' ), $key ); ?>><?php echo esc_html( $direction['label'] ); ?></option>
					<?php endforeach; ?>
				</select>
			</label>

			<label class="field">
				<span class="field__label"><?php echo esc_html( $labels['capacity'] ); ?></span>
				<select class="field__select" id="quizCapacity"></select>
			</label>

			<label class="field">
				<span class="field__label"><?php echo esc_html( $labels['stage'] ); ?></span>
				<select class="field__select" id="quizStage">
					<?php foreach ( $stages as $stage ) : ?>
					<option<?php selected( ! empty( $stage['tts_quiz_stage_default'] ) ); ?>><?php echo esc_html( (string) ( $stage['tts_quiz_stage'] ?? '' ) ); ?></option>
					<?php endforeach; ?>
				</select>
			</label>

			<button class="btn btn--solid btn--lg quiz__submit" id="quizSubmit" type="button">
				<?php echo esc_html( $submit ); ?>
				<?php echo tts_arrow(); // phpcs:ignore WordPress.Security.EscapeOutput ?>
			</button>
		</div>
	</div>

	<div class="shell quiz__output" id="quizOutput" role="region" aria-live="polite" hidden>
		<div class="recommendation on-dark">
			<div>
				<p class="kicker"><?php echo esc_html( (string) get_field( 'tts_quiz_result_kicker' ) ?: 'Предварительная рекомендация' ); ?></p>
				<p class="recommendation__title" id="recTitle"></p>
				<p class="recommendation__text" id="recText"></p>
			</div>
			<div class="recommendation__meta">
				<span id="recCapacity"></span>
				<span id="recStage"></span>
				<a class="btn btn--solid recommendation__cta" href="<?php echo esc_url( (string) ( $cta['url'] ?? '#contact' ) ); ?>">
					<?php echo esc_html( (string) ( $cta['title'] ?? 'Получить инженерный расчёт' ) ); ?>
				</a>
			</div>
		</div>

		<div>
			<div class="matches__head">
				<h3 class="matches__title"><?php echo esc_html( (string) get_field( 'tts_quiz_matches_title' ) ?: 'Оборудование под выбранные параметры' ); ?></h3>
				<?php $note = (string) get_field( 'tts_quiz_matches_note' ); ?>
				<?php if ( $note ) : ?>
				<p class="matches__note"><?php echo esc_html( $note ); ?></p>
				<?php endif; ?>
			</div>
			<div class="matches__cards" id="quizCards"></div>
		</div>
	</div>

	<script type="application/json" id="tts-quiz-config">
		<?php echo wp_json_encode( $config, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES ); ?>
	</script>
</section>
