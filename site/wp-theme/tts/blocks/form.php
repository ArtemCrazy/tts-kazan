<?php
/**
 * Блок «Форма заявки» (п. 11 ТЗ).
 *
 * Разметка повторяет статическую версию. Отправка идёт на адрес
 * /wp-json/tts/v1/lead: там серверная проверка, сохранение в разделе
 * «Заявки» и письмо получателю (см. inc/lead.php).
 *
 * @var array $block
 */

defined( 'ABSPATH' ) || exit;

$kicker     = (string) get_field( 'tts_form_kicker' );
$title      = (string) get_field( 'tts_form_title' );
$lead       = (string) get_field( 'tts_form_lead' );
$office     = (string) get_field( 'tts_form_office' );
$details    = tts_rows( get_field( 'tts_form_details' ) );
$card_title = (string) get_field( 'tts_form_card_title' );
$note       = (string) get_field( 'tts_form_note' );
$directions = tts_rows( get_field( 'tts_form_directions' ) );
$comment    = (string) get_field( 'tts_form_comment_label' ) ?: 'Комментарий';
$hint       = (string) get_field( 'tts_form_comment_hint' ) ?: 'Кратко опишите задачу';
$submit     = (string) get_field( 'tts_form_submit' ) ?: 'Получить консультацию';
$done_title = (string) get_field( 'tts_form_done_title' ) ?: 'Заявка отправлена';
$done_text  = (string) get_field( 'tts_form_done_text' );
$again      = (string) get_field( 'tts_form_again' ) ?: 'Заполнить ещё раз';
$source     = (string) get_field( 'tts_form_source' ) ?: 'general';
$second     = (string) get_field( 'tts_form_second_label' );
$seconds    = tts_rows( get_field( 'tts_form_second_options' ) );
$picked     = (bool) get_field( 'tts_form_picked' );

// Список моделей направления: в статике это первое поле формы на странице
// направления, и кнопка «Подобрать» в карточке выбирает в нём модель.
$models_label = (string) get_field( 'tts_form_models_label' );
$models       = array();
$models_term  = get_field( 'tts_form_models_direction' );
$models_term  = is_array( $models_term ) ? (int) reset( $models_term ) : (int) $models_term;
if ( $models_label && $models_term ) {
	foreach ( tts_items( 'equipment', array( 'tax_query' => array( array( 'taxonomy' => 'direction', 'terms' => $models_term ) ) ) ) as $item ) {
		$lead_name = (string) get_field( 'tts_equipment_lead_name', $item->ID );
		if ( $lead_name ) {
			$models[] = $lead_name;
		}
	}
}

