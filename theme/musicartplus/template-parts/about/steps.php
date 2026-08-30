<?php
/**
 * «Как всё устроено» — шаги, цитата и карточка пробного урока.
 *
 * @package MusicArtPlus
 */

defined( 'ABSPATH' ) || exit;

$map_id    = get_the_ID();
$map_steps = map_field( 'steps', $map_id, array() );
$map_quote = map_field( 'steps_quote', $map_id, '' );
$map_card  = map_field( 'steps_card_title', $map_id, '' );

if ( ! $map_steps && ! $map_quote && ! $map_card ) {
	return;
}

$map_card_img = (int) map_field( 'steps_card_image', $map_id, 0 );
?>
<section class="section">
	<div class="container">
		<div class="sec-head sec-head--center">
			<div class="sec-head__text">
				<?php if ( map_field( 'steps_eyebrow', $map_id ) ) : ?>
					<span class="eyebrow"><?php echo esc_html( map_field( 'steps_eyebrow', $map_id ) ); ?></span>
				<?php endif; ?>
				<h2 class="h2"><?php echo esc_html( map_field( 'steps_title', $map_id, __( 'Путь ученика', 'musicartplus' ) ) ); ?></h2>
			</div>
		</div>

		<div class="grid g-2" style="align-items:start">
			<?php if ( $map_steps ) : ?>
				<div class="tl">
					<?php foreach ( (array) $map_steps as $map_i => $map_step ) : ?>
						<div class="tl__item reveal" data-delay="<?php echo (int) $map_i; ?>">
							<div class="tl__year"><?php echo esc_html( isset( $map_step['step_label'] ) ? $map_step['step_label'] : '' ); ?></div>
							<h4><?php echo esc_html( isset( $map_step['step_title'] ) ? $map_step['step_title'] : '' ); ?></h4>
							<p><?php echo esc_html( isset( $map_step['step_text'] ) ? $map_step['step_text'] : '' ); ?></p>
						</div>
					<?php endforeach; ?>
				</div>
			<?php endif; ?>

			<div class="reveal reveal--right">
				<?php if ( $map_quote ) : ?>
					<div class="quote">
						<span class="quote__mark" aria-hidden="true">&ldquo;</span>
						<p><?php echo esc_html( $map_quote ); ?></p>
						<?php if ( map_field( 'steps_quote_author', $map_id ) ) : ?>
							<footer><?php echo esc_html( map_field( 'steps_quote_author', $map_id ) ); ?></footer>
						<?php endif; ?>
					</div>
				<?php endif; ?>

				<?php if ( $map_card ) : ?>
					<div class="card" style="margin-top:22px">
						<?php if ( $map_card_img ) : ?>
							<div class="card__media" style="aspect-ratio:4/3">
								<?php echo wp_get_attachment_image( $map_card_img, 'map-card', false, array( 'loading' => 'lazy' ) ); ?>
							</div>
						<?php endif; ?>
						<div class="card__body">
							<h3 class="card__title"><?php echo esc_html( $map_card ); ?></h3>
							<p class="card__text"><?php echo esc_html( map_field( 'steps_card_text', $map_id, '' ) ); ?></p>
							<div class="card__foot">
								<a class="btn btn--gold btn--sm"<?php echo map_cta_attrs(); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>><?php echo esc_html( map_field( 'steps_card_btn', $map_id, __( 'Записаться', 'musicartplus' ) ) ); ?></a>
							</div>
						</div>
					</div>
				<?php endif; ?>
			</div>
		</div>
	</div>
</section>
