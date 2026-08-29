<?php
/**
 * Обычная страница.
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
endwhile;

get_footer();
