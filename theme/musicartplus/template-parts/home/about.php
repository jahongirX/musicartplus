<?php
/**
 * Блок «О центре» на главной.
 *
 * @package MusicArtPlus
 */

defined( 'ABSPATH' ) || exit;

$map_points = map_lines( map_field( 'about_points', get_the_ID() ) );
$map_image  = map_field( 'about_image', get_the_ID() );
$map_about  = map_page_by_template( 'page-about.php' );
$map_teach  = map_page_by_template( 'page-teachers.php' );
?>
<section class="section" id="about">
	<?php map_deco( 'swirl' ); ?>
	<div class="container">
		<div class="about-split">
			<div class="about-visual reveal reveal--left">
				<span class="about-visual__ringbox" aria-hidden="true"><span class="about-visual__ring"></span></span>
				<div class="about-visual__circle">
					<?php if ( $map_image ) : ?>
						<?php echo wp_get_attachment_image( $map_image, 'map-teacher', false, array( 'loading' => 'lazy', 'width' => 700, 'height' => 700 ) ); ?>
					<?php endif; ?>
				</div>
				<div class="about-visual__badge">
					<img src="<?php echo esc_url( map_asset( 'assets/img/ui/logo-color.png' ) ); ?>" alt="" width="176" height="103" loading="lazy">
					<div><b><?php echo esc_html( get_bloginfo( 'name' ) ); ?></b><span><?php echo esc_html( get_bloginfo( 'description' ) ? get_bloginfo( 'description' ) : __( 'центр искусств', 'musicartplus' ) ); ?></span></div>
				</div>
			</div>

			<div class="reveal reveal--right">
				<span class="eyebrow"><?php echo esc_html( map_home_field( 'about_eyebrow', 'О центре искусств' ) ); ?></span>
				<h2 class="h2"><?php echo wp_kses_post( map_field( 'about_title', get_the_ID() ) ); ?></h2>

				<?php if ( map_field( 'about_text', get_the_ID() ) ) : ?>
					<div class="lead rich" style="margin-top:20px"><?php echo wp_kses_post( map_field( 'about_text', get_the_ID() ) ); ?></div>
				<?php endif; ?>

				<?php if ( $map_points ) : ?>
					<ul class="about-list">
						<?php foreach ( $map_points as $map_point ) : ?>
							<li><span class="tick"><?php map_the_icon( map_home_field( 'about_point_icon', 'check' ) ); ?></span><span><?php echo wp_kses_post( $map_point ); ?></span></li>
						<?php endforeach; ?>
					</ul>
				<?php endif; ?>

				<?php if ( $map_about || $map_teach ) : ?>
					<div class="flex-center mt-l" style="justify-content:flex-start">
						<?php if ( $map_about ) : ?>
							<a class="btn btn--dark" href="<?php echo esc_url( get_permalink( $map_about ) ); ?>"><?php echo esc_html( map_home_field( 'about_btn_text', 'Подробнее о центре' ) ); ?></a>
						<?php endif; ?>
						<?php if ( $map_teach ) : ?>
							<a class="link-arrow" href="<?php echo esc_url( get_permalink( $map_teach ) ); ?>"><?php echo esc_html( map_home_field( 'about_link_text', 'Наши педагоги' ) ); ?><?php map_the_icon( 'ar' ); ?></a>
						<?php endif; ?>
					</div>
				<?php endif; ?>
			</div>
		</div>
	</div>
</section>
