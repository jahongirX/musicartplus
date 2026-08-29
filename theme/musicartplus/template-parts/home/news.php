<?php
/**
 * Блок новостей на главной.
 *
 * @package MusicArtPlus
 */

defined( 'ABSPATH' ) || exit;

$map_news = get_posts( array(
	'posts_per_page' => max( 1, (int) map_home_field( 'news_count', 6 ) ),
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
				<span class="eyebrow"><?php echo esc_html( map_home_field( 'news_eyebrow', 'Новости' ) ); ?></span>
				<h2 class="h2"><?php echo esc_html( map_home_field( 'news_title', 'Чем живёт центр искусств' ) ); ?></h2>
				<p class="sec-head__desc"><?php echo esc_html( map_home_field( 'news_text', 'Концерты, мастер-классы, выставки и маленькие победы наших учеников.' ) ); ?></p>
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
				<a class="btn btn--ghost" href="<?php echo esc_url( get_permalink( $map_news_page ) ); ?>"><?php echo esc_html( map_home_field( 'news_btn_text', 'Все новости' ) ); ?><?php map_the_icon( 'ar', 'btn__ico' ); ?></a>
			</div>
		<?php endif; ?>
	</div>
</section>
