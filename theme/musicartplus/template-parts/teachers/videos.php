<?php
/**
 * Видео приглашённых мастеров.
 *
 * Ролик берётся из карточки самого мастера, обложкой служит его фотография —
 * отдельного списка видео для этой страницы заводить не нужно.
 *
 * @package MusicArtPlus
 */

defined( 'ABSPATH' ) || exit;

$map_id     = get_the_ID();
$map_guests = array();

foreach ( map_get_items( 'map_guest' ) as $map_guest ) {
	if ( map_field( 'guest_video', $map_guest->ID ) ) {
		$map_guests[] = $map_guest;
	}
}

if ( ! $map_guests ) {
	return;
}
?>
<section class="section section--cream">
	<div class="container">
		<div class="sec-head">
			<div class="sec-head__text">
				<?php if ( map_field( 'teachers_video_eyebrow', $map_id ) ) : ?>
					<span class="eyebrow"><?php echo esc_html( map_field( 'teachers_video_eyebrow', $map_id ) ); ?></span>
				<?php endif; ?>
				<h2 class="h2"><?php echo esc_html( map_field( 'teachers_video_title', $map_id, __( 'Приглашённые педагоги — в записи', 'musicartplus' ) ) ); ?></h2>
				<?php if ( map_field( 'teachers_video_text', $map_id ) ) : ?>
					<p class="sec-head__desc"><?php echo esc_html( map_field( 'teachers_video_text', $map_id ) ); ?></p>
				<?php endif; ?>
			</div>
			<?php if ( map_opt( 'rutube' ) ) : ?>
				<a class="link-arrow" href="<?php echo esc_url( map_opt( 'rutube' ) ); ?>" target="_blank" rel="noopener"><?php echo esc_html( map_opt( 'rutube_label', __( 'Канал на Rutube', 'musicartplus' ) ) ); ?><?php map_the_icon( 'ar' ); ?></a>
			<?php endif; ?>
		</div>

		<div class="grid g-3">
			<?php
			foreach ( $map_guests as $map_i => $map_guest ) :
				$map_url   = map_field( 'guest_video', $map_guest->ID );
				$map_photo = get_the_post_thumbnail_url( $map_guest, 'map-card' );
				// Ссылка на страницу ролика — запасной путь, если плеер не откроется.
				$map_page  = str_replace( '/play/embed/', '/video/', $map_url );
				?>
				<button class="video-card reveal" data-delay="<?php echo (int) ( $map_i % 3 ); ?>" type="button"
					data-video="<?php echo esc_url( $map_url ); ?>"
					data-video-type="iframe"
					data-video-page="<?php echo esc_url( $map_page ); ?>"
					aria-label="<?php echo esc_attr( sprintf( __( 'Смотреть видео: %s', 'musicartplus' ), get_the_title( $map_guest ) ) ); ?>">
					<?php if ( $map_photo ) : ?>
						<img src="<?php echo esc_url( $map_photo ); ?>" alt="" loading="lazy" width="800" height="500">
					<?php endif; ?>
					<span class="video-card__play"><?php map_the_icon( 'play' ); ?></span>
					<span class="video-card__cap">
						<b><?php echo esc_html( map_short_name( get_the_title( $map_guest ) ) ); ?></b>
						<span><?php echo esc_html( map_field( 'guest_role', $map_guest->ID ) ); ?></span>
					</span>
				</button>
			<?php endforeach; ?>
		</div>
	</div>
</section>
