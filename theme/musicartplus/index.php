<?php
/**
 * Запасной шаблон и лента новостей.
 *
 * @package MusicArtPlus
 */

defined( 'ABSPATH' ) || exit;

get_header();

get_template_part( 'template-parts/page/hero', null, array(
	'crumb' => __( 'Новости', 'musicartplus' ),
	'title' => is_home() && get_option( 'page_for_posts' ) ? get_the_title( get_option( 'page_for_posts' ) ) : __( 'Чем живёт центр искусств', 'musicartplus' ),
	'text'  => __( 'Концерты, мастер-классы, выставки и достижения наших учеников.', 'musicartplus' ),
) );
?>

<section class="section">
	<div class="container">
		<?php if ( have_posts() ) : ?>
			<div class="grid g-3">
				<?php
				$map_i = 0;

				while ( have_posts() ) :
					the_post();
					map_news_card( get_post(), $map_i % 3 );
					$map_i++;
				endwhile;
				?>
			</div>

			<?php
			the_posts_pagination( array(
				'mid_size'  => 1,
				'prev_text' => __( 'Назад', 'musicartplus' ),
				'next_text' => __( 'Вперёд', 'musicartplus' ),
			) );
			?>
		<?php else : ?>
			<p class="lead"><?php esc_html_e( 'Новостей пока нет.', 'musicartplus' ); ?></p>
		<?php endif; ?>
	</div>
</section>

<?php
map_cta_band(
	__( 'Хотите так же?', 'musicartplus' ),
	__( 'Приходите на пробный урок — познакомимся и подберём педагога.', 'musicartplus' )
);

get_footer();