if ( ! is_admin() ) {
	list( $url, $ver ) = tts_asset( 'js/form.js' );
	wp_enqueue_script( 'tts-form', $url, array(), $ver, array( 'strategy' => 'defer' ) );
}
?>
<section <?php echo tts_block_attrs( $block, 'contact photo-bed', 'contact' ); // phpcs:ignore WordPress.Security.EscapeOutput ?>>
	<div class="shell contact__grid">
		<div class="contact__copy">
			<?php if ( $kicker ) : ?>
			<p class="kicker"><?php echo esc_html( $kicker ); ?></p>
			<?php endif; ?>
			<?php if ( $title ) : ?>
			<?php // Перенос строки в заголовке — только на широком экране, как в статике (br-wide). ?>
			<h2 class="contact__title"><?php echo implode( '<br class="br-wide"> ', array_map( 'esc_html', preg_split( '/\R+/u', trim( $title ) ) ) ); // phpcs:ignore WordPress.Security.EscapeOutput ?></h2>
			<?php endif; ?>
			<?php if ( $lead ) : ?>
			<p class="contact__lead"><?php echo esc_html( $lead ); ?></p>
			<?php endif; ?>

			<?php if ( $office || $details ) : ?>
			<div class="contact__details">
				<?php if ( $office ) : ?>
				<strong class="contact__office"><?php echo esc_html( $office ); ?></strong>
				<?php endif; ?>
				<?php foreach ( $details as $line ) : ?>
				<span><?php echo esc_html( (string) ( $line['tts_form_detail'] ?? '' ) ); ?></span>
				<?php endforeach; ?>
			</div>
			<?php endif; ?>
		</div>

		<div class="form-card">
			<form class="form" id="leadForm" novalidate
				data-endpoint="<?php echo esc_url( rest_url( 'tts/v1/lead' ) ); ?>"
				data-source="<?php echo esc_attr( $source ); ?>">
				<?php if ( $card_title ) : ?>
				<h3 class="form__title"><?php echo esc_html( $card_title ); ?></h3>
				<?php endif; ?>
				<?php if ( $note ) : ?>
				<p class="form__note"><?php echo esc_html( $note ); ?></p>
				<?php endif; ?>

				<?php if ( $models ) : ?>
				<label class="field">
					<span class="field__label"><?php echo esc_html( $models_label ); ?></span>
					<select class="field__select" id="leadModel" name="model">
						<option value="Требуется подобрать" selected>Требуется подобрать</option>
						<?php foreach ( $models as $model ) : ?>
						<option value="<?php echo esc_attr( $model ); ?>"><?php echo esc_html( $model ); ?></option>
						<?php endforeach; ?>
					</select>
				</label>
				<?php endif; ?>

				<?php if ( $directions ) : ?>
				<label class="field">
					<span class="field__label"><?php echo esc_html( (string) get_field( 'tts_form_direction_label' ) ?: 'Направление' ); ?></span>
					<select class="field__select" name="direction">
						<?php foreach ( $directions as $item ) : ?>
						<option value="<?php echo esc_attr( (string) ( $item['tts_form_direction'] ?? '' ) ); ?>"<?php selected( ! empty( $item['tts_form_direction_default'] ) ); ?>><?php echo esc_html( (string) ( $item['tts_form_direction'] ?? '' ) ); ?></option>
						<?php endforeach; ?>
					</select>
				</label>
				<?php endif; ?>

				<?php if ( $picked ) : ?>
				<?php // Сюда каталог подставляет выбранную модель (п. 8.3 ТЗ) ?>
				<label class="field">
					<span class="field__label">Выбранное оборудование</span>
					<input class="field__input" id="leadPicked" name="model" type="text" placeholder="Подбор по задаче" readonly>
				</label>
				<?php endif; ?>

				<?php if ( $second && $seconds ) : ?>
				<label class="field">
					<span class="field__label"><?php echo esc_html( $second ); ?></span>
					<select class="field__select" name="urgency">
						<?php foreach ( $seconds as $item ) : ?>
						<option value="<?php echo esc_attr( (string) ( $item['tts_form_second_option'] ?? '' ) ); ?>"<?php selected( ! empty( $item['tts_form_second_option_default'] ) ); ?>><?php echo esc_html( (string) ( $item['tts_form_second_option'] ?? '' ) ); ?></option>
						<?php endforeach; ?>
					</select>
				</label>
				<?php endif; ?>

				<label class="field">
					<span class="field__label">Ваше имя</span>
					<input class="field__input" id="leadName" name="name" type="text" autocomplete="name" placeholder="Имя Фамилия" required>
					<span class="field__error" data-error></span>
				</label>

				<label class="field">
					<span class="field__label">Телефон для связи</span>
					<input class="field__input" name="phone" type="tel" autocomplete="tel" placeholder="Телефон" required>
					<span class="field__error" data-error></span>
				</label>

				<label class="field">
					<span class="field__label"><?php echo esc_html( $comment ); ?></span>
					<textarea class="field__input field__textarea" name="comment" rows="3" placeholder="<?php echo esc_attr( $hint ); ?>"></textarea>
				</label>

				<label class="consent">
					<input class="consent__box" name="consent" type="checkbox" required>
					<span class="consent__text">Я согласен на <a class="consent__link" href="<?php echo esc_url( tts_url( 'personal' ) ); ?>">обработку персональных данных</a></span>
					<span class="field__error" data-error></span>
				</label>

				<?php if ( $picked ) : ?>
				<?php // Служебные поля каталога: направление и применённые фильтры ?>
				<input type="hidden" id="leadDirection" name="direction" value="">
				<input type="hidden" id="leadFilters" name="filters" value="">
				<?php endif; ?>

				<?php // Поле-ловушка для роботов: людям оно не видно и не нужно. ?>
				<div class="visually-hidden" aria-hidden="true">
					<label>Организация<input name="company" type="text" tabindex="-1" autocomplete="off"></label>
				</div>

				<button class="btn btn--solid btn--lg form__submit" type="submit">
					<?php echo esc_html( $submit ); ?>
					<?php echo tts_arrow(); // phpcs:ignore WordPress.Security.EscapeOutput ?>
				</button>
				<p class="field__error" data-error-form></p>
			</form>

			<div class="form-done" id="leadDone" tabindex="-1" hidden>
				<span class="form-done__mark" aria-hidden="true">
					<svg viewBox="0 0 24 24" width="22" height="22" focusable="false">
						<path d="M4 12l5 5L20 6" fill="none" stroke="currentColor" stroke-width="2.4" stroke-linecap="square"></path>
					</svg>
				</span>
				<h3 class="form-done__title"><?php echo esc_html( $done_title ); ?></h3>
				<?php if ( $done_text ) : ?>
				<p class="form-done__text"><?php echo esc_html( $done_text ); ?></p>
				<?php endif; ?>
				<button class="btn btn--ghost" type="button" id="leadAgain"><?php echo esc_html( $again ); ?></button>
			</div>
		</div>
	</div>
</section>
