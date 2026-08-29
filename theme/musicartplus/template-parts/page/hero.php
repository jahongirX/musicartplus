<?php
/**
 * Первый экран внутренней страницы.
 *
 * @package MusicArtPlus
 *
 * @var array $args crumb, title, text, image.
 */

defined( 'ABSPATH' ) || exit;

$map_crumb = isset( $args['crumb'] ) ? $args['crumb'] : get_the_title();
$map_title = isset( $args['title'] ) ? $args['title'] : get_the_title();
$map_text  = isset( $args['text'] ) ? $args['text'] : '';
$map_image = isset( $args['image'] ) ? $args['image'] : '';

if ( ! $map_image && ! is_home() ) {
	$map_image = get_the_post_thumbnail_url( get_the_ID(), 'map-hero' );
}

if ( ! $map_image ) {
	$map_image = map_image_url(
		map_opt( 'hero_default_image' ),
		'map-hero',
		map_asset( 'assets/img/gallery/g07.jpg' )
	);
}
?>
<section class="page-hero<?php echo $map_image ? ' page-hero--photo' : ''; ?>">
	<?php if ( $map_image ) : ?>
		<div class="page-hero__bg">
			<?php // Фон первого экрана — крупнейший элемент страницы, грузим сразу. ?>
			<img src="<?php echo esc_url( $map_image ); ?>" alt="" fetchpriority="high" width="1600" height="900">
		</div>
	<?php endif; ?>

	<div class="container">
		<div class="page-hero__inner">
			<nav class="crumbs" aria-label="<?php esc_attr_e( 'Хлебные крошки', 'musicartplus' ); ?>">
				<a href="<?php echo esc_url( home_url( '/' ) ); ?>"><?php esc_html_e( 'Главная', 'musicartplus' ); ?></a>
				<?php map_the_icon( 'ar' ); ?>
				<span><?php echo esc_html( $map_crumb ); ?></span>
			</nav>
			<h1><?php echo esc_html( $map_title ); ?></h1>
			<?php if ( $map_text ) : ?>
				<p><?php echo esc_html( $map_text ); ?></p>
			<?php endif; ?>
		</div>
	</div>
</section>
