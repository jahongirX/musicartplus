<?php
/**
 * «Особые программы» на странице направлений.
 *
 * @package MusicArtPlus
 */

defined( 'ABSPATH' ) || exit;

$map_id    = get_the_ID();
$map_items = map_field( 'special_items', $map_id, array() );

if ( ! $map_items ) {
	return;
}
?>
<section class="section section--cream">
	<div class="container">
		<div class="sec-head sec-head--center">
			<div class="sec-head__text">
				<?php if ( map_field( 'special_eyebrow', $map_id ) ) : ?>
					<span class="eyebrow"><?php echo esc_html( map_field( 'special_eyebrow', $map_id ) ); ?></span>
				<?php endif; ?>
				<h2 class="h2"><?php echo esc_html( map_field( 'special_title', $map_id, __( 'Не только уроки по расписанию', 'musicartplus' ) ) ); ?></h2>
				<?php if ( map_field( 'special_text', $map_id ) ) : ?>
					<p class="sec-head__desc"><?php echo esc_html( map_field( 'special_text', $map_id ) ); ?></p>
				<?php endif; ?>
			</div>
		</div>

		<div class="grid g-3">
			<?php foreach ( array_values( (array) $map_items ) as $map_i => $map_item ) : ?>
				<article class="dir-tile reveal" data-delay="<?php echo (int) ( $map_i % 3 ); ?>">
					<?php if ( ! empty( $map_item['special_icon'] ) ) : ?>
						<span class="dir-tile__ico"><?php map_the_icon( $map_item['special_icon'] ); ?></span>
					<?php endif; ?>
					<h3><?php echo esc_html( $map_item['special_title'] ); ?></h3>
					<p><?php echo esc_html( $map_item['special_text'] ); ?></p>
				</article>
			<?php endforeach; ?>
		</div>
	</div>
</section>
