<?php
/**
 * Блок педагогов на главной.
 *
 * @package MusicArtPlus
 */

defined( 'ABSPATH' ) || exit;

$map_teachers = map_get_items( 'map_teacher' );

if ( ! $map_teachers ) {
	return;
}

$map_teach_page = map_page_by_template( 'page-teachers.php' );
?>
<section class="section section--cream" id="teachers">
	<div class="container">
		<div class="sec-head">
			<div class="sec-head__text">
				<span class="eyebrow"><?php esc_html_e( 'Педагоги', 'musicartplus' ); ?></span>
				<h2 class="h2"><?php esc_html_e( 'Люди, к которым хочется возвращаться', 'musicartplus' ); ?></h2>
				<p class="sec-head__desc"><?php esc_html_e( 'Нажмите на фотографию, чтобы открыть биографию, расписание и записаться на урок.', 'musicartplus' ); ?></p>
			</div>
			<?php map_slider_nav( 'teachers-home' ); ?>
		</div>

		<?php map_slider_open( 'teachers-home' ); ?>
			<?php foreach ( $map_teachers as $map_teacher ) : ?>
				<div class="swiper-slide"><?php map_teacher_card( $map_teacher, 0, false ); ?></div>
			<?php endforeach; ?>
		<?php map_slider_close(); ?>

		<?php if ( $map_teach_page ) : ?>
			<div class="flex-center mt-l">
				<a class="btn btn--ghost" href="<?php echo esc_url( get_permalink( $map_teach_page ) ); ?>"><?php esc_html_e( 'Все педагоги', 'musicartplus' ); ?><?php map_the_icon( 'ar', 'btn__ico' ); ?></a>
			</div>
		<?php endif; ?>
	</div>
</section>
