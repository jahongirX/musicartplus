<?php
/**
 * Страница новости.
 *
 * @package MusicArtPlus
 */

defined( 'ABSPATH' ) || exit;

get_header();

while ( have_posts() ) :
	the_post();

	$map_badge  = map_news_badge( get_post() );
	$map_cover  = get_the_post_thumbnail_url( get_the_ID(), 'map-hero' );
	$map_news   = (int) get_option( 'page_for_posts' );
	$map_link   = $map_news ? get_permalink( $map_news ) : home_url( '/' );
	$map_prev   = get_previous_post();
	$map_next   = get_next_post();
	$map_gallery = map_field( 'news_gallery', get_the_ID(), array() );
	?>

	<article class="section article">
		<div class="container container--narrow">
			<nav class="crumbs" aria-label="<?php esc_attr_e( 'Хлебные крошки', 'musicartplus' ); ?>">
				<a href="<?php echo esc_url( home_url( '/' ) ); ?>"><?php esc_html_e( 'Главная', 'musicartplus' ); ?></a>
				<?php map_the_icon( 'ar' ); ?>
				<a href="<?php echo esc_url( $map_link ); ?>"><?php esc_html_e( 'Новости', 'musicartplus' ); ?></a>
				<?php if ( $map_badge ) : ?>
					<?php map_the_icon( 'ar' ); ?>
					<span><?php echo esc_html( $map_badge ); ?></span>
				<?php endif; ?>
			</nav>

			<?php if ( $map_badge ) : ?>
				<span class="chip"><?php echo esc_html( $map_badge ); ?></span>
			<?php endif; ?>

			<h1><?php the_title(); ?></h1>

			<div class="article__meta">
				<time datetime="<?php echo esc_attr( get_the_date( 'c' ) ); ?>">
					<?php
					printf(
						'%1$s %2$s %3$s г.',
						esc_html( get_the_date( 'j' ) ),
						esc_html( map_month_genitive( (int) get_the_date( 'n' ) ) ),
						esc_html( get_the_date( 'Y' ) )
					);
					?>
				</time>
			</div>

			<?php if ( $map_cover ) : ?>
				<figure class="article__cover reveal">
					<img src="<?php echo esc_url( $map_cover ); ?>" alt="<?php echo esc_attr( get_the_title() ); ?>" fetchpriority="high">
				</figure>
			<?php endif; ?>

			<div class="article__body"><?php the_content(); ?></div>

			<?php map_the_article_video(); ?>

			<?php if ( $map_gallery ) : ?>
				<div class="article__gallery">
					<?php foreach ( (array) $map_gallery as $map_item ) : ?>
						<?php $map_gid = is_array( $map_item ) ? $map_item['ID'] : $map_item; ?>
						<figure class="article__shot">
							<?php echo wp_get_attachment_image( $map_gid, 'map-card', false, array( 'loading' => 'lazy' ) ); ?>
						</figure>
					<?php endforeach; ?>
				</div>
			<?php endif; ?>

			<div class="article__foot">
				<a class="link-arrow link-arrow--back" href="<?php echo esc_url( $map_link ); ?>"><?php map_the_icon( 'al' ); ?><?php esc_html_e( 'Все новости', 'musicartplus' ); ?></a>
				<div class="share">
					<span><?php esc_html_e( 'Поделиться', 'musicartplus' ); ?></span>
					<a data-share="tg" href="#" target="_blank" rel="noopener" aria-label="<?php esc_attr_e( 'Поделиться в Telegram', 'musicartplus' ); ?>"><?php map_the_icon( 'tg' ); ?></a>
					<a data-share="vk" href="#" target="_blank" rel="noopener" aria-label="<?php esc_attr_e( 'Поделиться во ВКонтакте', 'musicartplus' ); ?>"><?php map_the_icon( 'vk' ); ?></a>
					<button data-share="copy" type="button" aria-label="<?php esc_attr_e( 'Скопировать ссылку', 'musicartplus' ); ?>"><?php map_the_icon( 'copy' ); ?></button>
				</div>
			</div>

			<div class="prevnext">
				<?php if ( $map_prev ) : ?>
					<div class="prevnext--prev">
						<a href="<?php echo esc_url( get_permalink( $map_prev ) ); ?>"><?php map_the_icon( 'al' ); ?><span><span class="prevnext__dir"><?php esc_html_e( 'Предыдущая', 'musicartplus' ); ?></span><b><?php echo esc_html( get_the_title( $map_prev ) ); ?></b></span></a>
					</div>
				<?php else : ?>
					<div class="prevnext__stub"></div>
				<?php endif; ?>

				<?php if ( $map_next ) : ?>
					<div class="prevnext--next">
						<a href="<?php echo esc_url( get_permalink( $map_next ) ); ?>"><?php map_the_icon( 'ar' ); ?><span><span class="prevnext__dir"><?php esc_html_e( 'Следующая', 'musicartplus' ); ?></span><b><?php echo esc_html( get_the_title( $map_next ) ); ?></b></span></a>
					</div>
				<?php else : ?>
					<div class="prevnext__stub"></div>
				<?php endif; ?>
			</div>
		</div>
	</article>

	<?php
	$map_others = get_posts( array(
		'posts_per_page' => max( 1, (int) map_blog_field( 'news_related_count', 3 ) ),
		'post__not_in'   => array( get_the_ID() ),
		'no_found_rows'  => true,
	) );

	if ( $map_others ) :
		?>
		<section class="section section--cream">
			<div class="container">
				<div class="sec-head">
					<div class="sec-head__text">
						<span class="eyebrow"><?php echo esc_html( map_blog_field( 'news_related_eyebrow', __( 'Читайте также', 'musicartplus' ) ) ); ?></span>
						<h2 class="h2"><?php echo esc_html( map_blog_field( 'news_related_title', __( 'Другие новости центра', 'musicartplus' ) ) ); ?></h2>
					</div>
					<a class="link-arrow" href="<?php echo esc_url( $map_link ); ?>"><?php esc_html_e( 'Все новости', 'musicartplus' ); ?><?php map_the_icon( 'ar' ); ?></a>
				</div>
				<div class="grid g-3">
					<?php foreach ( $map_others as $map_i => $map_other ) : ?>
						<?php map_news_card( $map_other, $map_i ); ?>
					<?php endforeach; ?>
				</div>
			</div>
		</section>
		<?php
	endif;

	map_cta_band(
		map_blog_field( 'news_cta_title', __( 'Хотите так же?', 'musicartplus' ) ),
		map_blog_field( 'news_cta_text', __( 'Приходите на пробный урок — покажем центр, познакомим с педагогом и расскажем о программе.', 'musicartplus' ) )
	);

endwhile;

get_footer();
