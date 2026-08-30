<?php
/**
 * Блок «Связаться с нами» с формой записи.
 *
 * @package MusicArtPlus
 */

defined( 'ABSPATH' ) || exit;

// Цитату можно задать своей строкой; иначе берём начало свежего отзыва.
$map_quote_text   = (string) map_home_field( 'cta_quote' );
$map_quote_author = (string) map_home_field( 'cta_quote_author' );
$map_quote        = $map_quote_text ? array() : map_get_items( 'map_review', 1 );

if ( ! $map_quote_text && $map_quote ) {
	$map_review_post  = $map_quote[0];
	$map_lines        = map_lines( wp_strip_all_tags( $map_review_post->post_content ) );
	$map_quote_text   = isset( $map_lines[0] ) ? $map_lines[0] : '';
	$map_quote_author = trim( map_field( 'review_author', $map_review_post->ID, $map_review_post->post_title ) . ', ' . map_field( 'review_role', $map_review_post->ID ), ', ' );
}
?>
<section class="section" id="booking">
	<?php map_deco( 'note' ); ?>
	<div class="container">
		<div class="contact-grid">
			<div class="reveal reveal--left">
				<span class="eyebrow"><?php echo esc_html( map_home_field( 'cta_eyebrow', 'Связаться с нами' ) ); ?></span>
				<h2 class="h2"><?php echo esc_html( map_field( 'cta_title', get_the_ID(), __( 'Первый урок — чтобы просто попробовать', 'musicartplus' ) ) ); ?></h2>
				<p class="lead" style="margin-top:18px"><?php echo esc_html( map_field( 'cta_text', get_the_ID(), __( 'Оставьте имя и телефон: мы перезвоним, расспросим о ребёнке и подберём педагога и удобное время. Пробное занятие ни к чему не обязывает.', 'musicartplus' ) ) ); ?></p>

				<?php if ( $map_quote_text ) : ?>
					<div class="quote" style="margin-top:28px">
						<span class="quote__mark" aria-hidden="true">&ldquo;</span>
						<p><?php echo esc_html( $map_quote_text ); ?></p>
						<footer><?php echo esc_html( $map_quote_author ); ?></footer>
					</div>
				<?php endif; ?>
			</div>

			<?php map_form_card(
				__( 'Записаться на пробный урок', 'musicartplus' ),
				__( 'Заполните два поля — остальное мы уточним по телефону.', 'musicartplus' ),
				'form-home'
			); ?>
		</div>
	</div>
</section>
