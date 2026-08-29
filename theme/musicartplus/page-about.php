<?php
/**
 * Template Name: О нас
 *
 * @package MusicArtPlus
 */

defined( 'ABSPATH' ) || exit;

get_header();

while ( have_posts() ) :
	the_post();

	get_template_part( 'template-parts/page/hero', null, array(
		'crumb' => get_the_title(),
		'title' => get_the_title(),
		'text'  => map_page_subtitle(),
	) );
	?>

	<section class="section article">
		<div class="container container--narrow">
			<div class="article__body"><?php the_content(); ?></div>
		</div>
	</section>

	<?php
	get_template_part( 'template-parts/home/reviews' );
	map_widget_section();
	get_template_part( 'template-parts/home/map' );

	map_cta_band(
		map_field( 'about_cta_title', get_the_ID(), __( 'Приходите знакомиться', 'musicartplus' ) ),
		map_field( 'about_cta_text', get_the_ID(), __( 'Покажем центр, познакомим с педагогом и подберём программу под ребёнка.', 'musicartplus' ) )
	);

endwhile;

get_footer();
