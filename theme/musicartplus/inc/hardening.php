<?php
/**
 * Базовая защита сайта.
 *
 * WordPress по умолчанию рассказывает о себе больше, чем нужно: отдаёт список
 * пользователей, версию ядра и держит открытым XML-RPC, через который идёт
 * основной поток перебора паролей и pingback-флуда. Сайт-визитка ничем из
 * этого не пользуется.
 *
 * Всё отключаемое здесь можно вернуть фильтрами — см. комментарии.
 *
 * @package MusicArtPlus
 */

defined( 'ABSPATH' ) || exit;

/**
 * Полностью закрывает XML-RPC.
 *
 * Фильтра xmlrpc_enabled мало: он отключает только методы, требующие входа,
 * а pingback.ping и system.multicall остаются доступны и используются для
 * усиления DDoS-атак на чужие сайты.
 *
 * Если понадобится приложение WordPress для телефона или Jetpack — верните
 * доступ: add_filter( 'map_allow_xmlrpc', '__return_true' );
 *
 * @return void
 */
function map_block_xmlrpc() {
	if ( apply_filters( 'map_allow_xmlrpc', false ) ) {
		return;
	}

	if ( ! defined( 'XMLRPC_REQUEST' ) || ! XMLRPC_REQUEST ) {
		return;
	}

	header( 'Content-Type: text/plain; charset=utf-8' );
	status_header( 403 );
	exit( 'XML-RPC отключён.' );
}
add_action( 'init', 'map_block_xmlrpc', 1 );

/**
 * Убирает методы pingback из списка доступных.
 *
 * @param array $methods Методы XML-RPC.
 * @return array
 */
function map_remove_pingback_methods( $methods ) {
	unset( $methods['pingback.ping'], $methods['pingback.extensions.getPingbacks'] );

	return $methods;
}
add_filter( 'xmlrpc_methods', 'map_remove_pingback_methods' );

/**
 * Закрывает перечисление пользователей.
 *
 * Адрес /?author=1 переадресует на страницу автора и тем самым выдаёт логин
 * администратора — половина работы для подбора пароля уже сделана.
 *
 * @return void
 */
function map_block_author_scan() {
	if ( is_admin() || ! isset( $_GET['author'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification.Recommended
		return;
	}

	// Числовой author в адресе — это сканер, а не посетитель.
	if ( is_numeric( (string) wp_unslash( $_GET['author'] ) ) ) { // phpcs:ignore WordPress.Security.NonceVerification.Recommended
		wp_safe_redirect( home_url( '/' ), 301 );
		exit;
	}
}
// Приоритет 0: штатный redirect_canonical висит на том же хуке с приоритетом 10
// и успевает увести на /author/логин/ раньше, чем мы вмешаемся.
add_action( 'template_redirect', 'map_block_author_scan', 0 );

/**
 * Прячет список пользователей в REST API от неавторизованных.
 *
 * Маршрут /wp-json/wp/v2/users открыт всем и отдаёт логины. Редактору и
 * администратору он по-прежнему доступен — ломать админку не нужно.
 *
 * @param WP_Error|null|true $result Текущий результат проверки.
 * @return WP_Error|null|true
 */
function map_restrict_users_endpoint( $result ) {
	if ( ! empty( $result ) ) {
		return $result;
	}

	$route = isset( $GLOBALS['wp']->query_vars['rest_route'] ) ? $GLOBALS['wp']->query_vars['rest_route'] : '';

	if ( ! $route ) {
		$route = isset( $_SERVER['REQUEST_URI'] ) ? sanitize_text_field( wp_unslash( $_SERVER['REQUEST_URI'] ) ) : '';
	}

	if ( false === strpos( $route, '/wp/v2/users' ) ) {
		return $result;
	}

	if ( current_user_can( 'list_users' ) ) {
		return $result;
	}

	return new WP_Error(
		'rest_user_cannot_view',
		__( 'Список пользователей закрыт.', 'musicartplus' ),
		array( 'status' => 401 )
	);
}
add_filter( 'rest_authentication_errors', 'map_restrict_users_endpoint' );

/**
 * Отключает архивы авторов.
 *
 * У сайта один автор — администратор, и страница /author/логин/ существует
 * только чтобы показать поисковикам его имя учётной записи.
 *
 * @return void
 */
function map_disable_author_archives() {
	if ( ! is_author() ) {
		return;
	}

	wp_safe_redirect( home_url( '/' ), 301 );
	exit;
}
add_action( 'template_redirect', 'map_disable_author_archives', 0 );

/**
 * Убирает авторов из карты сайта.
 *
 * @param object $provider Поставщик раздела карты сайта.
 * @param string $name     Имя раздела.
 * @return object|false
 */
function map_trim_sitemap( $provider, $name ) {
	return 'users' === $name ? false : $provider;
}
add_filter( 'wp_sitemaps_add_provider', 'map_trim_sitemap', 10, 2 );

/**
 * Убирает версию WordPress из адресов стилей и скриптов.
 *
 * По номеру версии подбирают известные уязвимости. Свои файлы темы это
 * не затрагивает — у них версия по времени изменения.
 *
 * @param string $src Адрес файла.
 * @return string
 */
function map_strip_core_version( $src ) {
	if ( $src && false !== strpos( $src, 'ver=' . get_bloginfo( 'version' ) ) ) {
		$src = remove_query_arg( 'ver', $src );
	}

	return $src;
}
add_filter( 'style_loader_src', 'map_strip_core_version', 9999 );
add_filter( 'script_loader_src', 'map_strip_core_version', 9999 );

/**
 * Одинаковый ответ при неверном логине и при неверном пароле.
 *
 * Стандартные сообщения WordPress подсказывают, существует ли такой
 * пользователь.
 *
 * @return string
 */
function map_generic_login_error() {
	return __( 'Неверное имя пользователя или пароль.', 'musicartplus' );
}
add_filter( 'login_errors', 'map_generic_login_error' );
