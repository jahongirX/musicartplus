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
	'title' => map_blog_field(
		'news_hero_title',
		is_home() && get_option( 'page_for_posts' )
			? get_the_title( get_option( 'page_for_posts' ) )
			: __( 'Чем живёт центр искусств', 'musicartplus' )
	),
	'text'  => map_blog_field( 'news_hero_text', __( 'Концерты, мастер-классы, выставки и достижения наших учеников.', 'musicartplus' ) ),
	'image' => map_image_url( map_blog_field( 'news_hero_image' ), 'map-hero' ),
) );
?>

<?php
// Свежая новость идёт крупным блоком, три следующие — компактной колонкой
// рядом, остальные обычной сеткой. Так первая страница ленты выглядит как
// подборка, а не как ряд одинаковых карточек.
$map_posts = array();

while ( have_posts() ) :
	the_post();
	$map_posts[] = get_post();
endwhile;

$map_lead = array_slice( $map_posts, 0, 1 );
$map_side = array_slice( $map_posts, 1, 3 );
$map_rest = array_slice( $map_posts, 4 );
?>

<section class="section">
	<div class="container">
		<?php if ( $map_posts ) : ?>
			<?php map_news_filter( $map_posts ); ?>

			<div class="news-featured">
				<?php map_news_card( $map_lead[0], 0, true, '16/9' ); ?>
				<?php if ( $map_side ) : ?>
					<div class="news-side">
						<?php foreach ( $map_side as $map_i => $map_post ) : ?>
							<?php map_news_mini( $map_post, $map_i + 1 ); ?>
						<?php endforeach; ?>
					</div>
				<?php endif; ?>
			</div>

			<?php if ( $map_rest ) : ?>
				<div class="grid g-3" style="margin-top:clamp(20px,2.6vw,36px)">
					<?php foreach ( $map_rest as $map_i => $map_post ) : ?>
						<?php map_news_card( $map_post, $map_i % 3 ); ?>
					<?php endforeach; ?>
				</div>
			<?php endif; ?>

			<?php
			the_posts_pagination( array(
				'mid_size'  => 1,
				'prev_text' => __( 'Назад', 'musicartplus' ),
				'next_text' => __( 'Вперёд', 'musicartplus' ),
			) );
			?>
		<?php else : ?>
			<p class="lead"><?php echo esc_html( map_opt( 'archive_empty', __( 'Новостей пока нет.', 'musicartplus' ) ) ); ?></p>
		<?php endif; ?>
	</div>
</section>

<?php
// В вёрстке этот блок уводит в соцсети, а не на запись: подписка уместнее
// повторного призыва — на странице новостей посетитель ещё присматривается.
$map_news_buttons = array();

foreach ( array( 'telegram' => 'Telegram-канал', 'rutube' => 'Rutube' ) as $map_key => $map_label ) {
	if ( map_opt( $map_key ) ) {
		$map_news_buttons[] = array( 'url' => map_opt( $map_key ), 'label' => $map_label );
	}
}

map_cta_band(
	map_blog_field( 'news_cta_title', __( 'Не пропускайте новое', 'musicartplus' ) ),
	map_blog_field( 'news_cta_text', __( 'Анонсы концертов, мастер-классов и наборов в группы мы публикуем в Telegram-канале центра.', 'musicartplus' ) ),
	'section--cream',
	$map_news_buttons
);

get_footer();
