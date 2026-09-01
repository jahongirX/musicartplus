<?php
/**
 * Экран интеграции и удобства в админке.
 *
 * @package MusicArtPlus
 */

defined( 'ABSPATH' ) || exit;

/**
 * Добавляет пункт меню «Интеграция с CRM».
 *
 * @return void
 */
function map_admin_menu() {
	add_menu_page(
		__( 'Интеграция с CRM', 'musicartplus' ),
		__( 'Интеграция с CRM', 'musicartplus' ),
		'manage_options',
		'map-crm',
		'map_admin_page',
		'dashicons-update',
		27
	);
}
add_action( 'admin_menu', 'map_admin_menu' );

/**
 * Экран проверки интеграции.
 *
 * @return void
 */
function map_admin_page() {
	if ( ! current_user_can( 'manage_options' ) ) {
		return;
	}

	$notice = map_admin_handle_actions();

	echo '<div class="wrap"><h1>' . esc_html__( 'Интеграция с «Моим классом»', 'musicartplus' ) . '</h1>';

	if ( $notice ) {
		printf( '<div class="notice notice-%s"><p>%s</p></div>', esc_attr( $notice[0] ), esc_html( $notice[1] ) );
	}

	map_admin_key_status();
	map_admin_connection();
	map_admin_leads_summary();
	map_admin_notify_status();
	map_admin_actions_form();

	echo '</div>';
}

/**
 * Обрабатывает нажатия кнопок на экране.
 *
 * @return array|null Пара «тип уведомления, текст».
 */
function map_admin_handle_actions() {
	if ( empty( $_POST['map_action'] ) ) {
		return null;
	}

	check_admin_referer( 'map_crm_action' );

	$action = sanitize_key( wp_unslash( $_POST['map_action'] ) );

	if ( 'flush' === $action ) {
		$n = MAP_Moyklass::flush_cache( true );

		return array( 'success', sprintf(
			/* translators: %d — количество очищенных записей. */
			__( 'Кэш сброшен, записей удалено: %d.', 'musicartplus' ),
			$n
		) );
	}

	if ( 'retry' === $action ) {
		map_retry_leads();
		map_retry_notify();

		return array( 'success', __( 'Повторная отправка заявок выполнена.', 'musicartplus' ) );
	}

	if ( 'tgtest' === $action ) {
		if ( ! map_tg_ready() ) {
			return array( 'error', __( 'Telegram не настроен: включите отправку, впишите токен бота и хотя бы один чат.', 'musicartplus' ) );
		}

		$result = map_tg_test();

		if ( $result['failed'] ) {
			$lines = array();

			foreach ( $result['failed'] as $chat => $error ) {
				$lines[] = $chat . ' — ' . $error;
			}

			return array( 'error', sprintf(
				/* translators: 1 — сколько чатов получили сообщение, 2 — список ошибок. */
				__( 'Доставлено чатов: %1$d. Не получилось: %2$s', 'musicartplus' ),
				count( $result['sent'] ),
				implode( '; ', $lines )
			) );
		}

		return array( 'success', sprintf(
			/* translators: %d — количество чатов. */
			__( 'Пробное сообщение ушло. Чатов: %d.', 'musicartplus' ),
			count( $result['sent'] )
		) );
	}

	if ( 'seed' === $action ) {
		$result = map_seed_content();

		return array( 'success', $result );
	}

	return null;
}

/**
 * Показывает, откуда взят ключ API.
 *
 * @return void
 */
function map_admin_key_status() {
	echo '<h2>' . esc_html__( 'Ключ доступа', 'musicartplus' ) . '</h2>';

	if ( defined( 'MOYKLASS_API_KEY' ) && MOYKLASS_API_KEY ) {
		echo '<p>' . esc_html__( 'Ключ задан константой MOYKLASS_API_KEY в wp-config.php — так и должно быть.', 'musicartplus' ) . '</p>';

		return;
	}

	if ( MAP_Moyklass::is_configured() ) {
		echo '<p class="notice notice-warning" style="padding:10px">'
			. esc_html__( 'Ключ хранится в базе. Надёжнее перенести его в wp-config.php константой MOYKLASS_API_KEY — тогда он не попадёт в дамп базы и в резервную копию.', 'musicartplus' )
			. '</p>';

		return;
	}

	echo '<p class="notice notice-error" style="padding:10px">'
		. esc_html__( 'Ключ не задан. Добавьте в wp-config.php строку:', 'musicartplus' )
		. '</p><pre style="background:#f6f7f7;padding:12px;overflow:auto">'
		. "define( 'MOYKLASS_API_KEY', 'ваш-ключ' );"
		. '</pre>';
}

