<?php
/**
 * Разовая настройка после обновления темы.
 *
 * Откройте файл в браузере, войдя в админку под администратором:
 *
 *     https://ваш-сайт/wp-content/themes/musicartplus/map-update.php
 *
 * Скрипт переносит новые поля в базу, переводит цифры на подсчёт, чистит кэш
 * и показывает, что получилось. Ничего не удаляет и не перезаписывает контент.
 * После запуска файл можно удалить кнопкой внизу страницы.
 *
 * Схему базы скрипт не трогает: новых таблиц и колонок обновление не требует.
 *
 * @package MusicArtPlus
 */

// Ядро ищем вверх по дереву — от папки самого файла, от пути, по которому его
// открыл сервер, и от корня сайта. Три точки отсчёта нужны затем, что тема
// бывает не на своём обычном месте, а иногда и вовсе символической ссылкой:
// тогда __DIR__ уводит совсем в другую ветку файловой системы.
$map_load  = '';
$map_start = array( __DIR__ );

if ( ! empty( $_SERVER['SCRIPT_FILENAME'] ) ) {
	$map_start[] = dirname( $_SERVER['SCRIPT_FILENAME'] ); // phpcs:ignore
}

if ( ! empty( $_SERVER['DOCUMENT_ROOT'] ) ) {
	$map_start[] = $_SERVER['DOCUMENT_ROOT']; // phpcs:ignore
}

foreach ( $map_start as $map_dir ) {
	for ( $map_i = 0; $map_i < 8; $map_i++ ) {
		if ( is_readable( $map_dir . '/wp-load.php' ) ) {
			$map_load = $map_dir . '/wp-load.php';
			break 2;
		}

		$map_up = dirname( $map_dir );

		if ( $map_up === $map_dir ) {
			break;
		}

		$map_dir = $map_up;
	}
}

if ( ! $map_load ) {
	header( 'Content-Type: text/plain; charset=utf-8' );
	exit( "Не нашёл wp-load.php. Положите файл внутрь папки темы на сайте.\n" );
}

require_once $map_load;

