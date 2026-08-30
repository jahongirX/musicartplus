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
		'title' => map_field( 'about_hero_title', get_the_ID(), get_the_title() ),
		'text'  => map_field( 'about_hero_text', get_the_ID(), map_page_subtitle() ),
		'image' => map_image_url( map_field( 'about_hero_image', get_the_ID() ), 'map-hero' ),
	) );

	get_template_part( 'template-parts/about/intro' );
	get_template_part( 'template-parts/about/facts' );
	get_template_part( 'template-parts/about/steps' );
	get_template_part( 'template-parts/home/interlude-notes' );
	get_template_part( 'template-parts/about/mood' );
	get_template_part( 'template-parts/about/partner' );

	// Текст, введённый в редакторе, — если заказчик захочет дописать своё.
	if ( trim( get_the_content() ) ) :
		?>
		<section class="section article">
			<div class="container container--narrow">
				<div class="article__body"><?php the_content(); ?></div>
			</div>
		</section>
		<?php
	endif;

	get_template_part( 'template-parts/home/reviews' );
	map_widget_section();
	get_template_part( 'template-parts/home/map' );

	map_cta_band(
		map_field( 'about_cta_title', get_the_ID(), __( 'Приходите знакомиться', 'musicartplus' ) ),
		map_field( 'about_cta_text', get_the_ID(), __( 'Покажем центр, познакомим с педагогом и подберём программу под ребёнка.', 'musicartplus' ) ),
		'section--cream'
	);

endwhile;

get_footer();