/**
 * Проверяет связь с CRM и показывает сводку.
 *
 * @return void
 */
function map_admin_connection() {
	echo '<h2>' . esc_html__( 'Связь с CRM', 'musicartplus' ) . '</h2>';

	if ( ! MAP_Moyklass::is_configured() ) {
		echo '<p>' . esc_html__( 'Проверка недоступна: не задан ключ.', 'musicartplus' ) . '</p>';

		return;
	}

	$ping = MAP_Moyklass::ping();

	if ( is_wp_error( $ping ) ) {
		printf(
			'<p class="notice notice-error" style="padding:10px">%s<br><code>%s</code></p>',
			esc_html__( 'CRM не отвечает:', 'musicartplus' ),
			esc_html( $ping->get_error_message() )
		);

		return;
	}

	echo '<table class="widefat striped" style="max-width:640px"><tbody>';
	printf( '<tr><td>%s</td><td><strong>%s</strong></td></tr>', esc_html__( 'Филиал', 'musicartplus' ), esc_html( $ping['filial'] ) );
	printf( '<tr><td>%s</td><td>%s</td></tr>', esc_html__( 'Адрес', 'musicartplus' ), esc_html( $ping['address'] ) );
	printf( '<tr><td>%s</td><td>%d</td></tr>', esc_html__( 'Сотрудников', 'musicartplus' ), (int) $ping['managers'] );
	printf( '<tr><td>%s</td><td>%d</td></tr>', esc_html__( 'Групп', 'musicartplus' ), (int) $ping['classes'] );
	echo '</tbody></table>';
}

/**
 * Сводка по заявкам.
 *
 * @return void
 */
function map_admin_leads_summary() {
	$counts = array(
		'sent'    => 0,
		'pending' => 0,
		'failed'  => 0,
	);

	foreach ( array_keys( $counts ) as $status ) {
		$counts[ $status ] = map_count_leads( $status );
	}

	echo '<h2>' . esc_html__( 'Заявки с сайта', 'musicartplus' ) . '</h2>';
	echo '<table class="widefat striped" style="max-width:640px"><tbody>';
	printf( '<tr><td>%s</td><td>%d</td></tr>', esc_html__( 'Переданы в CRM', 'musicartplus' ), (int) $counts['sent'] );
	printf( '<tr><td>%s</td><td>%d</td></tr>', esc_html__( 'Ждут отправки', 'musicartplus' ), (int) $counts['pending'] );
	printf( '<tr><td>%s</td><td>%d</td></tr>', esc_html__( 'Не удалось передать', 'musicartplus' ), (int) $counts['failed'] );
	echo '</tbody></table>';
}

/**
 * Считает заявки в статусе.
 *
 * @param string $status Статус.
 * @return int
 */
function map_count_leads( $status ) {
	$query = new WP_Query( array(
		'post_type'      => 'map_lead',
		'post_status'    => 'private',
		'posts_per_page' => 1,
		'fields'         => 'ids',
		'meta_key'       => '_map_crm_status',
		'meta_value'     => $status,
	) );

	return (int) $query->found_posts;
}

/**
 * Куда уходят заявки помимо CRM.
 *
 * @return void
 */
