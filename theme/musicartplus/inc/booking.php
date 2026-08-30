<?php
/**
 * Онлайн-запись: приём заявки, отправка в CRM, уведомления.
 *
 * Порядок такой: сначала заявка сохраняется у себя, потом уходит в «Мой класс».
 * Если CRM в этот момент недоступна, посетитель всё равно видит подтверждение,
 * а заявка отправляется позже фоновой задачей — терять обращения нельзя.
 *
 * @package MusicArtPlus
 */

defined( 'ABSPATH' ) || exit;

/**
 * Максимум заявок с одного адреса за час.
 */
const MAP_RATE_LIMIT = 5;

/**
 * Обрабатывает заявку с сайта.
 *
 * @param array $input Сырые данные формы.
 * @return array|WP_Error Результат для ответа REST.
 */
function map_handle_booking( $input ) {
	$check = map_booking_guard( $input );

	if ( is_wp_error( $check ) ) {
		return $check;
	}

	$lead = map_sanitize_lead( $input );

	if ( is_wp_error( $lead ) ) {
		return $lead;
	}

	$lead_id = map_store_lead( $lead );

	// Заявка не записалась в базу — значит её не подхватит и повторная
	// отправка. Отвечать «принято» в таком случае нельзя.
	if ( ! $lead_id ) {
		return new WP_Error(
			'map_store_failed',
			__( 'Не удалось принять заявку. Позвоните нам, пожалуйста.', 'musicartplus' ),
			array( 'status' => 503 )
		);
	}

	map_bump_rate_limit();

	$crm = MAP_Moyklass::is_configured() ? MAP_Moyklass::create_lead( $lead ) : new WP_Error( 'map_mk_off', 'CRM не подключена' );

	if ( is_wp_error( $crm ) ) {
		update_post_meta( $lead_id, '_map_crm_status', 'pending' );
		update_post_meta( $lead_id, '_map_crm_error', $crm->get_error_message() );
		map_schedule_retry();
	} else {
		update_post_meta( $lead_id, '_map_crm_status', 'sent' );
		update_post_meta( $lead_id, '_map_crm_user_id', isset( $crm['id'] ) ? (int) $crm['id'] : 0 );
	}

	map_notify_admin( $lead, $lead_id, ! is_wp_error( $crm ) );

	/**
	 * Срабатывает после приёма заявки.
	 *
	 * @param array $lead    Данные заявки.
	 * @param int   $lead_id ID записи заявки.
	 * @param bool  $synced  Ушла ли заявка в CRM.
	 */
	do_action( 'map_booking_received', $lead, $lead_id, ! is_wp_error( $crm ) );

	return array(
		'ok'      => true,
		'id'      => $lead_id,
		'message' => __( 'Спасибо! Заявка принята — мы перезвоним в ближайшее рабочее время.', 'musicartplus' ),
	);
}

/**
 * Отсекает спам до всякой обработки.
 *
 * Ловушка для ботов, проверка скорости заполнения и ограничение по адресу
 * закрывают почти весь автоматический мусор без капчи.
 *
 * @param array $input Данные формы.
 * @return true|WP_Error
 */
function map_booking_guard( $input ) {
	// Скрытое поле, которое человек не видит и не заполняет.
	if ( ! empty( $input['website'] ) ) {
		return new WP_Error( 'map_spam', __( 'Заявка не принята.', 'musicartplus' ), array( 'status' => 400 ) );
	}

	// Форму нельзя заполнить осмысленно быстрее чем за пару секунд.
	$rendered = isset( $input['t'] ) ? (int) $input['t'] : 0;

	if ( $rendered && ( time() - $rendered ) < 2 ) {
		return new WP_Error( 'map_spam_fast', __( 'Заявка не принята.', 'musicartplus' ), array( 'status' => 400 ) );
	}

	// Галочка согласия проверяется в браузере, но запрос можно отправить и
	// напрямую. Без согласия персональные данные обрабатывать нельзя.
	if ( empty( $input['consent'] ) ) {
		return new WP_Error(
			'map_consent',
			__( 'Нужно согласие на обработку персональных данных.', 'musicartplus' ),
			array( 'status' => 422, 'field' => 'consent' )
		);
	}

	if ( map_rate_limit_hit() ) {
		return new WP_Error(
			'map_rate',
			__( 'Слишком много заявок подряд. Позвоните нам, пожалуйста.', 'musicartplus' ),
			array( 'status' => 429 )
		);
	}

	return true;
}

