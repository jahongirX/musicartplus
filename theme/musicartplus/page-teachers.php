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

	$map_id = get_the_ID();

	get_template_part( 'template-parts/page/hero', null, array(
		'crumb' => get_the_title(),
		'title' => map_field( 'teachers_hero_title', $map_id, get_the_title() ),
		'text'  => map_field( 'teachers_hero_text', $map_id, map_page_subtitle() ),
		'image' => map_image_url( map_field( 'teachers_hero_image', $map_id ), 'map-hero' ),
	) );

	$map_teachers = map_get_items( 'map_teacher' );
	$map_guests   = map_get_items( 'map_guest' );
	?>

	<?php if ( $map_teachers ) : ?>
		<section class="section">
			<div class="container">
				<div class="sec-head">
					<div class="sec-head__text">
						<span class="eyebrow"><?php echo esc_html( map_field( 'teachers_eyebrow', $map_id, 'Основной состав' ) ); ?></span>
						<h2 class="h2"><?php echo esc_html( map_field( 'teachers_title', $map_id, 'Наши преподаватели' ) ); ?></h2>
						<p class="sec-head__desc"><?php echo esc_html( map_field( 'teachers_text', $map_id, 'Кнопка «Записаться» ведёт в систему «Мой класс» — там видно свободное время педагога.' ) ); ?></p>
					</div>
				</div>

				<?php // На широком экране сетка, на телефоне — лента: карточек много и они высокие. ?>
				<?php map_slider_open( 'teachers-all', 'gridMobile', 'slider--grid slider--grid-3' ); ?>
					<?php foreach ( $map_teachers as $map_teacher ) : ?>
						<div class="swiper-slide"><?php map_teacher_card( $map_teacher, 0, false ); ?></div>
					<?php endforeach; ?>
				<?php map_slider_close(); ?>
			</div>
		</section>
	<?php endif; ?>

	<?php if ( $map_guests ) : ?>
		<section class="section section--cream">
			<?php map_deco( 'star' ); ?>
			<div class="container">
				<div class="sec-head sec-head--center">
					<div class="sec-head__text">
						<span class="eyebrow"><?php echo esc_html( map_field( 'guests_eyebrow', $map_id, 'Приглашённые артисты и педагоги' ) ); ?></span>
						<h2 class="h2"><?php echo esc_html( map_field( 'guests_title', $map_id, 'Мастера, которые приходят к нам на мастер-классы' ) ); ?></h2>
						<p class="sec-head__desc"><?php echo esc_html( map_field( 'guests_text', $map_id, 'Профессора ведущих музыкальных вузов страны проводят открытые занятия и творческие встречи для наших учеников.' ) ); ?></p>
					</div>
				</div>

				<?php map_slider_open( 'guests', 'gridMobile', 'slider--grid slider--grid-3' ); ?>
					<?php foreach ( $map_guests as $map_guest ) : ?>
						<div class="swiper-slide"><?php map_guest_card( $map_guest, 0, false ); ?></div>
					<?php endforeach; ?>
				<?php map_slider_close(); ?>
			</div>
		</section>
	<?php endif; ?>

	<?php
	get_template_part( 'template-parts/teachers/shots' );
	get_template_part( 'template-parts/teachers/videos' );

	get_template_part( 'template-parts/home/reviews', null, array(
		'eyebrow' => map_field( 'teachers_reviews_eyebrow', $map_id, __( 'Отзывы', 'musicartplus' ) ),
		'title'   => map_field( 'teachers_reviews_title', $map_id, __( 'Что говорят о наших педагогах', 'musicartplus' ) ),
		'name'    => 'reviews-teachers',
	) );

	get_template_part( 'template-parts/page/contact', null, array(
		'eyebrow'     => map_field( 'teachers_cta_eyebrow', $map_id, __( 'Обратная связь', 'musicartplus' ) ),
		'title'       => map_field( 'teachers_cta_title', $map_id, __( 'Не выбрали педагога? Поможем', 'musicartplus' ) ),
		'text'        => map_field( 'teachers_cta_text', $map_id ),
		'phone_label' => map_field( 'teachers_phone_label', $map_id, __( 'Позвонить сейчас', 'musicartplus' ) ),
		'form_title'  => map_field( 'teachers_form_title', $map_id, __( 'Подобрать педагога', 'musicartplus' ) ),
		'form_text'   => map_field( 'teachers_form_text', $map_id, __( 'Два поля — и мы свяжемся с вами в ближайшее рабочее время.', 'musicartplus' ) ),
		'form_btn'    => map_field( 'teachers_form_btn', $map_id, __( 'Отправить заявку', 'musicartplus' ) ),
		'form_id'     => 'form-teach',
		'class'       => 'section--cream',
	) );

	map_widget_section();

endwhile;

get_footer();
