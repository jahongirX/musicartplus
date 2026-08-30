<?php
/**
 * Цифры центра.
 *
 * @package MusicArtPlus
 */

defined( 'ABSPATH' ) || exit;

$map_facts = map_field( 'about_facts', get_the_ID(), array() );

if ( ! $map_facts ) {
	return;
}
?>
<section class="section section--cream section--tight">
	<div class="container">
		<div class="stats">
			<?php foreach ( (array) $map_facts as $map_i => $map_fact ) : ?>
				<div class="stat reveal" data-delay="<?php echo (int) $map_i; ?>">
					<b><?php echo esc_html( isset( $map_fact['num'] ) ? $map_fact['num'] : '' ); ?></b>
					<span class="stat__l"><?php echo esc_html( isset( $map_fact['label'] ) ? $map_fact['label'] : '' ); ?></span>
				</div>
			<?php endforeach; ?>
		</div>
	</div>
</section>
