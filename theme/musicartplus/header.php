<?php
/**
 * Шапка сайта.
 *
 * @package MusicArtPlus
 */

defined( 'ABSPATH' ) || exit;
?><!DOCTYPE html>
<html <?php language_attributes(); ?> class="no-js">
<head>
<script>document.documentElement.className="js"</script>
<meta charset="<?php bloginfo( 'charset' ); ?>">
<meta name="viewport" content="width=device-width, initial-scale=1">
<meta name="theme-color" content="#F3B71E">
<?php wp_head(); ?>
</head>
<body <?php body_class(); ?> data-page="<?php echo esc_attr( map_body_page() ); ?>" data-hero="<?php echo esc_attr( map_body_hero() ); ?>">
<?php wp_body_open(); ?>

<a class="skip-link screen-reader-text" href="#content"><?php esc_html_e( 'Перейти к содержимому', 'musicartplus' ); ?></a>

<header class="header">
	<div class="container header__inner">
		<a class="logo" href="<?php echo esc_url( home_url( '/' ) ); ?>" aria-label="<?php echo esc_attr( get_bloginfo( 'name' ) . ' — ' . __( 'на главную', 'musicartplus' ) ); ?>">
			<img class="logo__dark" src="<?php echo esc_url( map_asset( 'assets/img/ui/logo-color.png' ) ); ?>" alt="<?php echo esc_attr( get_bloginfo( 'name' ) ); ?>" width="176" height="103">
			<img class="logo__light" src="<?php echo esc_url( map_asset( 'assets/img/ui/logo-white.png' ) ); ?>" alt="" aria-hidden="true" width="176" height="103">
		</a>

		<nav class="nav" aria-label="<?php esc_attr_e( 'Основная навигация', 'musicartplus' ); ?>"><?php map_nav(); ?></nav>

		<div class="header__side">
			<a class="header__phone" href="tel:<?php echo esc_attr( map_phone_href() ); ?>"><?php echo esc_html( map_opt( 'phone' ) ); ?></a>
			<?php map_html( map_socials() ); ?>
			<a class="btn btn--gold btn--sm"<?php echo map_cta_attrs(); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- атрибуты экранируются внутри. ?>><?php esc_html_e( 'Записаться', 'musicartplus' ); ?></a>
			<button class="burger" type="button" aria-label="<?php esc_attr_e( 'Открыть меню', 'musicartplus' ); ?>" aria-expanded="false" aria-controls="mobile-menu">
				<span></span><span></span><span></span>
			</button>
		</div>
	</div>
</header>

<div class="mobile-menu" id="mobile-menu">
	<nav class="mobile-menu__nav" aria-label="<?php esc_attr_e( 'Мобильная навигация', 'musicartplus' ); ?>"><?php map_nav( true ); ?></nav>
	<div class="mobile-menu__foot">
		<a class="btn btn--gold btn--block"<?php echo map_cta_attrs(); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>><?php esc_html_e( 'Записаться на пробный урок', 'musicartplus' ); ?></a>
		<a class="header__phone" href="tel:<?php echo esc_attr( map_phone_href() ); ?>" style="font-size:22px;display:block"><?php echo esc_html( map_opt( 'phone' ) ); ?></a>
		<p class="muted" style="font-size:15px"><?php echo esc_html( map_opt( 'address' ) ); ?></p>
		<?php map_html( map_socials() ); ?>
	</div>
</div>

<main id="content">
