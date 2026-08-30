<?php
/**
 * «Наши преимущества» — принципы авторской методики.
 *
 * @package MusicArtPlus
 */

defined( 'ABSPATH' ) || exit;

$map_id    = get_the_ID();
$map_items = map_field( 'adv_items', $map_id, array() );

if ( ! $map_items ) {
	return;
}
?>
<section class="section">
	<div class="container">
		<div class="sec-head sec-head--center">
			<div class="sec-head__text">
				<?php if ( map_field( 'adv_eyebrow', $map_id ) ) : ?>
					<span class="eyebrow"><?php echo esc_html( map_field( 'adv_eyebrow', $map_id ) ); ?></span>
				<?php endif; ?>
				<h2 class="h2"><?php echo esc_html( map_field( 'adv_title', $map_id, __( 'Авторская методика центра', 'musicartplus' ) ) ); ?></h2>
				<?php if ( map_field( 'adv_text', $map_id ) ) : ?>
					<p class="sec-head__desc"><?php echo esc_html( map_field( 'adv_text', $map_id ) ); ?></p>
				<?php endif; ?>
			</div>
		</div>

		<div class="grid g-4">
			<?php foreach ( array_values( (array) $map_items ) as $map_i => $map_item ) : ?>
				<article class="adv reveal" data-delay="<?php echo (int) ( $map_i % 4 ); ?>" style="position:relative">
					<?php // Номер проставляется по порядку — редактору не надо следить за нумерацией. ?>
					<span class="adv__num" aria-hidden="true"><?php echo (int) ( $map_i + 1 ); ?></span>
					<?php if ( ! empty( $map_item['adv_icon'] ) ) : ?>
						<span class="adv__ico"><?php map_the_icon( $map_item['adv_icon'] ); ?></span>
					<?php endif; ?>
					<div>
						<h4><?php echo esc_html( $map_item['adv_title'] ); ?></h4>
						<p><?php echo esc_html( $map_item['adv_text'] ); ?></p>
					</div>
				</article>
			<?php endforeach; ?>
		</div>
	</div>
</section>