/**
 * Ключ ограничения по адресу посетителя.
 *
 * @return string
 */
function map_rate_key() {
	$ip = isset( $_SERVER['REMOTE_ADDR'] ) ? sanitize_text_field( wp_unslash( $_SERVER['REMOTE_ADDR'] ) ) : 'unknown';

	return 'map_rate_' . md5( $ip );
}

/**
 * Исчерпан ли лимит заявок.
 *
 * @return bool
 */
function map_rate_limit_hit() {
	return (int) get_transient( map_rate_key() ) >= MAP_RATE_LIMIT;
}

/**
 * Увеличивает счётчик заявок с адреса.
 *
 * @return void
 */
function map_bump_rate_limit() {
	$key = map_rate_key();
	$n   = (int) get_transient( $key );

	set_transient( $key, $n + 1, HOUR_IN_SECONDS );
}

/**
 * Проверяет и нормализует данные заявки.
 *
 * @param array $input Сырые данные.
 * @return array|WP_Error
 */
function map_sanitize_lead( $input ) {
	$name = isset( $input['name'] ) ? sanitize_text_field( $input['name'] ) : '';
	$name = trim( preg_replace( '/\s+/u', ' ', $name ) );

	if ( mb_strlen( $name ) < 2 ) {
		return new WP_Error( 'map_name', __( 'Укажите, пожалуйста, имя.', 'musicartplus' ), array( 'status' => 422, 'field' => 'name' ) );
	}

	$phone = map_normalize_phone( isset( $input['phone'] ) ? $input['phone'] : '' );

	if ( ! $phone ) {
		return new WP_Error( 'map_phone', __( 'Укажите телефон полностью.', 'musicartplus' ), array( 'status' => 422, 'field' => 'phone' ) );
	}

	$email = isset( $input['email'] ) ? sanitize_email( $input['email'] ) : '';

	if ( $email && ! is_email( $email ) ) {
		$email = '';
	}

	return array(
		'name'      => mb_substr( $name, 0, 120 ),
		'phone'     => $phone,
		'email'     => $email,
		'direction' => isset( $input['direction'] ) ? mb_substr( sanitize_text_field( $input['direction'] ), 0, 120 ) : '',
		'teacher'   => isset( $input['teacher'] ) ? mb_substr( sanitize_text_field( $input['teacher'] ), 0, 120 ) : '',
		'comment'   => isset( $input['comment'] ) ? mb_substr( sanitize_textarea_field( $input['comment'] ), 0, 1000 ) : '',
		'page'      => isset( $input['page'] ) ? esc_url_raw( $input['page'] ) : '',
		'utms'      => map_collect_utms( $input ),
	);
}

/**
 * Приводит телефон к формату +7XXXXXXXXXX.
 *
 * @param string $raw Введённый телефон.
 * @return string Пустая строка, если номер не похож на российский.
 */
function map_normalize_phone( $raw ) {
	$digits = preg_replace( '/\D+/', '', (string) $raw );

	if ( ! $digits ) {
		return '';
	}

	// 8XXXXXXXXXX и 7XXXXXXXXXX — один и тот же номер.
	if ( 11 === strlen( $digits ) && ( '8' === $digits[0] || '7' === $digits[0] ) ) {
		return '+7' . substr( $digits, 1 );
	}

	if ( 10 === strlen( $digits ) ) {
		return '+7' . $digits;
	}

	// Иностранные номера пропускаем как есть, если длина правдоподобная.
	if ( strlen( $digits ) >= 11 && strlen( $digits ) <= 15 ) {
		return '+' . $digits;
	}

	return '';
}

