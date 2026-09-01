<?php
/**
 * Уведомления о заявке: письмо и Telegram.
 *
 * Заявка в первую очередь уходит в CRM, но менеджер не сидит в CRM целый день.
 * Поэтому о каждой заявке сайт сообщает ещё двумя путями — на почту и в чат.
 * Ни один из них не должен ронять приём заявки: посетителю уже ответили
 * «спасибо», и ошибка доставки — наше дело, а не его.
 *
 * @package MusicArtPlus
 */

defined( 'ABSPATH' ) || exit;

/**
 * Сколько раз пробуем достучаться до Telegram, прежде чем сдаться.
 */
const MAP_TG_ATTEMPTS = 8;

/**
 * Рассылает уведомления о новой заявке.
 *
 * @param array $lead    Данные заявки.
 * @param int   $lead_id ID записи заявки.
 * @param bool  $synced  Ушла ли заявка в CRM.
 * @return void
 */
function map_notify_lead( $lead, $lead_id, $synced ) {
	map_notify_email( $lead, $lead_id, $synced );
	map_notify_telegram( $lead, $lead_id, $synced );
}

/**
 * Заявка в виде пар «подпись — значение».
 *
 * Один источник и для письма, и для сообщения в чат: иначе они разъезжаются,
 * и в чате не хватает того, что есть в письме.
 *
 * @param array $lead    Данные заявки.
 * @param int   $lead_id ID записи заявки.
 * @param bool  $synced  Ушла ли заявка в CRM.
 * @return array<int,array{0:string,1:string}>
 */
function map_lead_summary( $lead, $lead_id, $synced ) {
	$fields = array(
		'name'      => __( 'Имя', 'musicartplus' ),
		'phone'     => __( 'Телефон', 'musicartplus' ),
		'email'     => __( 'Почта', 'musicartplus' ),
		'direction' => __( 'Направление', 'musicartplus' ),
		'teacher'   => __( 'Педагог', 'musicartplus' ),
		'comment'   => __( 'Комментарий', 'musicartplus' ),
		'page'      => __( 'Страница', 'musicartplus' ),
	);

	$rows = array();

	foreach ( $fields as $key => $label ) {
		if ( ! empty( $lead[ $key ] ) ) {
			$rows[] = array( $label, (string) $lead[ $key ] );
		}
	}

	if ( ! empty( $lead['utms'] ) && is_array( $lead['utms'] ) ) {
		$utms = array();

		foreach ( $lead['utms'] as $key => $value ) {
			$utms[] = $key . '=' . $value;
		}

		$rows[] = array( __( 'Метки', 'musicartplus' ), implode( ' ', $utms ) );
	}

	$rows[] = array(
		__( 'CRM', 'musicartplus' ),
		$synced
			? __( 'заявка передана в «Мой класс»', 'musicartplus' )
			: __( 'пока не передана — сайт повторит попытку сам', 'musicartplus' ),
	);

	if ( $lead_id ) {
		$rows[] = array(
			__( 'Карточка заявки', 'musicartplus' ),
			admin_url( 'post.php?post=' . (int) $lead_id . '&action=edit' ),
		);
	}

	return $rows;
}

/**
 * Адреса, на которые уходит письмо о заявке.
 *
 * @return string[]
 */
function map_notify_recipients() {
	$list = array( (string) map_opt( 'notify_email' ) );

	foreach ( preg_split( '/[,;\s]+/', (string) map_opt( 'notify_email_extra' ) ) as $extra ) {
		$list[] = $extra;
	}

	$list = array_values( array_unique( array_filter( array_map( 'sanitize_email', $list ), 'is_email' ) ) );

	// Ни одного адреса — пишем администратору сайта: потерять заявку хуже,
	// чем прислать письмо не тому.
	if ( ! $list ) {
		$admin = sanitize_email( get_option( 'admin_email' ) );

		if ( is_email( $admin ) ) {
			$list[] = $admin;
		}
	}

	/**
	 * Кому уходит письмо о новой заявке.
	 *
	 * @param string[] $list Адреса.
	 */
	return apply_filters( 'map_notify_recipients', $list );
}

