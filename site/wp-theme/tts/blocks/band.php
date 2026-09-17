<?php
/**
 * Блок «Плашка с кнопкой» — короткий переход на другой раздел.
 *
 * Разметка повторяет функцию band() из tools/pagekit.py: .band с заголовком,
 * текстом и кнопкой со стрелкой. Кнопка без модификатора размера — так же,
 * как в статической вёрстке, поэтому собираем её здесь, а не через tts_button().
 *
 * @var array $block Данные блока от Secure Custom Fields.
 */

defined( 'ABSPATH' ) || exit;

$title  = (string) get_field( 'tts_band_title' );
$text   = (string) get_field( 'tts_band_text' );
$button = (array) get_field( 'tts_band_button' );
$label  = (string) ( $button['title'] ?? '' );
$url    = (string) ( $button['url'] ?? '' );
$blank  = '_blank' === (string) ( $button['target'] ?? '' );

// Классы тона секции — как в pagekit.section(). В статической вёрстке плашка
// всегда тёмная, поэтому это значение по умолчанию.
$tones = array(
	'light' => 'page-section--light on-light',
	'muted' => 'page-section--muted on-light',
	'dark'  => 'page-section--dark',
);
$tone  = (string) get_field( 'tts_band_tone' );
$tone  = $tones[ $tone ] ?? $tones['dark'];
?>
<section <?php echo tts_block_attrs( $block, 'page-section ' . $tone ); // phpcs:ignore WordPress.Security.EscapeOutput ?>>
	<div class="shell">
		<div class="band">
			<div>
				<?php if ( $title ) : ?>
				<strong class="band__title"><?php echo esc_html( $title ); ?></strong>
				<?php endif; ?>
				<?php if ( $text ) : ?>
				<p class="band__text"><?php echo esc_html( $text ); ?></p>
				<?php endif; ?>
			</div>
			<?php if ( $label && $url ) : ?>
			<a class="btn btn--solid" href="<?php echo esc_url( $url ); ?>"<?php echo $blank ? ' target="_blank" rel="noopener"' : ''; ?>>
				<?php echo esc_html( $label ); ?>
				<?php echo tts_arrow(); // phpcs:ignore WordPress.Security.EscapeOutput ?>
			</a>
			<?php endif; ?>
		</div>
	</div>
</section>
