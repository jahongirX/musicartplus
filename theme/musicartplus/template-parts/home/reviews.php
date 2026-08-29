<?php
/**
 * Блок отзывов.
 *
 * @package MusicArtPlus
 */

defined( 'ABSPATH' ) || exit;

$map_reviews = map_get_items( 'map_review' );

if ( ! $map_reviews ) {
	return;
}
?>
<section class="section" id="reviews">
	<?php map_deco( 'star' ); ?>
	<div class="container">
		<div class="sec-head">
			<div class="sec-head__text">
				<span class="eyebrow"><?php echo esc_html( map_home_field( 'reviews_eyebrow', 'Отзывы' ) ); ?></span>
				<h2 class="h2"><?php echo esc_html( map_home_field( 'reviews_title', 'Что говорят родители и ученики' ) ); ?></h2>
			</div>
			<?php map_slider_nav( 'reviews-home' ); ?>
		</div>

		<?php map_slider_open( 'reviews-home' ); ?>
			<?php foreach ( $map_reviews as $map_review ) : ?>
				<div class="swiper-slide"><?php map_review_card( $map_review, 0, false ); ?></div>
			<?php endforeach; ?>
		<?php map_slider_close(); ?>
	</div>
</section>