/**
 * Письмо о новой заявке.
 *
 * @param array $lead    Данные заявки.
 * @param int   $lead_id ID записи заявки.
 * @param bool  $synced  Ушла ли заявка в CRM.
 * @return void
 */
function map_notify_email( $lead, $lead_id, $synced ) {
	$to = map_notify_recipients();

	if ( ! $to ) {
		return;
	}

	$lines = array();

	foreach ( map_lead_summary( $lead, $lead_id, $synced ) as $row ) {
		$lines[] = $row[0] . ': ' . $row[1];
	}

	$sent = wp_mail(
		$to,
		sprintf(
			/* translators: 1 — название сайта, 2 — имя посетителя. */
			__( '[%1$s] Заявка с сайта: %2$s', 'musicartplus' ),
			get_bloginfo( 'name' ),
			$lead['name']
		),
		implode( "\n", $lines )
	);

	// Письмо чаще всего не уходит из-за сервера, а не из-за заявки. Отметку
	// видно в карточке — иначе о молчащей почте узнают по пропавшим заявкам.
	if ( $lead_id ) {
		update_post_meta( $lead_id, '_map_mail_status', $sent ? 'sent' : 'failed' );
	}
}

/**
 * Токен бота.
 *
 * Как и ключ CRM, надёжнее держать его константой в wp-config.php: тогда он
 * не попадает ни в дамп базы, ни в резервную копию.
 *
 * @return string
 */
function map_tg_token() {
	if ( defined( 'MAP_TG_BOT_TOKEN' ) && MAP_TG_BOT_TOKEN ) {
		return trim( (string) MAP_TG_BOT_TOKEN );
	}

	return trim( (string) map_opt( 'tg_token' ) );
}

/**
 * Чаты, куда уходит заявка.
 *
 * @return string[]
 */
function map_tg_chats() {
	$rows = map_opt( 'tg_chats' );
	$out  = array();

	foreach ( (array) $rows as $row ) {
		$id = is_array( $row ) && isset( $row['chat_id'] ) ? $row['chat_id'] : $row;
		// ID группы начинается с минуса, у канала вместо номера бывает @имя.
		$id = preg_replace( '/[^0-9@_a-zA-Z-]/', '', (string) $id );

		if ( '' !== $id && ! in_array( $id, $out, true ) ) {
			$out[] = $id;
		}
	}

	return $out;
}

/**
 * Настроена ли отправка в Telegram.
 *
 * @return bool
 */
function map_tg_ready() {
	return map_opt( 'tg_enabled' ) && map_tg_token() && map_tg_chats();
}

/**
 * Прячет токен в тексте ошибки.
 *
 * Адрес запроса к Telegram содержит токен целиком, а текст ошибки уходит в
 * карточку заявки и на экран настроек.
 *
 * @param string $text Текст.
 * @return string
 */
function map_tg_hide_token( $text ) {
	$token = map_tg_token();

	return $token ? str_replace( $token, '…', (string) $text ) : (string) $text;
}

/**
 * Экранирует текст для разметки Telegram.
 *
 * Телеграм понимает лишь горстку тегов и требует, чтобы всё остальное было
 * экранировано вручную. esc_html() тут не годится: он превращает кавычки в
 * числовые сущности, которые Телеграм показывает как есть.
 *
 * @param string $text Текст.
 * @return string
 */
function map_tg_escape( $text ) {
	return str_replace( array( '&', '<', '>' ), array( '&amp;', '&lt;', '&gt;' ), (string) $text );
}

/**
 * Сообщение о заявке для чата.
 *
 * @param array $lead    Данные заявки.
 * @param int   $lead_id ID записи заявки.
 * @param bool  $synced  Ушла ли заявка в CRM.
 * @return string
 */
