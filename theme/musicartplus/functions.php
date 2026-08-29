<?php
/**
 * Точка входа темы.
 *
 * Вся логика вынесена в inc.php и папку inc/ — здесь только подключение,
 * чтобы файл оставался читаемым и не разрастался.
 *
 * @package MusicArtPlus
 */

defined( 'ABSPATH' ) || exit;

define( 'MAP_VERSION', '1.0.0' );
define( 'MAP_DIR', get_template_directory() );
define( 'MAP_URI', get_template_directory_uri() );

require_once MAP_DIR . '/inc.php';
