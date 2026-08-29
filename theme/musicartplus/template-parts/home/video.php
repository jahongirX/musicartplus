<?php
/**
 * Блок видео на главной.
 *
 * @package MusicArtPlus
 */

defined( 'ABSPATH' ) || exit;

$map_videos = map_field( 'videos', get_the_ID(), array() );

if ( ! $map_videos ) {
	return;
}
?>
<section class="section" id="video">
	<?php map_deco( 'brush' ); ?>
	<div class="container">
		<div class="sec-head">
			<div class="sec-head__text">
				<span class="eyebrow"><?php echo esc_html( map_home_field( 'video_eyebrow', 'Видео' ) ); ?></span>
				<h2 class="h2"><?php echo esc_html( map_home_field( 'video_title', 'Посмотрите, как мы работаем' ) ); ?></h2>
				<p class="sec-head__desc"><?php echo esc_html( map_home_field( 'video_text', 'Съёмки занятий и мероприятий. Видео открывается прямо на сайте.' ) ); ?></p>
			</div>
			<?php if ( map_opt( 'rutube' ) ) : ?>
				<a class="link-arrow" href="<?php echo esc_url( map_opt( 'rutube' ) ); ?>" target="_blank" rel="noopener"><?php echo esc_html( map_opt( 'rutube_label', __( 'Канал на Rutube', 'musicartplus' ) ) ); ?><?php map_the_icon( 'ar' ); ?></a>
			<?php endif; ?>
		</div>

		<div class="grid g-3">
			<?php
			foreach ( array_values( (array) $map_videos ) as $map_i => $map_video ) :
				if ( empty( $map_video['url'] ) ) {
					continue;
				}

				$map_cover = ! empty( $map_video['cover'] ) ? wp_get_attachment_image_url( $map_video['cover'], 'map-card' ) : '';
				// Ссылка на страницу ролика — запасной путь, если плеер не откроется.
				$map_page  = str_replace( '/play/embed/', '/video/', $map_video['url'] );
				?>
				<button class="video-card reveal" data-delay="<?php echo (int) $map_i; ?>" type="button"
					data-video="<?php echo esc_url( $map_video['url'] ); ?>"
					data-video-type="iframe"
					data-video-page="<?php echo esc_url( $map_page ); ?>"
					aria-label="<?php echo esc_attr( sprintf( __( 'Смотреть: %s', 'musicartplus' ), $map_video['title'] ) ); ?>">
					<?php if ( $map_cover ) : ?>
						<img src="<?php echo esc_url( $map_cover ); ?>" alt="" loading="lazy" width="800" height="500">
					<?php endif; ?>
					<span class="video-card__play"><?php map_the_icon( 'play' ); ?></span>
					<span class="video-card__cap">
						<b><?php echo esc_html( $map_video['title'] ); ?></b>
						<span><?php echo esc_html( isset( $map_video['subtitle'] ) ? $map_video['subtitle'] : '' ); ?></span>
					</span>
				</button>
			<?php endforeach; ?>
		</div>
	</div>
</section>
