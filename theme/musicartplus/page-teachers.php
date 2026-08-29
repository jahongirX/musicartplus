<?php
/**
 * Template Name: Педагоги
 *
 * @package MusicArtPlus
 */

defined( 'ABSPATH' ) || exit;

get_header();

while ( have_posts() ) :
	the_post();

	get_template_part( 'template-parts/page/hero', null, array(
		'crumb' => get_the_title(),
		'title' => get_the_title(),
		'text'  => map_page_subtitle() ? map_page_subtitle() : __( 'Нажмите на фотографию — откроется биография, расписание и запись на урок.', 'musicartplus' ),
	) );

	$map_teachers = map_get_items( 'map_teacher' );
	$map_guests   = map_get_items( 'map_guest' );
	?>

	<?php if ( $map_teachers ) : ?>
		<section class="section">
			<div class="container">
				<div class="sec-head">
					<div class="sec-head__text">
						<span class="eyebrow"><?php esc_html_e( 'Основной состав', 'musicartplus' ); ?></span>
						<h2 class="h2"><?php esc_html_e( 'Наши преподаватели', 'musicartplus' ); ?></h2>
						<p class="sec-head__desc"><?php esc_html_e( 'Кнопка «Записаться» ведёт в систему «Мой класс» — там видно свободное время педагога.', 'musicartplus' ); ?></p>
					</div>
				</div>

				<div class="grid g-3">
					<?php foreach ( $map_teachers as $map_i => $map_teacher ) : ?>
						<?php map_teacher_card( $map_teacher, $map_i % 3 ); ?>
					<?php endforeach; ?>
				</div>
			</div>
		</section>
	<?php endif; ?>

	<?php if ( $map_guests ) : ?>
		<section class="section section--cream">
			<?php map_deco( 'star' ); ?>
			<div class="container">
				<div class="sec-head sec-head--center">
					<div class="sec-head__text">
						<span class="eyebrow"><?php esc_html_e( 'Приглашённые мастера', 'musicartplus' ); ?></span>
						<h2 class="h2"><?php esc_html_e( 'Мастера, которые приходят', 'musicartplus' ); ?></h2>
						<p class="sec-head__desc"><?php esc_html_e( 'Профессора и заслуженные деятели искусств проводят у нас мастер-классы и творческие встречи.', 'musicartplus' ); ?></p>
					</div>
				</div>

				<div class="grid g-3">
					<?php foreach ( $map_guests as $map_i => $map_guest ) : ?>
						<?php map_guest_card( $map_guest, $map_i % 3 ); ?>
					<?php endforeach; ?>
				</div>
			</div>
		</section>
	<?php endif; ?>

	<?php
	map_widget_section();
	get_template_part( 'template-parts/home/reviews' );

	map_cta_band(
		__( 'Хотите заниматься у наших педагогов?', 'musicartplus' ),
		__( 'Оставьте заявку — подберём педагога под возраст, характер и цели ребёнка.', 'musicartplus' )
	);

endwhile;

get_footer();
