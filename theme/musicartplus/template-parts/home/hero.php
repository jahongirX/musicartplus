<?php
/**
 * Первый экран главной страницы.
 *
 * @package MusicArtPlus
 */

defined( 'ABSPATH' ) || exit;

$map_slides = map_field( 'hero_slides', get_the_ID(), array() );
$map_facts  = map_field( 'hero_facts', get_the_ID(), array() );
$map_title  = map_field( 'hero_title', get_the_ID(), get_bloginfo( 'name' ) );
?>
<section class="hero">
	<div class="hero__media">
		<?php
		if ( $map_slides ) :
			foreach ( array_values( (array) $map_slides ) as $map_i => $map_slide ) :
				$map_id  = is_array( $map_slide ) ? $map_slide['ID'] : $map_slide;
				$map_src = wp_get_attachment_image_url( $map_id, 'map-hero' );

				if ( ! $map_src ) {
					continue;
				}
				?>
				<div class="hero__slide<?php echo 0 === $map_i ? ' is-active' : ''; ?>">
					<img src="<?php echo esc_url( $map_src ); ?>"
						alt="<?php echo esc_attr( get_post_meta( $map_id, '_wp_attachment_image_alt', true ) ); ?>"
						<?php
						// Первый слайд — самый крупный элемент экрана: грузим его в первую
						// очередь, остальные откладываем.
						echo 0 === $map_i ? 'fetchpriority="high"' : 'loading="lazy"';
						?>
						width="1400" height="900">
				</div>
				<?php
			endforeach;
		endif;
		?>
	</div>

	<div class="container hero__inner">
		<div class="hero__content">
			<?php if ( map_field( 'hero_eyebrow', get_the_ID() ) ) : ?>
				<span class="hero__eyebrow"><?php map_the_icon( 'pin2' ); ?><?php echo esc_html( map_field( 'hero_eyebrow', get_the_ID() ) ); ?></span>
			<?php endif; ?>

			<h1 class="hero__title" data-reveal-title><?php echo esc_html( $map_title ); ?></h1>

			<?php if ( map_field( 'hero_text', get_the_ID() ) ) : ?>
				<p class="hero__sub"><?php echo esc_html( map_field( 'hero_text', get_the_ID() ) ); ?></p>
			<?php endif; ?>

			<div class="hero__actions">
				<a class="btn btn--gold btn--lg"<?php echo map_cta_attrs(); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>><?php esc_html_e( 'Записаться на пробный урок', 'musicartplus' ); ?></a>
				<a class="btn btn--light btn--lg" href="#directions"><?php esc_html_e( 'Наши направления', 'musicartplus' ); ?></a>
			</div>
		</div>

		<div class="hero__bottom">
			<div class="hero__facts">
				<?php foreach ( (array) $map_facts as $map_fact ) : ?>
					<div class="hero-fact">
						<div class="hero-fact__num"><?php echo esc_html( $map_fact['num'] ); ?></div>
						<div class="hero-fact__label"><?php echo esc_html( $map_fact['label'] ); ?></div>
					</div>
				<?php endforeach; ?>
			</div>
			<div class="hero__dots" role="tablist" aria-label="<?php esc_attr_e( 'Слайды', 'musicartplus' ); ?>"></div>
		</div>
	</div>
</section>
