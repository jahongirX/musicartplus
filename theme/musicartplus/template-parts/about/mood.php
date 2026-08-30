<?php
/**
 * «Атмосфера» — галерея занятий.
 *
 * @package MusicArtPlus
 */

defined( 'ABSPATH' ) || exit;

$map_id      = get_the_ID();
$map_gallery = map_field( 'mood_gallery', $map_id, array() );

if ( ! $map_gallery ) {
	return;
}
?>
<section class="section section--cream">
	<div class="container">
		<div class="sec-head sec-head--center">
			<div class="sec-head__text">
				<?php if ( map_field( 'mood_eyebrow', $map_id ) ) : ?>
					<span class="eyebrow"><?php echo esc_html( map_field( 'mood_eyebrow', $map_id ) ); ?></span>
				<?php endif; ?>
				<h2 class="h2"><?php echo esc_html( map_field( 'mood_title', $map_id, __( 'Как проходят наши занятия', 'musicartplus' ) ) ); ?></h2>
				<?php if ( map_field( 'mood_text', $map_id ) ) : ?>
					<p class="sec-head__desc"><?php echo esc_html( map_field( 'mood_text', $map_id ) ); ?></p>
				<?php endif; ?>
			</div>
		</div>

		<div class="reveal">
			<div class="gallery">
				<?php foreach ( (array) $map_gallery as $map_item ) : ?>
					<?php $map_gid = is_array( $map_item ) ? $map_item['ID'] : $map_item; ?>
					<figure><?php echo wp_get_attachment_image( $map_gid, 'map-tile', false, array( 'loading' => 'lazy' ) ); ?></figure>
				<?php endforeach; ?>
			</div>
		</div>
	</div>
</section>
