<?php
/**
 * Блок отзывов.
 *
 * @package MusicArtPlus
 *
 * @var array $args eyebrow, title, name, class, mode, limit, link_url, link_label.
 */

defined( 'ABSPATH' ) || exit;

$map_reviews = map_get_items( 'map_review' );

if ( ! $map_reviews ) {
	return;
}

// Заголовки можно переопределить: на странице педагогов у блока свой текст.
$map_a = wp_parse_args( $args, array(
	'eyebrow'    => map_home_field( 'reviews_eyebrow', 'Отзывы' ),
	'title'      => map_home_field( 'reviews_title', 'Что говорят родители и ученики' ),
	'name'       => 'reviews-home',
	'class'      => '',
	// grid — короткая выборка с ссылкой «все отзывы», slider — вся лента.
	'mode'       => 'slider',
	'limit'      => 0,
	'link_url'   => '',
	'link_label' => '',
) );

if ( $map_a['limit'] ) {
	$map_reviews = array_slice( $map_reviews, 0, (int) $map_a['limit'] );
}

$map_grid = 'grid' === $map_a['mode'];
?>
<section class="section<?php echo $map_a['class'] ? ' ' . esc_attr( $map_a['class'] ) : ''; ?>" id="reviews">
	<?php map_deco( 'star' ); ?>
	<div class="container">
		<div class="sec-head">
			<div class="sec-head__text">
				<span class="eyebrow"><?php echo esc_html( $map_a['eyebrow'] ); ?></span>
				<h2 class="h2"><?php echo esc_html( $map_a['title'] ); ?></h2>
			</div>
			<?php if ( $map_grid ) : ?>
				<?php if ( $map_a['link_url'] ) : ?>
					<a class="link-arrow" href="<?php echo esc_url( $map_a['link_url'] ); ?>"><?php echo esc_html( $map_a['link_label'] ); ?><?php map_the_icon( 'ar' ); ?></a>
				<?php endif; ?>
			<?php else : ?>
				<?php map_slider_nav( $map_a['name'] ); ?>
			<?php endif; ?>
		</div>

		<?php if ( $map_grid ) : ?>
			<div class="grid g-3">
				<?php foreach ( $map_reviews as $map_i => $map_review ) : ?>
					<?php map_review_card( $map_review, $map_i % 3 ); ?>
				<?php endforeach; ?>
			</div>
		<?php else : ?>
			<?php map_slider_open( $map_a['name'] ); ?>
				<?php foreach ( $map_reviews as $map_review ) : ?>
					<div class="swiper-slide"><?php map_review_card( $map_review, 0, false ); ?></div>
				<?php endforeach; ?>
			<?php map_slider_close(); ?>
		<?php endif; ?>
	</div>
</section>
