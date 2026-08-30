<?php
/**
 * «Партнёр центра» — блок о фонде.
 *
 * Название и ссылка берутся из настроек темы, чтобы не задваивать их
 * с подвалом: фонд там уже указан.
 *
 * @package MusicArtPlus
 */

defined( 'ABSPATH' ) || exit;

$map_id   = get_the_ID();
$map_text = map_field( 'partner_text', $map_id, '' );

if ( ! $map_text ) {
	return;
}

$map_fund_name = map_opt( 'fund_name' );
$map_fund_url  = map_opt( 'fund_url' );
$map_fund_logo = map_image_url( map_opt( 'fund_logo' ), 'full', map_asset( 'assets/img/ui/forteforma.svg' ) );
?>
<section class="section section--ink">
	<div class="container">
		<div class="about-split">
			<div class="reveal reveal--left">
				<?php if ( map_field( 'partner_eyebrow', $map_id ) ) : ?>
					<span class="eyebrow" style="color:var(--gold)"><?php echo esc_html( map_field( 'partner_eyebrow', $map_id ) ); ?></span>
				<?php endif; ?>

				<h2 class="h2"><?php echo esc_html( map_field( 'partner_title', $map_id, '' ) ); ?></h2>

				<p style="margin-top:18px;font-size:18px;line-height:1.65"><?php echo esc_html( $map_text ); ?></p>

				<?php if ( $map_fund_url ) : ?>
					<div class="flex-center mt-l" style="justify-content:flex-start">
						<a class="btn btn--gold" href="<?php echo esc_url( $map_fund_url ); ?>" target="_blank" rel="noopener"><?php echo esc_html( map_field( 'partner_btn_text', $map_id, __( 'Сайт фонда', 'musicartplus' ) ) ); ?><?php map_the_icon( 'ar', 'btn__ico' ); ?></a>
					</div>
				<?php endif; ?>
			</div>

			<div class="reveal reveal--right ff-mark">
				<img src="<?php echo esc_url( $map_fund_logo ); ?>" alt="<?php echo esc_attr( sprintf( __( 'Фонд %s', 'musicartplus' ), $map_fund_name ) ); ?>" loading="lazy">
				<b><?php echo esc_html( $map_fund_name ); ?></b>
				<span><?php echo esc_html( map_field( 'partner_note', $map_id, '' ) ); ?></span>
			</div>
		</div>
	</div>
</section>
