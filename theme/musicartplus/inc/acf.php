<?php
/**
 * Настройка Advanced Custom Fields.
 *
 * Группы полей объявлены кодом в acf-fields.php: так они лежат в репозитории,
 * одинаковы на всех копиях сайта и не требуют запросов к базе при отрисовке.
 *
 * @package MusicArtPlus
 */

defined( 'ABSPATH' ) || exit;

/**
 * Установлен ли ACF.
 *
 * @return bool
 */
function map_has_acf() {
	return function_exists( 'acf_add_local_field_group' );
}

/**
 * Куда ACF сохраняет JSON групп полей.
 *
 * Нужен для того, что заказчик заведёт руками: такие группы попадут в файлы
 * и переедут вместе с темой.
 *
 * @param string $path Текущий путь.
 * @return string
 */
function map_acf_json_save( $path ) {
	$dir = MAP_DIR . '/acf-json';

	if ( ! is_dir( $dir ) ) {
		wp_mkdir_p( $dir );
	}

	return $dir;
}
add_filter( 'acf/settings/save_json', 'map_acf_json_save' );

/**
 * Откуда ACF читает JSON групп полей.
 *
 * @param array $paths Текущие пути.
 * @return array
 */
function map_acf_json_load( $paths ) {
	$paths[] = MAP_DIR . '/acf-json';

	return $paths;
}
add_filter( 'acf/settings/load_json', 'map_acf_json_load' );

/**
 * Страница настроек сайта.
 *
 * @return void
 */
function map_acf_options_page() {
	if ( ! function_exists( 'acf_add_options_page' ) ) {
		return;
	}

	acf_add_options_page( array(
		'page_title' => __( 'Настройки сайта', 'musicartplus' ),
		'menu_title' => __( 'Настройки сайта', 'musicartplus' ),
		'menu_slug'  => 'map-settings',
		'capability' => 'manage_options',
		'position'   => 26,
		'icon_url'   => 'dashicons-admin-settings',
		'redirect'   => false,
		'autoload'   => true,
	) );
}
add_action( 'acf/init', 'map_acf_options_page' );

/**
 * Предупреждает, если ACF не установлен.
 *
 * Без него сайт работает и показывает значения по умолчанию, но редактировать
 * контент через админку не получится.
 *
 * @return void
 */
function map_acf_notice() {
	if ( map_has_acf() || ! current_user_can( 'activate_plugins' ) ) {
		return;
	}

	printf(
		'<div class="notice notice-warning"><p><strong>%s</strong> %s</p></div>',
		esc_html__( 'MusicArtPlus:', 'musicartplus' ),
		esc_html__( 'для редактирования контента нужен плагин Advanced Custom Fields PRO. Сайт работает и без него, но поля в админке не появятся.', 'musicartplus' )
	);
}
add_action( 'admin_notices', 'map_acf_notice' );

/**
 * Список иконок для полей выбора.
 *
 * @return array<string,string>
 */
function map_icon_choices() {
	return array(
		'piano'   => 'Фортепиано',
		'violin'  => 'Скрипка',
		'trumpet' => 'Труба',
		'mic'     => 'Микрофон',
		'masks'   => 'Театральные маски',
		'palette' => 'Палитра',
		'note'    => 'Нота',
		'child'   => 'Ребёнок',
		'users'   => 'Группа',
		'book'    => 'Книга',
		'online'  => 'Онлайн',
		'cap'     => 'Выпускная шапочка',
		'person'  => 'Человек',
		'chat'    => 'Диалог',
		'spark'   => 'Искра',
		'scale'   => 'Весы',
		'cal'     => 'Календарь',
		'heart'   => 'Сердце',
		'star'    => 'Звезда',
		'clock'   => 'Часы',
	);
}
