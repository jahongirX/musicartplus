<?php
/**
 * Разделитель с видео кисти.
 *
 * @package MusicArtPlus
 */

defined( 'ABSPATH' ) || exit;

$map_video = MAP_DIR . '/assets/video/brush.mp4';

if ( ! file_exists( $map_video ) ) {
	return;
}
?>
<div class="interlude interlude--cream">
	<div class="container interlude__narrow">
		<div class="reveal" style="display:grid;grid-template-columns:minmax(0,1fr) minmax(0,1fr);gap:clamp(20px,4vw,50px);align-items:center">
			<div style="border-radius:var(--r-lg);overflow:hidden;box-shadow:var(--sh-2);background:#fff">
				<video data-autoloop muted loop playsinline preload="none"
					src="<?php echo esc_url( map_asset( 'assets/video/brush.mp4' ) ); ?>"
					poster="<?php echo esc_url( map_asset( 'assets/video/brush-poster.jpg' ) ); ?>"
					width="640" height="640"
					aria-label="<?php esc_attr_e( 'Детская рука выводит кистью надпись «Музыка Арт Плюс»', 'musicartplus' ); ?>"></video>
			</div>
			<div>
				<span class="eyebrow"><?php esc_html_e( 'Наш почерк', 'musicartplus' ); ?></span>
				<h3 class="h3"><?php esc_html_e( 'Творчество начинается с первого движения руки', 'musicartplus' ); ?></h3>
				<p class="muted" style="margin-top:14px"><?php esc_html_e( 'Кисть, смычок или клавиша — мы верим, что первое прикосновение к искусству должно быть радостным. С него начинается всё остальное.', 'musicartplus' ); ?></p>
			</div>
		</div>
	</div>
</div>
