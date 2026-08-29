<?php
/**
 * Блок направлений на главной.
 *
 * @package MusicArtPlus
 */

defined( 'ABSPATH' ) || exit;

// Переключатель «Показывать на главной» в карточке направления.
// Если его никто не включил (свежая установка), показываем первые восемь —
// иначе блок на главной оказался бы пустым.
$map_dirs = map_get_featured_directions( 8 );

if ( ! $map_dirs ) {
	return;
}

$map_dir_page = map_page_by_template( 'page-directions.php' );
?>
<section class="section" id="directions">
	<?php map_deco( 'star' ); ?>
	<div class="container">
		<div class="sec-head sec-head--center">
			<div class="sec-head__text">
				<span class="eyebrow"><?php esc_html_e( 'Наши направления', 'musicartplus' ); ?></span>
				<h2 class="h2"><?php echo esc_html( sprintf( _n( '%d путь к искусству', '%d путей к искусству', count( $map_dirs ), 'musicartplus' ), count( $map_dirs ) ) ); ?></h2>
				<p class="sec-head__desc"><?php esc_html_e( 'Инструменты, вокал, сцена и живопись — можно выбрать одно направление или собрать своё сочетание.', 'musicartplus' ); ?></p>
			</div>
		</div>

		<div class="dir-mini-grid">
			<?php foreach ( $map_dirs as $map_i => $map_dir ) : ?>
				<a class="dir-mini reveal" data-delay="<?php echo (int) ( $map_i % 4 ); ?>" href="<?php echo esc_url( map_direction_link( $map_dir ) ); ?>">
					<span class="dir-mini__ico"><?php map_the_icon( map_field( 'dir_icon', $map_dir->ID, 'note' ) ); ?></span>
					<span class="dir-mini__body">
						<b><?php echo esc_html( get_the_title( $map_dir ) ); ?></b>
						<span><?php echo esc_html( map_field( 'dir_age', $map_dir->ID ) ); ?></span>
					</span>
				</a>
			<?php endforeach; ?>
		</div>

		<?php if ( $map_dir_page ) : ?>
			<div class="flex-center mt-l">
				<a class="btn btn--gold" href="<?php echo esc_url( get_permalink( $map_dir_page ) ); ?>"><?php esc_html_e( 'Посмотреть все направления', 'musicartplus' ); ?><?php map_the_icon( 'ar', 'btn__ico' ); ?></a>
			</div>
		<?php endif; ?>
	</div>
</section>
