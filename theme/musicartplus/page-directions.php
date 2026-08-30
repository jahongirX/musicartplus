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

	$map_id = get_the_ID();

	get_template_part( 'template-parts/page/hero', null, array(
		'crumb' => get_the_title(),
		'title' => map_field( 'dirs_hero_title', $map_id, get_the_title() ),
		'text'  => map_field( 'dirs_hero_text', $map_id, map_page_subtitle() ),
		'image' => map_image_url( map_field( 'dirs_hero_image', $map_id ), 'map-hero' ),
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
				<?php if ( map_field( 'dirs_title', $map_id ) || map_field( 'dirs_eyebrow', $map_id ) ) : ?>
					<div class="sec-head sec-head--center">
						<div class="sec-head__text">
							<?php if ( map_field( 'dirs_eyebrow', $map_id ) ) : ?>
								<span class="eyebrow"><?php echo esc_html( map_field( 'dirs_eyebrow', $map_id ) ); ?></span>
							<?php endif; ?>
							<h2 class="h2"><?php echo esc_html( map_field( 'dirs_title', $map_id, __( 'Выберите, с чего начать', 'musicartplus' ) ) ); ?></h2>
							<?php if ( map_field( 'dirs_text', $map_id ) ) : ?>
								<p class="sec-head__desc"><?php echo esc_html( map_field( 'dirs_text', $map_id ) ); ?></p>
							<?php endif; ?>
						</div>
					</div>
				<?php endif; ?>

				<div class="grid g-4">
					<?php foreach ( $map_dirs as $map_i => $map_dir ) : ?>
						<?php map_direction_tile( $map_dir, $map_i % 4 ); ?>
					<?php endforeach; ?>
				</div>
			</div>
		</section>
	<?php endif; ?>

	<?php
	get_template_part( 'template-parts/directions/specials' );
	get_template_part( 'template-parts/home/interlude-notes' );
	get_template_part( 'template-parts/directions/advantages' );
	get_template_part( 'template-parts/directions/faq' );

	get_template_part( 'template-parts/page/contact', null, array(
		'eyebrow'    => map_field( 'dirs_cta_eyebrow', $map_id, __( 'Не знаете, что выбрать?', 'musicartplus' ) ),
		'title'      => map_field( 'dirs_cta_title', $map_id, __( 'Поможем подобрать направление и педагога', 'musicartplus' ) ),
		'text'       => map_field( 'dirs_cta_text', $map_id ),
		'points'     => map_lines( map_field( 'dirs_cta_points', $map_id ) ),
		'form_title' => map_field( 'dirs_form_title', $map_id, __( 'Подобрать направление', 'musicartplus' ) ),
		'form_text'  => map_field( 'dirs_form_text', $map_id, __( 'Оставьте контакты — перезвоним и всё обсудим.', 'musicartplus' ) ),
		'form_id'    => 'form-dir',
	) );

	map_widget_section();

endwhile;

get_footer();
