<?php
/**
 * Блок «текст слева, форма справа» для внутренних страниц.
 *
 * @package MusicArtPlus
 *
 * @var array $args eyebrow, title, text, points, phone_label, form_title,
 *                  form_text, form_id, class.
 */

defined( 'ABSPATH' ) || exit;

$map_a = wp_parse_args( $args, array(
	'eyebrow'     => '',
	'title'       => '',
	'text'        => '',
	'points'      => array(),
	'phone_label' => '',
	'form_title'  => '',
	'form_text'   => '',
	'form_id'     => 'form-page',
	'class'       => '',
) );
?>
<section class="section<?php echo $map_a['class'] ? ' ' . esc_attr( $map_a['class'] ) : ''; ?>">
	<div class="container">
		<div class="contact-grid">
			<div class="reveal reveal--left">
				<?php if ( $map_a['eyebrow'] ) : ?>
					<span class="eyebrow"><?php echo esc_html( $map_a['eyebrow'] ); ?></span>
				<?php endif; ?>
				<h2 class="h2"><?php echo esc_html( $map_a['title'] ); ?></h2>
				<?php if ( $map_a['text'] ) : ?>
					<p class="lead" style="margin-top:18px"><?php echo esc_html( $map_a['text'] ); ?></p>
				<?php endif; ?>

				<?php if ( $map_a['points'] ) : ?>
					<ul class="about-list">
						<?php foreach ( (array) $map_a['points'] as $map_point ) : ?>
							<li>
								<span class="tick"><?php map_the_icon( 'check' ); ?></span>
								<span><?php echo esc_html( $map_point ); ?></span>
							</li>
						<?php endforeach; ?>
					</ul>
				<?php endif; ?>

				<?php if ( $map_a['phone_label'] && map_opt( 'phone' ) ) : ?>
					<div class="ci" style="margin-top:26px">
						<span class="ci__ico"><?php map_the_icon( 'phone' ); ?></span>
						<div>
							<b><?php echo esc_html( $map_a['phone_label'] ); ?></b>
							<a href="<?php echo esc_attr( map_phone_href() ); ?>"><?php echo esc_html( map_opt( 'phone' ) ); ?></a>
						</div>
					</div>
				<?php endif; ?>
			</div>

			<?php map_form_card( $map_a['form_title'], $map_a['form_text'], $map_a['form_id'] ); ?>
		</div>
	</div>
</section>
