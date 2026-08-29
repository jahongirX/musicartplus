<?php
/**
 * Блок «Как нас найти».
 *
 * @package MusicArtPlus
 */

defined( 'ABSPATH' ) || exit;

$map_embed = map_opt( 'map_embed' );

if ( ! $map_embed ) {
	// Запасной вариант: адрес из настроек на карте Яндекса.
	$map_embed = 'https://yandex.ru/map-widget/v1/?text=' . rawurlencode( map_opt( 'address' ) ) . '&z=17';
}
?>
<section class="section section--cream section--map" id="contacts">
	<?php map_deco( 'swirl' ); ?>
	<div class="container">
		<div class="sec-head sec-head--center">
			<div class="sec-head__text">
				<span class="eyebrow"><?php echo esc_html( map_home_field( 'contacts_eyebrow', 'Как нас найти' ) ); ?></span>
				<h2 class="h2"><?php echo esc_html( map_home_field( 'contacts_title', 'Мы рядом с метро Минская' ) ); ?></h2>
				<p class="sec-head__desc"><?php echo esc_html( map_home_field( 'contacts_text', 'Центр находится на закрытой территории — перед первым визитом позвоните, и мы встретим вас у входа.' ) ); ?></p>
			</div>
		</div>

		<div class="contact-info contact-info--row">
			<div class="ci reveal">
				<span class="ci__ico"><?php map_the_icon( 'pin' ); ?></span>
				<div>
					<b><?php esc_html_e( 'Адрес', 'musicartplus' ); ?></b>
					<span><?php echo esc_html( map_opt( 'address' ) ); ?></span>
					<?php if ( map_opt( 'address_note' ) ) : ?>
						<small><?php echo esc_html( map_opt( 'address_note' ) ); ?></small>
					<?php endif; ?>
				</div>
			</div>

			<div class="ci reveal" data-delay="1">
				<span class="ci__ico"><?php map_the_icon( 'phone' ); ?></span>
				<div>
					<b><?php esc_html_e( 'Телефон', 'musicartplus' ); ?></b>
					<a href="tel:<?php echo esc_attr( map_phone_href() ); ?>"><?php echo esc_html( map_opt( 'phone' ) ); ?></a>
					<small><?php echo esc_html( map_opt( 'work_hours', 'Пн–Вс, 10:00–20:00' ) ); ?></small>
				</div>
			</div>

			<div class="ci reveal" data-delay="2">
				<span class="ci__ico"><?php map_the_icon( 'mail' ); ?></span>
				<div>
					<b><?php esc_html_e( 'Почта', 'musicartplus' ); ?></b>
					<a href="mailto:<?php echo esc_attr( map_opt( 'email' ) ); ?>"><?php echo esc_html( map_opt( 'email' ) ); ?></a>
				</div>
			</div>

			<div class="ci reveal" data-delay="3">
				<span class="ci__ico"><?php map_the_icon( 'clock' ); ?></span>
				<div>
					<b><?php esc_html_e( 'Занятия', 'musicartplus' ); ?></b>
					<span><?php echo esc_html( map_opt( 'lessons_format', __( 'Очно в центре и онлайн', 'musicartplus' ) ) ); ?></span>
					<small><?php echo esc_html( map_opt( 'lessons_format_note', __( 'Некоторые педагоги проводят занятия на дому', 'musicartplus' ) ) ); ?></small>
				</div>
			</div>
		</div>
	</div>

	<div class="map-wrap map-wrap--full reveal">
		<iframe src="<?php echo esc_url( $map_embed ); ?>"
			title="<?php echo esc_attr( sprintf( __( '%s на карте', 'musicartplus' ), get_bloginfo( 'name' ) ) ); ?>"
			loading="lazy" allowfullscreen></iframe>
	</div>
</section>
