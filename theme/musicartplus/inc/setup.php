<?php
/**
 * Настройка темы: возможности WordPress, меню, размеры изображений.
 *
 * @package MusicArtPlus
 */

defined( 'ABSPATH' ) || exit;

/**
 * Объявляет поддерживаемые темой возможности.
 *
 * @return void
 */
function map_setup() {
	load_theme_textdomain( 'musicartplus', MAP_DIR . '/languages' );

	add_theme_support( 'title-tag' );
	add_theme_support( 'post-thumbnails' );
	add_theme_support( 'automatic-feed-links' );
	add_theme_support( 'responsive-embeds' );
	add_theme_support( 'custom-logo', array(
		'height'      => 103,
		'width'       => 176,
		'flex-height' => true,
		'flex-width'  => true,
	) );
	add_theme_support( 'html5', array(
		'search-form',
		'gallery',
		'caption',
		'style',
		'script',
		'navigation-widgets',
	) );

	register_nav_menus( array(
		'primary' => __( 'Основная навигация', 'musicartplus' ),
		'footer'  => __( 'Разделы в подвале', 'musicartplus' ),
		'dirs'    => __( 'Направления в подвале', 'musicartplus' ),
	) );

	// Карточки педагогов и новостей — фиксированные пропорции, чтобы не прыгала вёрстка.
	add_image_size( 'map-teacher', 800, 800, true );
	add_image_size( 'map-card', 760, 520, true );
	add_image_size( 'map-hero', 1920, 1200, true );
}
add_action( 'after_setup_theme', 'map_setup' );

/**
 * Ширина контента для встраиваемого медиа.
 *
 * @return void
 */
function map_content_width() {
	$GLOBALS['content_width'] = 1240;
}
add_action( 'after_setup_theme', 'map_content_width', 0 );

/**
 * Добавляет data-атрибуты страницы на <body>.
 *
 * Скрипты темы ориентируются на них так же, как в статической вёрстке.
 *
 * @param array $classes Классы body.
 * @return array
 */
function map_body_class( $classes ) {
	$classes[] = 'map';

	return $classes;
}
add_filter( 'body_class', 'map_body_class' );

/**
 * Значение атрибута data-page для <body>.
 *
 * @return string
 */
function map_body_page() {
	if ( is_front_page() ) {
		return 'home';
	}

	if ( is_singular( 'map_teacher' ) || is_post_type_archive( 'map_teacher' ) || is_page_template( 'page-teachers.php' ) ) {
		return 'teachers';
	}

	if ( is_page_template( 'page-directions.php' ) ) {
		return 'directions';
	}

	if ( is_page_template( 'page-about.php' ) ) {
		return 'about';
	}

	if ( is_home() || is_singular( 'post' ) || is_archive() ) {
		return 'news';
	}

	return 'page';
}

/**
 * Тип первого экрана: тёмный (фото на всю ширину) или светлый.
 *
 * @return string
 */
function map_body_hero() {
	return is_singular( 'post' ) ? 'light' : 'dark';
}

/**
 * Убирает приставку «Рубрика:» из заголовка архива.
 *
 * В шапке страницы уже есть хлебные крошки — приставка дублирует их и мешает
 * вёрстке заголовка.
 *
 * @param string $title Заголовок.
 * @return string
 */
function map_archive_title( $title ) {
	if ( is_category() || is_tag() || is_tax() ) {
		return single_term_title( '', false );
	}

	if ( is_author() ) {
		return get_the_author();
	}

	return $title;
}
add_filter( 'get_the_archive_title', 'map_archive_title' );

/**
 * Обновляет правила ссылок при активации темы.
 *
 * Тема добавляет свои типы записей, и без обновления правил их страницы
 * отдают 404, пока кто-нибудь не зайдёт в настройки постоянных ссылок.
 *
 * @return void
 */
function map_flush_rewrites() {
	map_register_post_types();
	flush_rewrite_rules();
}
add_action( 'after_switch_theme', 'map_flush_rewrites' );