function map_admin_notify_status() {
	echo '<h2>' . esc_html__( 'Уведомления о заявках', 'musicartplus' ) . '</h2>';

	$emails = map_notify_recipients();
	$chats  = map_tg_chats();
	$token  = map_tg_token();

	if ( defined( 'MAP_TG_BOT_TOKEN' ) && MAP_TG_BOT_TOKEN ) {
		$where = __( 'константа в wp-config.php', 'musicartplus' );
	} elseif ( $token ) {
		$where = __( 'настройки сайта (надёжнее перенести в wp-config.php)', 'musicartplus' );
	} else {
		$where = __( 'не задан', 'musicartplus' );
	}

	echo '<table class="widefat striped" style="max-width:640px"><tbody>';

	printf(
		'<tr><td style="width:220px">%s</td><td>%s</td></tr>',
		esc_html__( 'Почта', 'musicartplus' ),
		$emails ? esc_html( implode( ', ', $emails ) ) : esc_html__( 'ни одного адреса', 'musicartplus' )
	);

	printf(
		'<tr><td>%s</td><td>%s</td></tr>',
		esc_html__( 'Telegram', 'musicartplus' ),
		map_opt( 'tg_enabled' ) ? esc_html__( 'включён', 'musicartplus' ) : esc_html__( 'выключен', 'musicartplus' )
	);

	printf( '<tr><td>%s</td><td>%s</td></tr>', esc_html__( 'Токен бота', 'musicartplus' ), esc_html( $where ) );

	printf(
		'<tr><td>%s</td><td>%s</td></tr>',
		esc_html__( 'Чаты', 'musicartplus' ),
		$chats ? esc_html( implode( ', ', $chats ) ) : esc_html__( 'не указаны', 'musicartplus' )
	);

	$waiting = map_pending_notify_count();

	if ( $waiting ) {
		printf( '<tr><td>%s</td><td>%d</td></tr>', esc_html__( 'Ждут отправки в Telegram', 'musicartplus' ), (int) $waiting );
	}

	echo '</tbody></table>';

	printf(
		'<p><a href="%s">%s</a></p>',
		esc_url( admin_url( 'admin.php?page=map-settings' ) ),
		esc_html__( 'Изменить адреса и чаты — «Настройки сайта» → «Уведомления о заявках»', 'musicartplus' )
	);
}

/**
 * Кнопки действий.
 *
 * @return void
 */
function map_admin_actions_form() {
	echo '<h2>' . esc_html__( 'Действия', 'musicartplus' ) . '</h2>';
	echo '<form method="post">';
	wp_nonce_field( 'map_crm_action' );

	printf(
		'<p><button class="button" name="map_action" value="flush">%s</button> <span class="description">%s</span></p>',
		esc_html__( 'Сбросить кэш CRM', 'musicartplus' ),
		esc_html__( 'Пригодится, если в CRM поменяли группы, а на сайте ещё старые.', 'musicartplus' )
	);

	printf(
		'<p><button class="button" name="map_action" value="retry">%s</button> <span class="description">%s</span></p>',
		esc_html__( 'Дослать заявки', 'musicartplus' ),
		esc_html__( 'Отправит в CRM те заявки, которые не ушли с первого раза.', 'musicartplus' )
	);

	printf(
		'<p><button class="button" name="map_action" value="tgtest">%s</button> <span class="description">%s</span></p>',
		esc_html__( 'Проверить Telegram', 'musicartplus' ),
		esc_html__( 'Отправит в указанные чаты пробное сообщение.', 'musicartplus' )
	);

	printf(
		'<p><button class="button button-primary" name="map_action" value="seed" onclick="return confirm(\'%s\')">%s</button> <span class="description">%s</span></p>',
		esc_js( __( 'Создать страницы и карточки? Существующие записи затронуты не будут.', 'musicartplus' ) ),
		esc_html__( 'Наполнить сайт', 'musicartplus' ),
		esc_html__( 'Создаёт страницы, педагогов, направления и отзывы из вёрстки. Повторный запуск ничего не дублирует.', 'musicartplus' )
	);

	echo '</form>';
}

/**
 * Показывает данные заявки прямо в её карточке.
 *
 * @return void
 */
function map_lead_metabox() {
	add_meta_box(
		'map_lead_data',
		__( 'Данные заявки', 'musicartplus' ),
		'map_render_lead_metabox',
		'map_lead',
		'normal',
		'high'
	);
}
add_action( 'add_meta_boxes', 'map_lead_metabox' );

/**
 * Разметка карточки заявки.
 *
 * @param WP_Post $post Заявка.
 * @return void
 */
