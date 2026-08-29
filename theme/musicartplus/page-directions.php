<?php
/**
 * Template Name: Наши направления
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

	$map_dirs = map_get_items( 'map_direction' );
	?>

	<?php if ( get_the_content() ) : ?>
		<section class="section article">
			<div class="container container--narrow">
				<div class="article__body"><?php the_content(); ?></div>
			</div>
		</section>
	<?php endif; ?>

	<?php if ( $map_dirs ) : ?>
		<section class="section<?php echo get_the_content() ? ' section--cream' : ''; ?>">
			<?php map_deco( 'star' ); ?>
			<div class="container">
				<div class="sec-head sec-head--center">
					<div class="sec-head__text">
						<span class="eyebrow"><?php esc_html_e( 'Направления', 'musicartplus' ); ?></span>
						<h2 class="h2"><?php esc_html_e( 'Выберите, с чего начать', 'musicartplus' ); ?></h2>
						<p class="sec-head__desc"><?php esc_html_e( 'Можно заниматься одним направлением или собрать своё сочетание.', 'musicartplus' ); ?></p>
					</div>
				</div>

				<div class="grid g-3">
					<?php foreach ( $map_dirs as $map_i => $map_dir ) : ?>
						<?php map_direction_tile( $map_dir, $map_i % 4 ); ?>
					<?php endforeach; ?>
				</div>
			</div>
		</section>
	<?php endif; ?>

	<?php
	map_widget_section();

	map_cta_band(
		__( 'Не знаете, что выбрать?', 'musicartplus' ),
		__( 'Расскажите о ребёнке — подскажем направление и педагога на пробном уроке.', 'musicartplus' )
	);

endwhile;

get_footer();
