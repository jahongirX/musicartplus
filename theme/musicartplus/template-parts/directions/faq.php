<?php
/**
 * Часто задаваемые вопросы.
 *
 * @package MusicArtPlus
 */

defined( 'ABSPATH' ) || exit;

$map_id    = get_the_ID();
$map_items = map_field( 'faq_items', $map_id, array() );

if ( ! $map_items ) {
	return;
}
?>
<section class="section section--cream" id="faq">
	<div class="container container--narrow">
		<div class="sec-head sec-head--center">
			<div class="sec-head__text">
				<?php if ( map_field( 'faq_eyebrow', $map_id ) ) : ?>
					<span class="eyebrow"><?php echo esc_html( map_field( 'faq_eyebrow', $map_id ) ); ?></span>
				<?php endif; ?>
				<h2 class="h2"><?php echo esc_html( map_field( 'faq_title', $map_id, __( 'Часто задаваемые вопросы', 'musicartplus' ) ) ); ?></h2>
			</div>
		</div>

		<div class="faq">
			<?php foreach ( array_values( (array) $map_items ) as $map_i => $map_item ) : ?>
				<div class="faq__item reveal" data-delay="<?php echo (int) ( $map_i % 4 ); ?>">
					<button class="faq__q" type="button" aria-expanded="false">
						<span><?php echo esc_html( $map_item['faq_q'] ); ?></span>
						<span class="faq__ico"><?php map_the_icon( 'plus' ); ?></span>
					</button>
					<div class="faq__a"><div><?php echo esc_html( $map_item['faq_a'] ); ?></div></div>
				</div>
			<?php endforeach; ?>
		</div>
	</div>
</section>
