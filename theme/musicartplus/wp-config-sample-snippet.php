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

// Токен Telegram-бота, который присылает заявки в чат. Выдаёт @BotFather.
// Можно вписать и в «Настройки сайта» → «Уведомления о заявках», но здесь
// он не попадёт в дамп базы.
define( 'MAP_TG_BOT_TOKEN', 'сюда-токен' );

// Полезно на боевом сервере: запрет правки файлов темы через админку.
define( 'DISALLOW_FILE_EDIT', true );

// Журнал ошибок в файл, а не на экран посетителю.
define( 'WP_DEBUG', false );
define( 'WP_DEBUG_LOG', true );
define( 'WP_DEBUG_DISPLAY', false );
