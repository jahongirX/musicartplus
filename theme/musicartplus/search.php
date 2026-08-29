<?php
/**
 * Результаты поиска.
 *
 * @package MusicArtPlus
 */

defined( 'ABSPATH' ) || exit;

get_header();

get_template_part( 'template-parts/page/hero', null, array(
	'crumb' => __( 'Поиск', 'musicartplus' ),
	'title' => sprintf( __( 'Поиск: %s', 'musicartplus' ), get_search_query() ),
	'text'  => '',
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
			<p class="lead"><?php echo esc_html( map_opt( 'search_empty', __( 'Ничего не нашлось. Попробуйте другой запрос.', 'musicartplus' ) ) ); ?></p>
		<?php endif; ?>
	</div>
</section>

<?php
get_footer();
