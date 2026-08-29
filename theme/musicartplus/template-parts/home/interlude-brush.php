<?php
/**
 * Разделитель с видео кисти.
 *
 * @package MusicArtPlus
 */

defined( 'ABSPATH' ) || exit;

// Ролик можно заменить своим в настройках главной; пока его нет — берём файл темы.
$map_video_url = (string) map_home_field( 'brush_video', '' );
$map_poster    = map_image_url( map_home_field( 'brush_poster' ), 'map-card', map_asset( 'assets/video/brush-poster.jpg' ) );

if ( ! $map_video_url ) {
	$map_video_url = map_asset( 'assets/video/brush.mp4' );
}

$map_video = MAP_DIR . '/assets/video/brush.mp4';

if ( ! map_home_field( 'brush_video', '' ) && ! file_exists( $map_video ) ) {
	return;
}
?>
<div class="interlude interlude--cream">
	<div class="container interlude__narrow">
		<div class="reveal" style="display:grid;grid-template-columns:minmax(0,1fr) minmax(0,1fr);gap:clamp(20px,4vw,50px);align-items:center">
			<div style="border-radius:var(--r-lg);overflow:hidden;box-shadow:var(--sh-2);background:#fff">
				<video data-autoloop muted loop playsinline preload="none"
					src="<?php echo esc_url( $map_video_url ); ?>"
					poster="<?php echo esc_url( $map_poster ); ?>"
					width="640" height="640"
					aria-label="<?php esc_attr_e( 'Детская рука выводит кистью надпись «Музыка Арт Плюс»', 'musicartplus' ); ?>"></video>
			</div>
			<div>
				<span class="eyebrow"><?php echo esc_html( map_home_field( 'brush_eyebrow', 'Наш почерк' ) ); ?></span>
				<h3 class="h3"><?php echo esc_html( map_home_field( 'brush_title', 'Творчество начинается с первого движения руки' ) ); ?></h3>
				<p class="muted" style="margin-top:14px"><?php echo esc_html( map_home_field( 'brush_text', 'Кисть, смычок или клавиша — мы верим, что первое прикосновение к искусству должно быть радостным. С него начинается всё остальное.' ) ); ?></p>
			</div>
		</div>
	</div>
</div>