/**
 * Собирает UTM-метки из данных формы.
 *
 * @param array $input Данные формы.
 * @return array<string,string>
 */
function map_collect_utms( $input ) {
	$keys = array( 'utm_source', 'utm_medium', 'utm_campaign', 'utm_content', 'utm_term' );
	$out  = array();

	foreach ( $keys as $key ) {
		if ( ! empty( $input[ $key ] ) ) {
			$out[ $key ] = mb_substr( sanitize_text_field( $input[ $key ] ), 0, 200 );
		}
	}

	if ( ! empty( $input['referrer'] ) ) {
		$out['referrer'] = esc_url_raw( $input['referrer'] );
	}

	return $out;
}

/**
 * Сохраняет заявку в базе сайта.
 *
 * @param array $lead Данные заявки.
 * @return int ID записи.
 */
function map_store_lead( $lead ) {
	$title = sprintf(
		'%s — %s',
		$lead['name'],
		$lead['direction'] ? $lead['direction'] : __( 'пробный урок', 'musicartplus' )
	);

	$lead_id = wp_insert_post( array(
		'post_type'   => 'map_lead',
		'post_status' => 'private',
		'post_title'  => $title,
	), true );

	if ( is_wp_error( $lead_id ) ) {
		return 0;
	}

	foreach ( array( 'name', 'phone', 'email', 'direction', 'teacher', 'comment', 'page' ) as $key ) {
		if ( ! empty( $lead[ $key ] ) ) {
			update_post_meta( $lead_id, '_map_' . $key, $lead[ $key ] );
		}
	}

	if ( ! empty( $lead['utms'] ) ) {
		update_post_meta( $lead_id, '_map_utms', $lead['utms'] );
	}

	// Отметка о согласии: когда и с какого адреса. Пригодится, если однажды
	// спросят, на каком основании обрабатывались данные.
	update_post_meta( $lead_id, '_map_consent', array(
		'time' => gmdate( 'c' ),
		'ip'   => isset( $_SERVER['REMOTE_ADDR'] ) ? sanitize_text_field( wp_unslash( $_SERVER['REMOTE_ADDR'] ) ) : '',
		'page' => $lead['page'],
		'doc'  => map_privacy_url(),
	) );

	return $lead_id;
}

/**
 * Письмо администратору о новой заявке.
 *
 * @param array $lead    Данные заявки.
 * @param int   $lead_id ID записи.
 * @param bool  $synced  Ушла ли заявка в CRM.
 * @return void
 */
function map_notify_admin( $lead, $lead_id, $synced ) {
	$to = map_opt( 'notify_email' );

	if ( ! $to ) {
		$to = get_option( 'admin_email' );
	}

	if ( ! $to ) {
		return;
	}

	$lines = array(
		'Имя: ' . $lead['name'],
		'Телефон: ' . $lead['phone'],
	);

	if ( $lead['email'] ) {
		$lines[] = 'Почта: ' . $lead['email'];
	}

	if ( $lead['direction'] ) {
		$lines[] = 'Направление: ' . $lead['direction'];
	}

	if ( $lead['teacher'] ) {
		$lines[] = 'Педагог: ' . $lead['teacher'];
	}

	if ( $lead['comment'] ) {
		$lines[] = 'Комментарий: ' . $lead['comment'];
	}

	if ( $lead['page'] ) {
		$lines[] = 'Страница: ' . $lead['page'];
	}

	$lines[] = '';
	$lines[] = $synced
		? 'В «Мой класс» заявка передана.'
		: 'В «Мой класс» пока не передана — сайт повторит попытку автоматически.';

	if ( $lead_id ) {
		$lines[] = 'Карточка заявки: ' . admin_url( 'post.php?post=' . $lead_id . '&action=edit' );
	}

	wp_mail(
		$to,
		sprintf( '[%s] Заявка с сайта: %s', get_bloginfo( 'name' ), $lead['name'] ),
		implode( "\n", $lines )
	);
}

