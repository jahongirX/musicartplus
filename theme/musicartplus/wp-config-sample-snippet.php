<?php
/**
 * Что дописать в wp-config.php на сервере.
 *
 * Скопируйте строки ниже в wp-config.php ВЫШЕ строки
 * require_once ABSPATH . 'wp-settings.php';
 *
 * Этот файл — образец. Настоящий ключ в репозиторий не коммитится.
 *
 * @package MusicArtPlus
 */

// Ключ API CRM «Мой класс». Берётся в «Мой класс» → Настройки → API.
define( 'MOYKLASS_API_KEY', 'сюда-ключ' );

// Полезно на боевом сервере: запрет правки файлов темы через админку.
define( 'DISALLOW_FILE_EDIT', true );

// Журнал ошибок в файл, а не на экран посетителю.
define( 'WP_DEBUG', false );
define( 'WP_DEBUG_LOG', true );
define( 'WP_DEBUG_DISPLAY', false );
