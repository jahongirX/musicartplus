<?php
/**
 * Ускорение фронтенда.
 *
 * WordPress по умолчанию грузит на каждой странице довольно много того, что
 * этой теме не нужно. Здесь всё лишнее отключается, а нужное — подсказывается
 * браузеру заранее.
 *
 * @package MusicArtPlus
 */

defined( 'ABSPATH' ) || exit;

/**
 * Убирает из <head> служебные теги, которыми тема не пользуется.
 *
 * @return void
 */
function map_clean_head() {
	remove_action( 'wp_head', 'rsd_link' );
	remove_action( 'wp_head', 'wlwmanifest_link' );
	remove_action( 'wp_head', 'wp_generator' );
	remove_action( 'wp_head', 'wp_shortlink_wp_head' );
	remove_action( 'wp_head', 'adjacent_posts_rel_link_wp_head' );
	remove_action( 'wp_head', 'rest_output_link_wp_head' );
	remove_action( 'wp_head', 'wp_oembed_add_discovery_links' );
	remove_action( 'template_redirect', 'wp_shortlink_header', 11 );
}
add_action( 'init', 'map_clean_head' );

/**
 * Отключает скрипт эмодзи.
 *
 * Это ~12 КБ JavaScript и лишний DNS-запрос к s.w.org на каждой странице;
 * системные эмодзи в современных браузерах отображаются и без него.
 *
 * @return void
 */
function map_disable_emoji() {
	remove_action( 'wp_head', 'print_emoji_detection_script', 7 );
	remove_action( 'admin_print_scripts', 'print_emoji_detection_script' );
	remove_action( 'wp_print_styles', 'print_emoji_styles' );
	remove_action( 'admin_print_styles', 'print_emoji_styles' );
	remove_filter( 'the_content_feed', 'wp_staticize_emoji' );
	remove_filter( 'comment_text_rss', 'wp_staticize_emoji' );
	remove_filter( 'wp_mail', 'wp_staticize_emoji_for_email' );

	add_filter( 'tiny_mce_plugins', function ( $plugins ) {
		return is_array( $plugins ) ? array_diff( $plugins, array( 'wpemoji' ) ) : array();
	} );

	add_filter( 'wp_resource_hints', function ( $urls, $relation ) {
		if ( 'dns-prefetch' !== $relation ) {
			return $urls;
		}

		return array_filter( $urls, function ( $url ) {
			return false === strpos( is_array( $url ) ? '' : $url, 's.w.org' );
		} );
	}, 10, 2 );
}
add_action( 'init', 'map_disable_emoji' );

/**
 * Снимает стили и скрипты, которые тема не использует.
 *
 * Стили редактора блоков подключаются только там, где контент реально собран
 * из блоков, — на большинстве страниц темы разметка своя.
 *
 * @return void
 */
function map_dequeue_unused() {
	if ( is_admin() ) {
		return;
	}

	wp_dequeue_script( 'wp-embed' );
	wp_deregister_script( 'wp-embed' );

	$needs_blocks = is_singular() && has_blocks( get_queried_object_id() );

	if ( ! $needs_blocks ) {
		wp_dequeue_style( 'wp-block-library' );
		wp_dequeue_style( 'wp-block-library-theme' );
		wp_dequeue_style( 'wc-blocks-style' );
		wp_dequeue_style( 'classic-theme-styles' );
	}
}
add_action( 'wp_enqueue_scripts', 'map_dequeue_unused', 100 );

/**
 * Убирает встроенные стили глобальных настроек оформления.
 *
 * У классической темы нет theme.json, и эти ~9 КБ вставляются в каждую
 * страницу впустую. На страницах, собранных из блоков, стили остаются.
 *
 * @return void
 */
function map_remove_global_styles() {
	if ( is_admin() ) {
		return;
	}

	if ( is_singular() && has_blocks( get_queried_object_id() ) ) {
		return;
	}

	remove_action( 'wp_enqueue_scripts', 'wp_enqueue_global_styles' );
	remove_action( 'wp_footer', 'wp_enqueue_global_styles', 1 );
	remove_action( 'wp_body_open', 'wp_global_styles_render_svg_filters' );
	remove_action( 'in_admin_header', 'wp_global_styles_render_svg_filters' );
}
add_action( 'wp', 'map_remove_global_styles' );

/**
 * Подсказывает браузеру заранее подключиться к внешним доменам.
 *
 * Шрифты у нас локальные, но виджет и плеер живут на чужих доменах —
 * ранний DNS+TLS экономит примерно четверть секунды на мобильной сети.
 *
 * @param array  $urls     Текущие подсказки.
 * @param string $relation Тип подсказки.
 * @return array
 */
function map_resource_hints( $urls, $relation ) {
	if ( 'preconnect' !== $relation ) {
		return $urls;
	}

	if ( map_page_has_widget() ) {
		$urls[] = array(
			'href'        => 'https://app.moyklass.com',
			'crossorigin' => 'anonymous',
		);
	}

	return $urls;
}
add_filter( 'wp_resource_hints', 'map_resource_hints', 10, 2 );

/**
 * Добавляет defer к скриптам темы.
 *
 * @param string $tag    Тег скрипта.
 * @param string $handle Идентификатор скрипта.
 * @return string
 */
function map_defer_scripts( $tag, $handle ) {
	$deferred = array( 'map-main', 'map-swiper' );

	if ( ! in_array( $handle, $deferred, true ) || false !== strpos( $tag, ' defer' ) ) {
		return $tag;
	}

	return str_replace( ' src=', ' defer src=', $tag );
}
add_filter( 'script_loader_tag', 'map_defer_scripts', 10, 2 );

/**
 * Снижает частоту heartbeat в админке.
 *
 * @param array $settings Настройки.
 * @return array
 */
function map_heartbeat( $settings ) {
	$settings['interval'] = 60;

	return $settings;
}
add_filter( 'heartbeat_settings', 'map_heartbeat' );

/**
 * Отключает XML-RPC: тема им не пользуется, а перебор паролей через него идёт постоянно.
 */
add_filter( 'xmlrpc_enabled', '__return_false' );

/**
 * Убирает ссылку на пингбэк из заголовков ответа.
 *
 * @param array $headers Заголовки.
 * @return array
 */
function map_remove_pingback_header( $headers ) {
	unset( $headers['X-Pingback'] );

	return $headers;
}
add_filter( 'wp_headers', 'map_remove_pingback_header' );

/**
 * Проставляет размеры и ленивую загрузку изображениям в контенте.
 *
 * Явные width/height убирают скачок вёрстки при подгрузке картинки.
 *
 * @param string $content Контент записи.
 * @return string
 */
function map_content_images( $content ) {
	if ( is_admin() || ! $content ) {
		return $content;
	}

	return wp_filter_content_tags( $content );
}
add_filter( 'the_content', 'map_content_images', 12 );

/**
 * Первое изображение на странице не должно грузиться лениво.
 *
 * Ленивая загрузка LCP-картинки откладывает её запрос и заметно ухудшает
 * показатель Largest Contentful Paint.
 *
 * @param string|bool $value Значение атрибута loading.
 * @param string      $image Разметка изображения.
 * @param string      $context Контекст вызова.
 * @return string|bool
 */
function map_skip_lazy_for_first( $value, $image, $context ) {
	static $seen = false;

	if ( 'the_content' !== $context || $seen ) {
		return $value;
	}

	$seen = true;

	return false;
}
add_filter( 'wp_img_tag_add_loading_attr', 'map_skip_lazy_for_first', 10, 3 );
