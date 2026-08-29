<?php
/**
 * Подключение стилей и скриптов.
 *
 * Версии файлов берутся из времени изменения — при правке ассета кэш
 * сбрасывается сам, а на проде не нужно ничего чистить руками.
 *
 * @package MusicArtPlus
 */

defined( 'ABSPATH' ) || exit;

/**
 * Версия ассета по времени изменения файла.
 *
 * @param string $rel Путь относительно папки темы.
 * @return string
 */
function map_asset_version( $rel ) {
	$path = MAP_DIR . '/' . ltrim( $rel, '/' );

	if ( file_exists( $path ) ) {
		return (string) filemtime( $path );
	}

	return MAP_VERSION;
}

/**
 * URL ассета темы.
 *
 * @param string $rel Путь относительно папки темы.
 * @return string
 */
function map_asset( $rel ) {
	return MAP_URI . '/' . ltrim( $rel, '/' );
}

/**
 * Нужен ли на текущей странице Swiper.
 *
 * Библиотека весит около 140 КБ — грузим её только там, где есть слайдеры.
 *
 * @return bool
 */
function map_page_needs_swiper() {
	return (bool) apply_filters(
		'map_page_needs_swiper',
		is_front_page() || is_page_template( array( 'page-teachers.php', 'page-about.php', 'page-directions.php' ) )
	);
}

/**
 * Нужен ли на текущей странице виджет расписания «Мой класс».
 *
 * @return bool
 */
function map_page_has_widget() {
	$default = is_front_page() || is_page_template( array( 'page-directions.php', 'page-teachers.php' ) );

	if ( ! map_opt( 'crm_widget_enabled', true ) ) {
		$default = false;
	}

	return (bool) apply_filters( 'map_page_has_widget', $default );
}

/**
 * Подключает ассеты фронтенда.
 *
 * @return void
 */
function map_enqueue_assets() {
	// Шрифты отдельным файлом: он маленький и меняется реже основного стиля.
	wp_enqueue_style( 'map-fonts', map_asset( 'assets/css/fonts.css' ), array(), map_asset_version( 'assets/css/fonts.css' ) );
	wp_enqueue_style( 'map-style', map_asset( 'assets/css/style.css' ), array( 'map-fonts' ), map_asset_version( 'assets/css/style.css' ) );

	if ( map_page_needs_swiper() ) {
		wp_enqueue_style( 'map-swiper', map_asset( 'assets/vendor/swiper/swiper-bundle.min.css' ), array(), '11.2.10' );
		wp_enqueue_script( 'map-swiper', map_asset( 'assets/vendor/swiper/swiper-bundle.min.js' ), array(), '11.2.10', true );
	}

	wp_enqueue_script( 'map-main', map_asset( 'assets/js/main.js' ), array(), map_asset_version( 'assets/js/main.js' ), true );

	wp_localize_script( 'map-main', 'MAP_CFG', map_js_config() );

	if ( is_singular() && comments_open() && get_option( 'thread_comments' ) ) {
		wp_enqueue_script( 'comment-reply' );
	}
}
add_action( 'wp_enqueue_scripts', 'map_enqueue_assets' );

/**
 * Данные, которые скрипты темы получают с сервера.
 *
 * Ключ API сюда не попадает: заявки уходят на свой REST-маршрут, а тот уже
 * обращается к «Моему классу» на стороне сервера.
 *
 * @return array<string,mixed>
 */
function map_js_config() {
	return array(
		'restUrl'  => esc_url_raw( rest_url( 'map/v1/' ) ),
		'nonce'    => wp_create_nonce( 'wp_rest' ),
		'crmUrl'   => map_crm_url(),
		'phone'    => map_opt( 'phone' ),
		'strings'  => array(
			'sending'   => __( 'Отправляем…', 'musicartplus' ),
			'error'     => __( 'Не удалось отправить. Позвоните нам, пожалуйста.', 'musicartplus' ),
			'success'   => __( 'Спасибо! Заявка принята — мы перезвоним в ближайшее рабочее время.', 'musicartplus' ),
		),
	);
}

/**
 * Предзагружает шрифты первого экрана.
 *
 * Без preload браузер узнаёт о шрифте только после разбора CSS, и заголовок
 * первого экрана заметное время рисуется системным шрифтом.
 *
 * @return void
 */
function map_preload_fonts() {
	$fonts = array(
		'assets/fonts/Ysabeau-cyrillic-normal.woff2',
		'assets/fonts/YsabeauSC-cyrillic-normal.woff2',
	);

	foreach ( $fonts as $font ) {
		if ( ! file_exists( MAP_DIR . '/' . $font ) ) {
			continue;
		}

		printf(
			'<link rel="preload" href="%s" as="font" type="font/woff2" crossorigin>' . "\n",
			esc_url( map_asset( $font ) )
		);
	}
}
add_action( 'wp_head', 'map_preload_fonts', 1 );

/**
 * Подключает скрипт виджета «Мой класс» перед закрытием body.
 *
 * @return void
 */
function map_widget_script() {
	if ( ! map_page_has_widget() ) {
		return;
	}

	printf(
		'<script src="https://app.moyklass.com/api/site/widget/?id=%s" charset="UTF-8" defer></script>' . "\n",
		esc_attr( map_widget_key() )
	);
}
add_action( 'wp_footer', 'map_widget_script', 20 );

/**
 * Стили экрана входа и админки, чтобы они не спорили с темой.
 *
 * @return void
 */
function map_admin_assets() {
	$rel = 'assets/css/admin.css';

	if ( file_exists( MAP_DIR . '/' . $rel ) ) {
		wp_enqueue_style( 'map-admin', map_asset( $rel ), array(), map_asset_version( $rel ) );
	}
}
add_action( 'admin_enqueue_scripts', 'map_admin_assets' );