function map_tg_lead_message( $lead, $lead_id, $synced ) {
	$lines = array( '<b>' . map_tg_escape( __( 'Новая заявка с сайта', 'musicartplus' ) ) . '</b>' );

	foreach ( map_lead_summary( $lead, $lead_id, $synced ) as $row ) {
		$lines[] = map_tg_escape( $row[0] ) . ': ' . map_tg_escape( $row[1] );
	}

	return implode( "\n", $lines );
}

/**
 * Отправляет сообщение в один чат.
 *
 * @param string $chat ID чата.
 * @param string $text Текст с разметкой Telegram.
 * @return true|WP_Error
 */
function map_tg_send( $chat, $text ) {
	$token = map_tg_token();

	if ( ! $token ) {
		return new WP_Error( 'map_tg_token', __( 'Токен бота не задан.', 'musicartplus' ) );
	}

	if ( '' === (string) $chat ) {
		return new WP_Error( 'map_tg_chat', __( 'Не указан чат.', 'musicartplus' ) );
	}

	$response = wp_remote_post(
		'https://api.telegram.org/bot' . $token . '/sendMessage',
		array(
			'timeout' => 10,
			'body'    => array(
				'chat_id'                  => $chat,
				'text'                     => $text,
				'parse_mode'               => 'HTML',
				'disable_web_page_preview' => 'true',
			),
		)
	);

	if ( is_wp_error( $response ) ) {
		return new WP_Error( 'map_tg_http', map_tg_hide_token( $response->get_error_message() ) );
	}

	$code = (int) wp_remote_retrieve_response_code( $response );
	$body = json_decode( wp_remote_retrieve_body( $response ), true );

	if ( 200 === $code && ! empty( $body['ok'] ) ) {
		return true;
	}

	$desc = isset( $body['description'] ) ? $body['description'] : __( 'ответ без описания', 'musicartplus' );

	return new WP_Error(
		'map_tg_api',
		sprintf(
			/* translators: 1 — ответ Telegram, 2 — код ответа. */
			__( 'Telegram: %1$s (код %2$d)', 'musicartplus' ),
			map_tg_hide_token( $desc ),
			$code
		)
	);
}

/**
 * Рассылает сообщение во все чаты.
 *
 * @param string   $text Текст сообщения.
 * @param string[] $done Чаты, в которые сообщение уже ушло, — их пропускаем,
 *                       иначе при повторе получится два одинаковых сообщения.
 * @return array{sent:string[],failed:array<string,string>}
 */
function map_tg_broadcast( $text, $done = array() ) {
	$out = array( 'sent' => array(), 'failed' => array() );

	foreach ( map_tg_chats() as $chat ) {
		if ( in_array( $chat, (array) $done, true ) ) {
			continue;
		}

		$result = map_tg_send( $chat, $text );

		if ( is_wp_error( $result ) ) {
			$out['failed'][ $chat ] = $result->get_error_message();
		} else {
			$out['sent'][] = $chat;
		}
	}

	return $out;
}

/**
 * Чаты, в которые заявка уже ушла.
 *
 * Пустая мета возвращается строкой, и без этой проверки в списке заведётся
 * пустой «чат».
 *
 * @param int $lead_id ID записи заявки.
 * @return string[]
 */
function map_tg_delivered( $lead_id ) {
	$done = $lead_id ? get_post_meta( $lead_id, '_map_tg_sent', true ) : array();

	return is_array( $done ) ? array_values( array_filter( $done ) ) : array();
}

/**
 * Отправляет заявку в чаты и запоминает, чем это кончилось.
 *
 * @param array $lead    Данные заявки.
 * @param int   $lead_id ID записи заявки.
 * @param bool  $synced  Ушла ли заявка в CRM.
 * @return void
 */
function map_notify_telegram( $lead, $lead_id, $synced ) {
	if ( ! map_tg_ready() ) {
		return;
	}

	$done   = map_tg_delivered( $lead_id );
	$result = map_tg_broadcast( map_tg_lead_message( $lead, $lead_id, $synced ), $done );

	if ( $lead_id ) {
		update_post_meta( $lead_id, '_map_tg_sent', array_values( array_unique( array_merge( $done, $result['sent'] ) ) ) );
		update_post_meta( $lead_id, '_map_tg_status', $result['failed'] ? 'pending' : 'sent' );

		if ( $result['failed'] ) {
			update_post_meta( $lead_id, '_map_tg_error', implode( '; ', $result['failed'] ) );
		} else {
			delete_post_meta( $lead_id, '_map_tg_error' );
		}
	}

	if ( $result['failed'] ) {
		map_schedule_notify_retry();
	}
}