/**
 * Ставит повторную отправку в очередь.
 *
 * @return void
 */
function map_schedule_retry() {
	if ( ! wp_next_scheduled( 'map_retry_leads' ) ) {
		wp_schedule_single_event( time() + 15 * MINUTE_IN_SECONDS, 'map_retry_leads' );
	}
}

/**
 * Досылает в CRM заявки, которые не ушли с первого раза.
 *
 * @return void
 */
function map_retry_leads() {
	if ( ! MAP_Moyklass::is_configured() ) {
		// Ключ ещё не прописан. Заявки ждут, но проверить надо будет снова —
		// иначе очередь останется в базе навсегда.
		if ( map_pending_leads_count() > 0 ) {
			wp_schedule_single_event( time() + HOUR_IN_SECONDS, 'map_retry_leads' );
		}

		return;
	}

	$pending = get_posts( array(
		'post_type'      => 'map_lead',
		'post_status'    => 'private',
		'posts_per_page' => 20,
		'meta_key'       => '_map_crm_status',
		'meta_value'     => 'pending',
		'no_found_rows'  => true,
	) );

	if ( ! $pending ) {
		return;
	}


	foreach ( $pending as $post ) {
		$lead = array(
			'name'      => get_post_meta( $post->ID, '_map_name', true ),
			'phone'     => get_post_meta( $post->ID, '_map_phone', true ),
			'email'     => get_post_meta( $post->ID, '_map_email', true ),
			'direction' => get_post_meta( $post->ID, '_map_direction', true ),
			'teacher'   => get_post_meta( $post->ID, '_map_teacher', true ),
			'comment'   => get_post_meta( $post->ID, '_map_comment', true ),
			'page'      => get_post_meta( $post->ID, '_map_page', true ),
			'utms'      => get_post_meta( $post->ID, '_map_utms', true ),
		);

		$result = MAP_Moyklass::create_lead( $lead );

		if ( is_wp_error( $result ) ) {
			$attempts = (int) get_post_meta( $post->ID, '_map_crm_attempts', true ) + 1;

			update_post_meta( $post->ID, '_map_crm_attempts', $attempts );
			update_post_meta( $post->ID, '_map_crm_error', $result->get_error_message() );

			// После десяти неудач перестаём пытаться — иначе очередь будет расти вечно.
			if ( $attempts >= 10 ) {
				update_post_meta( $post->ID, '_map_crm_status', 'failed' );
			}

			continue;
		}

		update_post_meta( $post->ID, '_map_crm_status', 'sent' );
		update_post_meta( $post->ID, '_map_crm_user_id', isset( $result['id'] ) ? (int) $result['id'] : 0 );
	}

	// Перепланируем по факту остатка, а не по флагу ошибки: за один проход
	// берётся максимум двадцать заявок, и если очередь длиннее, остальные
	// иначе зависли бы навсегда.
	if ( map_pending_leads_count() > 0 ) {
		wp_schedule_single_event( time() + HOUR_IN_SECONDS, 'map_retry_leads' );
	}
}
add_action( 'map_retry_leads', 'map_retry_leads' );

/**
 * Сколько заявок ждёт отправки в CRM.
 *
 * @return int
 */
function map_pending_leads_count() {
	$query = new WP_Query( array(
		'post_type'      => 'map_lead',
		'post_status'    => 'private',
		'posts_per_page' => 1,
		'fields'         => 'ids',
		'meta_key'       => '_map_crm_status', // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_key
		'meta_value'     => 'pending',         // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_value
	) );

	return (int) $query->found_posts;
}
