<?php
/**
 * «Кто мы» — текст о центре и фотографии рядом.
 *
 * @package MusicArtPlus
 */

defined( 'ABSPATH' ) || exit;

$map_id      = get_the_ID();
$map_title   = map_field( 'intro_title', $map_id, '' );
$map_text    = map_field( 'intro_text', $map_id, '' );
$map_gallery = map_field( 'intro_gallery', $map_id, array() );

if ( ! $map_title && ! $map_text ) {
	return;
}

$map_accent  = map_field( 'intro_accent', $map_id, '' );
$map_teach   = map_page_by_template( 'page-teachers.php' );
?>
<section class="section">
	<div class="container">
		<div class="about-split">
			<div class="reveal reveal--left">
				<?php if ( map_field( 'intro_eyebrow', $map_id ) ) : ?>
					<span class="eyebrow"><?php echo esc_html( map_field( 'intro_eyebrow', $map_id ) ); ?></span>
				<?php endif; ?>

				<h2 class="h2">
					<?php echo esc_html( $map_title ); ?>
					<?php if ( $map_accent ) : ?>
						<span class="accent"><?php echo esc_html( $map_accent ); ?></span>
					<?php endif; ?>
				</h2>

				<?php if ( $map_text ) : ?>
					<div class="about-split__text"><?php echo wp_kses_post( $map_text ); ?></div>
				<?php endif; ?>

				<div class="flex-center mt-l" style="justify-content:flex-start">
					<a class="btn btn--gold"<?php echo map_cta_attrs(); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>><?php echo esc_html( map_field( 'intro_btn_text', $map_id, map_cta_label() ) ); ?></a>
					<?php if ( $map_teach ) : ?>
						<a class="btn btn--ghost" href="<?php echo esc_url( get_permalink( $map_teach ) ); ?>"><?php echo esc_html( map_field( 'intro_link_text', $map_id, __( 'Познакомиться с педагогами', 'musicartplus' ) ) ); ?></a>
					<?php endif; ?>
				</div>
			</div>

			<?php if ( $map_gallery ) : ?>
				<div class="reveal reveal--right">
					<div class="gallery-strip">
						<?php foreach ( (array) $map_gallery as $map_item ) : ?>
							<?php $map_gid = is_array( $map_item ) ? $map_item['ID'] : $map_item; ?>
							<figure><?php echo wp_get_attachment_image( $map_gid, 'map-card', false, array( 'loading' => 'lazy' ) ); ?></figure>
						<?php endforeach; ?>
					</div>
				</div>
			<?php endif; ?>
		</div>
	</div>
</section>
