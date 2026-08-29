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
						<span class="eyebrow"><?php echo esc_html( map_field( 'teachers_eyebrow', get_the_ID(), 'Основной состав' ) ); ?></span>
						<h2 class="h2"><?php echo esc_html( map_field( 'teachers_title', get_the_ID(), 'Наши преподаватели' ) ); ?></h2>
						<p class="sec-head__desc"><?php echo esc_html( map_field( 'teachers_text', get_the_ID(), 'Кнопка «Записаться» ведёт в систему «Мой класс» — там видно свободное время педагога.' ) ); ?></p>
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
						<span class="eyebrow"><?php echo esc_html( map_field( 'guests_eyebrow', get_the_ID(), 'Приглашённые мастера' ) ); ?></span>
						<h2 class="h2"><?php echo esc_html( map_field( 'guests_title', get_the_ID(), 'Мастера, которые приходят' ) ); ?></h2>
						<p class="sec-head__desc"><?php echo esc_html( map_field( 'guests_text', get_the_ID(), 'Профессора и заслуженные деятели искусств проводят у нас мастер-классы и творческие встречи.' ) ); ?></p>
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
		map_field( 'teachers_cta_title', get_the_ID(), __( 'Хотите заниматься у наших педагогов?', 'musicartplus' ) ),
		map_field( 'teachers_cta_text', get_the_ID(), __( 'Оставьте заявку — подберём педагога под возраст, характер и цели ребёнка.', 'musicartplus' ) )
	);

endwhile;

get_footer();
