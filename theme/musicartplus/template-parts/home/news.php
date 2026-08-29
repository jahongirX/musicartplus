<?php
/**
 * Блок новостей на главной.
 *
 * @package MusicArtPlus
 */

defined( 'ABSPATH' ) || exit;

$map_news = get_posts( array(
	'posts_per_page' => 6,
	'no_found_rows'  => true,
) );

if ( ! $map_news ) {
	return;
}

$map_news_page = (int) get_option( 'page_for_posts' );
?>
<section class="section section--cream" id="news">
	<?php map_deco( 'note' ); ?>
	<div class="container">
		<div class="sec-head">
			<div class="sec-head__text">
				<span class="eyebrow"><?php esc_html_e( 'Новости', 'musicartplus' ); ?></span>
				<h2 class="h2"><?php esc_html_e( 'Чем живёт центр искусств', 'musicartplus' ); ?></h2>
				<p class="sec-head__desc"><?php esc_html_e( 'Концерты, мастер-классы, выставки и маленькие победы наших учеников.', 'musicartplus' ); ?></p>
			</div>
			<?php map_slider_nav( 'news' ); ?>
		</div>

		<?php map_slider_open( 'news' ); ?>
			<?php foreach ( $map_news as $map_post ) : ?>
				<div class="swiper-slide"><?php map_news_card( $map_post, 0, false ); ?></div>
			<?php endforeach; ?>
		<?php map_slider_close(); ?>

		<?php if ( $map_news_page ) : ?>
			<div class="flex-center mt-l">
				<a class="btn btn--ghost" href="<?php echo esc_url( get_permalink( $map_news_page ) ); ?>"><?php esc_html_e( 'Все новости', 'musicartplus' ); ?><?php map_the_icon( 'ar', 'btn__ico' ); ?></a>
			</div>
		<?php endif; ?>
	</div>
</section>
