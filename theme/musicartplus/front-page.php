<?php
/**
 * Главная страница.
 *
 * Секции лежат в template-parts/home/ — каждая проверяет свои данные и
 * не выводится, если контента нет.
 *
 * @package MusicArtPlus
 */

defined( 'ABSPATH' ) || exit;

get_header();

if ( have_posts() ) {
	the_post();
}

get_template_part( 'template-parts/home/hero' );
get_template_part( 'template-parts/home/about' );
get_template_part( 'template-parts/home/news' );
get_template_part( 'template-parts/home/interlude-notes' );
get_template_part( 'template-parts/home/directions' );
get_template_part( 'template-parts/home/teachers' );
get_template_part( 'template-parts/home/video' );
get_template_part( 'template-parts/home/interlude-brush' );
get_template_part( 'template-parts/home/reviews' );
get_template_part( 'template-parts/home/map' );
get_template_part( 'template-parts/home/booking' );

map_widget_section();

get_footer();