/**
 * Ставит в очередь повторную отправку в Telegram.
 *
 * @return void
 */
function map_schedule_notify_retry() {
	if ( ! wp_next_scheduled( 'map_retry_notify' ) ) {
		wp_schedule_single_event( time() + 10 * MINUTE_IN_SECONDS, 'map_retry_notify' );
	}
}

/**
 * Сколько заявок ждёт отправки в Telegram.
 *
 * @return int
 */
function map_pending_notify_count() {
	$query = new WP_Query( array(
		'post_type'      => 'map_lead',
		'post_status'    => 'private',
		'posts_per_page' => 1,
		'fields'         => 'ids',
		'meta_key'       => '_map_tg_status', // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_key
		'meta_value'     => 'pending',        // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_value
	) );

	return (int) $query->found_posts;
}

/**
 * Досылает в Telegram заявки, которые не ушли с первого раза.
 *
 * @return void
 */
function map_retry_notify() {
	if ( ! map_tg_ready() ) {
		// Бота ещё не настроили. Заявки ждут в очереди, но проверять каждые
		// десять минут незачем — заглянем через час.
		if ( map_pending_notify_count() > 0 ) {
			wp_schedule_single_event( time() + HOUR_IN_SECONDS, 'map_retry_notify' );
		}

		return;
	}

	$pending = get_posts( array(
		'post_type'      => 'map_lead',
		'post_status'    => 'private',
		'posts_per_page' => 20,
		'meta_key'       => '_map_tg_status',
		'meta_value'     => 'pending',
		'no_found_rows'  => true,
	) );

	foreach ( $pending as $post ) {
		$lead   = map_lead_from_post( $post->ID );
		$synced = 'sent' === get_post_meta( $post->ID, '_map_crm_status', true );
		$done   = map_tg_delivered( $post->ID );
		$result = map_tg_broadcast( map_tg_lead_message( $lead, $post->ID, $synced ), $done );

		update_post_meta( $post->ID, '_map_tg_sent', array_values( array_unique( array_merge( $done, $result['sent'] ) ) ) );

		if ( ! $result['failed'] ) {
			update_post_meta( $post->ID, '_map_tg_status', 'sent' );
			delete_post_meta( $post->ID, '_map_tg_error' );

			continue;
		}

		$attempts = (int) get_post_meta( $post->ID, '_map_tg_attempts', true ) + 1;

		update_post_meta( $post->ID, '_map_tg_attempts', $attempts );
		update_post_meta( $post->ID, '_map_tg_error', implode( '; ', $result['failed'] ) );

		// Чат могли удалить или бота из него выгнать — тогда попытки бесполезны.
		if ( $attempts >= MAP_TG_ATTEMPTS ) {
			update_post_meta( $post->ID, '_map_tg_status', 'failed' );
		}
	}

	if ( map_pending_notify_count() > 0 ) {
		wp_schedule_single_event( time() + HOUR_IN_SECONDS, 'map_retry_notify' );
	}
}
add_action( 'map_retry_notify', 'map_retry_notify' );

/**
 * Пробное сообщение — проверить бота, не дожидаясь заявки.
 *
 * @return array{sent:string[],failed:array<string,string>}
 */
function map_tg_test() {
	$text = sprintf(
		"<b>%s</b>\n%s",
		map_tg_escape( __( 'Проверка связи', 'musicartplus' ) ),
		map_tg_escape( sprintf(
			/* translators: %s — название сайта. */
			__( 'Так в этот чат будут приходить заявки с сайта «%s».', 'musicartplus' ),
			get_bloginfo( 'name' )
		) )
	);

	return map_tg_broadcast( $text );
}
