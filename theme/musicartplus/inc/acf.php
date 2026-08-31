<?php
/**
 * Настройка Advanced Custom Fields.
 *
 * Группы полей хранятся в acf-json/ рядом с темой. ACF сам пишет туда файл при
 * каждом сохранении группы в админке, поэтому правки редактора попадают в
 * репозиторий и переезжают на другие копии сайта вместе с темой.
 *
 * На копии, где групп ещё нет в базе (свежая установка, перенос на хостинг),
 * они подхватываются автоматически — см. map_acf_sync_json() ниже.
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
 * Убирает из файла группы путь к нему самому.
 *
 * ACF дописывает в JSON ключ local_file с абсолютным путём — своим на каждой
 * машине. В репозитории он создаёт ложные расхождения, а при загрузке ACF
 * подставляет его заново.
 *
 * @param array $post Группа полей.
 * @return array
 */
function map_acf_strip_local_file( $post ) {
	unset( $post['local_file'] );

	return $post;
}
add_filter( 'acf/prepare_field_group_for_export', 'map_acf_strip_local_file' );

/**
 * Переносит группы полей из JSON в базу, если их там ещё нет.
 *
 * Без этого на новой копии сайта список групп пуст, а поля не появляются на
 * экранах редактирования, пока администратор не нажмёт «Синхронизировать».
 * Группу, удалённую в админке, обратно не возвращает: ACF стирает и её файл.
 *
 * @return void
 */
function map_acf_sync_json() {
	if ( ! function_exists( 'acf_import_field_group' ) ) {
		return;
	}

	foreach ( acf_get_field_groups() as $group ) {
		if ( empty( $group['local'] ) || 'json' !== $group['local'] ) {
			continue;
		}

		if ( ! empty( $group['ID'] ) ) {
			// Группа в базе есть. Переносим заново, только если файл новее —
			// так правки, приехавшие с обновлением темы, доходят до админки
			// сами. После сохранения из админки время в файле и в базе
			// совпадает, поэтому свои правки этим не затрёт.
			$saved = (int) get_post_modified_time( 'U', true, $group['ID'] );

			if ( empty( $group['modified'] ) || (int) $group['modified'] <= $saved ) {
				continue;
			}
		}

		$group['fields'] = acf_get_fields( $group );

		acf_import_field_group( $group );
	}
}
add_action( 'admin_init', 'map_acf_sync_json' );

/**
 * Значок вкладки задаёт и стандартный «Значок сайта» WordPress.
 *
 * Иначе иконку пришлось бы менять в двух местах, а админка и экран входа
 * остались бы со старой.
 *
 * @param mixed  $value   Значение поля.
 * @param mixed  $post_id Куда сохраняется.
 * @param array  $field   Описание поля.
 * @return mixed
 */
function map_acf_sync_site_icon( $value, $post_id, $field ) {
	// ACF отдаёт сюда 'options' — в множественном числе.
	if ( 'option' !== $post_id && 'options' !== $post_id ) {
		return $value;
	}

	$id = (int) ( is_array( $value ) && isset( $value['ID'] ) ? $value['ID'] : $value );

	if ( $id !== (int) get_option( 'site_icon' ) ) {
		update_option( 'site_icon', $id );
		map_prepare_site_icon( $id );
	}

	return $value;
}

/**
 * Готовит нарезку значка сайта.
 *
 * Свои размеры для значка WordPress подмешивает только на экране настроек
 * внешнего вида. Если картинку назначили в обход него, обрезок не будет —
 * и в плитку приложения уедет полноразмерный файл.
 *
 * @param int $id ID вложения.
 * @return void
 */
function map_prepare_site_icon( $id ) {
	if ( ! $id ) {
		return;
	}

	require_once ABSPATH . 'wp-admin/includes/image.php';
	require_once ABSPATH . 'wp-admin/includes/class-wp-site-icon.php';

	$icon = new WP_Site_Icon();
	add_filter( 'intermediate_image_sizes_advanced', array( $icon, 'additional_sizes' ) );

	$file = get_attached_file( $id );

	if ( $file ) {
		wp_update_attachment_metadata( $id, wp_generate_attachment_metadata( $id, $file ) );
	}

	remove_filter( 'intermediate_image_sizes_advanced', array( $icon, 'additional_sizes' ) );
}
add_filter( 'acf/update_value/name=favicon', 'map_acf_sync_site_icon', 10, 3 );

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
 * Включает автозагрузку значений со страницы настроек.
 *
 * Без этого каждое поле настроек читается отдельным запросом к базе —
 * на странице выходит три десятка лишних SELECT. С автозагрузкой они
 * приезжают вместе с остальными опциями одним запросом.
 */
add_filter( 'acf/settings/autoload', '__return_true' );