function map_render_lead_metabox( $post ) {
	$rows = array(
		'_map_name'      => __( 'Имя', 'musicartplus' ),
		'_map_phone'     => __( 'Телефон', 'musicartplus' ),
		'_map_email'     => __( 'Почта', 'musicartplus' ),
		'_map_direction' => __( 'Направление', 'musicartplus' ),
		'_map_teacher'   => __( 'Педагог', 'musicartplus' ),
		'_map_comment'   => __( 'Комментарий', 'musicartplus' ),
		'_map_page'      => __( 'Страница', 'musicartplus' ),
	);

	echo '<table class="widefat striped"><tbody>';

	foreach ( $rows as $key => $label ) {
		$value = get_post_meta( $post->ID, $key, true );

		if ( ! $value ) {
			continue;
		}

		printf( '<tr><td style="width:180px">%s</td><td>%s</td></tr>', esc_html( $label ), esc_html( $value ) );
	}

	$status = get_post_meta( $post->ID, '_map_crm_status', true );
	$labels = array(
		'sent'    => __( 'Передана в CRM', 'musicartplus' ),
		'pending' => __( 'Ждёт отправки', 'musicartplus' ),
		'failed'  => __( 'Передать не удалось', 'musicartplus' ),
	);

	printf(
		'<tr><td>%s</td><td><strong>%s</strong></td></tr>',
		esc_html__( 'Статус', 'musicartplus' ),
		esc_html( isset( $labels[ $status ] ) ? $labels[ $status ] : '—' )
	);

	$error = get_post_meta( $post->ID, '_map_crm_error', true );

	if ( $error && 'sent' !== $status ) {
		printf( '<tr><td>%s</td><td><code>%s</code></td></tr>', esc_html__( 'Ошибка', 'musicartplus' ), esc_html( $error ) );
	}

	$mail = get_post_meta( $post->ID, '_map_mail_status', true );

	if ( $mail ) {
		printf(
			'<tr><td>%s</td><td>%s</td></tr>',
			esc_html__( 'Письмо', 'musicartplus' ),
			'sent' === $mail
				? esc_html__( 'отправлено', 'musicartplus' )
				: esc_html__( 'сервер не принял письмо', 'musicartplus' )
		);
	}

	$tg = get_post_meta( $post->ID, '_map_tg_status', true );

	if ( $tg ) {
		$tg_labels = array(
			'sent'    => __( 'отправлено', 'musicartplus' ),
			'pending' => __( 'ждёт повтора', 'musicartplus' ),
			'failed'  => __( 'отправить не удалось', 'musicartplus' ),
		);

		printf(
			'<tr><td>%s</td><td>%s</td></tr>',
			esc_html__( 'Telegram', 'musicartplus' ),
			esc_html( isset( $tg_labels[ $tg ] ) ? $tg_labels[ $tg ] : $tg )
		);

		$tg_error = get_post_meta( $post->ID, '_map_tg_error', true );

		if ( $tg_error && 'sent' !== $tg ) {
			printf( '<tr><td>%s</td><td><code>%s</code></td></tr>', esc_html__( 'Ошибка Telegram', 'musicartplus' ), esc_html( $tg_error ) );
		}
	}

	echo '</tbody></table>';
}

/**
 * Колонки в списке заявок.
 *
 * @param array $columns Колонки.
 * @return array
 */
function map_lead_columns( $columns ) {
	$columns = array(
		'cb'        => isset( $columns['cb'] ) ? $columns['cb'] : '',
		'title'     => __( 'Заявка', 'musicartplus' ),
		'map_phone' => __( 'Телефон', 'musicartplus' ),
		'map_crm'   => __( 'CRM', 'musicartplus' ),
		'date'      => __( 'Дата', 'musicartplus' ),
	);

	return $columns;
}
add_filter( 'manage_map_lead_posts_columns', 'map_lead_columns' );

/**
 * Содержимое колонок списка заявок.
 *
 * @param string $column  Колонка.
 * @param int    $post_id ID заявки.
 * @return void
 */
function map_lead_column_content( $column, $post_id ) {
	if ( 'map_phone' === $column ) {
		echo esc_html( get_post_meta( $post_id, '_map_phone', true ) );
	}

	if ( 'map_crm' === $column ) {
		$status = get_post_meta( $post_id, '_map_crm_status', true );
		$marks  = array(
			'sent'    => '✓',
			'pending' => '⏳',
			'failed'  => '✕',
		);

		echo esc_html( isset( $marks[ $status ] ) ? $marks[ $status ] : '—' );
	}
}
add_action( 'manage_map_lead_posts_custom_column', 'map_lead_column_content', 10, 2 );