if ( ! is_user_logged_in() || ! current_user_can( 'manage_options' ) ) {
	status_header( 403 );
	header( 'Content-Type: text/html; charset=utf-8' );

	printf(
		'<p style="font:16px/1.5 system-ui;padding:40px">Сначала войдите в админку под администратором, потом откройте этот адрес снова. <a href="%s">Войти</a></p>',
		esc_url( wp_login_url( ( is_ssl() ? 'https://' : 'http://' ) . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'] ) ) // phpcs:ignore
	);

	exit;
}

/**
 * Строка отчёта.
 *
 * @param string $label Что проверяли.
 * @param string $value Что получилось.
 * @param string $state ok | warn | bad.
 * @return void
 */
function map_update_row( $label, $value, $state = 'ok' ) {
	$marks = array( 'ok' => '✓', 'warn' => '!', 'bad' => '✕' );

	printf(
		'<tr class="%s"><td class="m">%s</td><td class="l">%s</td><td>%s</td></tr>',
		esc_attr( $state ),
		esc_html( isset( $marks[ $state ] ) ? $marks[ $state ] : '' ),
		esc_html( $label ),
		wp_kses_post( $value )
	);
}

// Удаление файла — по кнопке внизу отчёта.
if ( ! empty( $_POST['map_selfdelete'] ) && check_admin_referer( 'map_update_delete' ) ) {
	$ok = @unlink( __FILE__ ); // phpcs:ignore WordPress.PHP.NoSilencedErrors.Discouraged

	header( 'Content-Type: text/html; charset=utf-8' );
	printf(
		'<p style="font:16px/1.5 system-ui;padding:40px">%s <a href="%s">В админку</a></p>',
		$ok
			? 'Файл удалён — обновление завершено.'
			: 'Удалить файл не вышло, снимите его вручную по FTP: wp-content/themes/musicartplus/map-update.php',
		esc_url( admin_url() )
	);

	exit;
}

header( 'Content-Type: text/html; charset=utf-8' );
?>
<!doctype html>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width,initial-scale=1">
<title>Обновление темы MusicArtPlus</title>
<style>
body{font:15px/1.6 system-ui,-apple-system,"Segoe UI",Roboto,sans-serif;margin:0;padding:40px 20px;background:#f6f7f7;color:#1d2327}
.wrap{max-width:840px;margin:0 auto;background:#fff;border:1px solid #dcdcde;border-radius:8px;padding:28px 32px}
h1{font-size:23px;margin:0 0 4px}
h2{font-size:17px;margin:32px 0 8px}
p.sub{color:#646970;margin:0 0 24px}
table{width:100%;border-collapse:collapse}
td{padding:8px 10px;border-bottom:1px solid #f0f0f1;vertical-align:top}
td.m{width:24px;text-align:center;font-weight:700}
td.l{width:270px;color:#50575e}
tr.ok td.m{color:#00a32a}tr.warn td.m{color:#dba617}tr.bad td.m{color:#d63638}
code{background:#f6f7f7;padding:1px 5px;border-radius:3px;font-size:13px}
.note{margin-top:28px;padding:14px 16px;background:#fcf9e8;border-left:4px solid #dba617;border-radius:0 4px 4px 0}
button{background:#d63638;color:#fff;border:0;border-radius:4px;padding:10px 18px;font-size:14px;cursor:pointer}
a{color:#2271b1}
</style>
<div class="wrap">
<h1>Обновление темы MusicArtPlus</h1>
<p class="sub">Проверка и разовые действия после загрузки новых файлов.</p>

<h2>Окружение</h2>
<table>
<?php
$map_theme  = wp_get_theme();
$map_active = 'musicartplus' === get_stylesheet();

map_update_row(
	'Активная тема',
	esc_html( $map_theme->get( 'Name' ) . ' ' . $map_theme->get( 'Version' ) ),
	$map_active ? 'ok' : 'bad'
);

if ( ! $map_active ) {
	map_update_row( 'Внимание', 'Активна другая тема — включите MusicArtPlus в «Внешний вид → Темы».', 'bad' );
}

map_update_row( 'WordPress', esc_html( get_bloginfo( 'version' ) ) );
map_update_row( 'PHP', esc_html( PHP_VERSION ), version_compare( PHP_VERSION, '7.4', '>=' ) ? 'ok' : 'bad' );

if ( function_exists( 'acf_get_setting' ) ) {
	map_update_row( 'Advanced Custom Fields', esc_html( acf_get_setting( 'version' ) ) );
} else {
	map_update_row( 'Advanced Custom Fields', 'плагин не найден — поля в админке не появятся', 'bad' );
}

$map_files = array( 'inc/notify.php', 'inc/helpers.php', 'acf-json/group_map_settings.json' );
$map_miss  = array();

foreach ( $map_files as $map_f ) {
	if ( ! is_readable( __DIR__ . '/' . $map_f ) ) {
		$map_miss[] = $map_f;
	}
}

map_update_row(
	'Файлы темы',
	$map_miss ? 'не загружены: ' . esc_html( implode( ', ', $map_miss ) ) : 'на месте',
	$map_miss ? 'bad' : 'ok'
);
?>
</table>

<h2>Что сделано</h2>
<table>
<?php
// 1. Поля из acf-json — в базу.
if ( function_exists( 'map_acf_sync_json' ) ) {
	map_acf_sync_json();
	map_update_row( 'Группы полей', 'перенесены из файлов темы в базу' );
} else {
	map_update_row( 'Группы полей', 'функция синхронизации не найдена — файлы темы загружены не полностью', 'bad' );
}

// 2. Цифры — на подсчёт.
if ( function_exists( 'map_upgrade_fact_sources' ) ) {
	map_upgrade_fact_sources();
	map_update_row( 'Цифры на страницах', 'переведены на подсчёт карточек' );
} else {
	map_update_row( 'Цифры на страницах', 'функция обновления не найдена', 'bad' );
}

// 3. Кэш и ссылки.
wp_cache_flush();
flush_rewrite_rules();
map_update_row( 'Кэш и постоянные ссылки', 'сброшены' );

if ( class_exists( 'MAP_Moyklass' ) ) {
	map_update_row( 'Кэш CRM', sprintf( 'очищено записей: %d', (int) MAP_Moyklass::flush_cache() ) );
}
?>
</table>

<h2>Цифры сейчас</h2>
<table>
<?php
if ( function_exists( 'map_fact_num' ) ) {
	$map_pages = array(
		array( (int) get_option( 'page_on_front' ), 'hero_facts', 'Главная' ),
		array( function_exists( 'map_page_by_template' ) ? (int) map_page_by_template( 'page-about.php' ) : 0, 'about_facts', 'О нас' ),
	);

	$map_names = function_exists( 'map_fact_sources' ) ? map_fact_sources() : array();

	foreach ( $map_pages as $map_p ) {
		list( $map_id, $map_field, $map_title ) = $map_p;

		if ( ! $map_id || ! function_exists( 'get_field' ) ) {
			map_update_row( $map_title, 'страница не найдена', 'warn' );
			continue;
		}

		foreach ( (array) get_field( $map_field, $map_id ) as $map_row ) {
			$map_src = isset( $map_row['source'] ) ? $map_row['source'] : '';

			map_update_row(
				$map_title,
				sprintf(
					'<strong>%s</strong> — %s <code>%s</code>',
					esc_html( map_fact_num( $map_row ) ),
					esc_html( isset( $map_row['label'] ) ? $map_row['label'] : '' ),
					esc_html( $map_src && isset( $map_names[ $map_src ] ) ? 'считается: ' . $map_names[ $map_src ] : 'вписано руками' )
				)
			);
		}
	}
} else {
	map_update_row( 'Цифры', 'подсчёт не подключён — проверьте inc/helpers.php', 'bad' );
}
?>
</table>

<h2>Уведомления о заявках</h2>
<table>
<?php
if ( function_exists( 'map_notify_recipients' ) ) {
	$map_to = map_notify_recipients();

	map_update_row(
		'Почта',
		$map_to ? esc_html( implode( ', ', $map_to ) ) : 'ни одного адреса',
		$map_to ? 'ok' : 'bad'
	);

	$map_tg_on    = (bool) map_opt( 'tg_enabled' );
	$map_tg_chats = map_tg_chats();
	$map_tg_token = map_tg_token();

	if ( ! $map_tg_on ) {
		map_update_row( 'Telegram', 'выключен — включается в «Настройки сайта → Уведомления о заявках»', 'warn' );
	} elseif ( ! $map_tg_token || ! $map_tg_chats ) {
		map_update_row( 'Telegram', 'включён, но не хватает токена или списка чатов', 'warn' );
	} else {
		map_update_row( 'Telegram', sprintf( 'работает, чатов: %d', count( $map_tg_chats ) ) );
	}

	map_update_row(
		'Проверка бота',
		sprintf(
			'кнопка «Проверить Telegram» — <a href="%s">Интеграция с CRM</a>',
			esc_url( admin_url( 'admin.php?page=map-crm' ) )
		),
		'ok'
	);
} else {
	map_update_row( 'Уведомления', 'модуль inc/notify.php не загружен', 'bad' );
}
?>
</table>

<div class="note">
	Файл рабочий только для администратора, но держать его на сайте незачем.
	Удалите его — кнопкой ниже или по FTP:
	<code>wp-content/themes/musicartplus/map-update.php</code>
</div>

<form method="post" style="margin-top:20px">
	<?php wp_nonce_field( 'map_update_delete' ); ?>
	<button type="submit" name="map_selfdelete" value="1">Удалить этот файл</button>
</form>
</div>
