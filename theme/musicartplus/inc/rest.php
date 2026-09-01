<?php
/**
 * REST-маршруты темы.
 *
 * Браузер обращается только сюда — ключ CRM остаётся на сервере.
 *
 * @package MusicArtPlus
 */

defined( 'ABSPATH' ) || exit;

/**
 * Регистрирует маршруты.
 *
 * @return void
 */
function map_register_rest_routes() {
	register_rest_route( 'map/v1', '/booking', array(
		'methods'             => WP_REST_Server::CREATABLE,
		'callback'            => 'map_rest_booking',
		// Форма открыта всем посетителям; от спама защищают ловушка, таймер
		// и ограничение по адресу в booking.php.
		'permission_callback' => '__return_true',
		'args'                => array(
			'name'  => array( 'required' => true, 'type' => 'string' ),
			'phone' => array( 'required' => true, 'type' => 'string' ),
		),
	) );

	register_rest_route( 'map/v1', '/schedule', array(
		'methods'             => WP_REST_Server::READABLE,
		'callback'            => 'map_rest_schedule',
		'permission_callback' => '__return_true',
	) );

	register_rest_route( 'map/v1', '/slots', array(
		'methods'             => WP_REST_Server::READABLE,
		'callback'            => 'map_rest_slots',
		'permission_callback' => '__return_true',
		'args'                => array(
			// Без параметра отдаём окна всех педагогов сразу: карточка должна
			// открываться уже с расписанием, а не подгружать его на глазах.
			'teacher' => array( 'required' => false, 'type' => 'integer' ),
		),
	) );
}
add_action( 'rest_api_init', 'map_register_rest_routes' );

/**
 * Приём заявки.
 *
 * @param WP_REST_Request $request Запрос.
 * @return WP_REST_Response|WP_Error
 */
function map_rest_booking( WP_REST_Request $request ) {
	$result = map_handle_booking( $request->get_params() );

	if ( is_wp_error( $result ) ) {
		$data   = $result->get_error_data();
		$status = isset( $data['status'] ) ? (int) $data['status'] : 400;

		return new WP_REST_Response( array(
			'ok'      => false,
			'message' => $result->get_error_message(),
			'field'   => isset( $data['field'] ) ? $data['field'] : '',
		), $status );
	}

	return new WP_REST_Response( $result, 200 );
}

/**
 * Расписание групп из CRM.
 *
 * Отдаём только то, что можно показывать публично: название группы, педагога
 * и ближайшие занятия. Данные учеников сюда не попадают.
 *
 * @return WP_REST_Response
 */
function map_rest_schedule() {
	$data = map_public_schedule();

	$response = new WP_REST_Response( $data, 200 );
	$response->header( 'Cache-Control', 'public, max-age=300' );

	return $response;
}

/**
 * Собирает публичное расписание.
 *
 * @return array
 */
function map_public_schedule() {
	if ( ! MAP_Moyklass::is_configured() ) {
		return array( 'ok' => false, 'items' => array() );
	}

	$cached = get_transient( 'map_schedule_public' );

	if ( false !== $cached ) {
		return $cached;
	}

	$classes  = MAP_Moyklass::classes();
	$courses  = MAP_Moyklass::courses();
	$managers = MAP_Moyklass::managers();

	if ( is_wp_error( $classes ) ) {
		return array( 'ok' => false, 'items' => array() );
	}

	$course_names  = array();
	$manager_names = array();

	if ( ! is_wp_error( $courses ) ) {
		foreach ( $courses as $course ) {
			$course_names[ (int) $course['id'] ] = $course['name'];
		}
	}

	if ( ! is_wp_error( $managers ) ) {
		foreach ( $managers as $manager ) {
			$manager_names[ (int) $manager['id'] ] = $manager['name'];
		}
	}

	$items = array();

	foreach ( $classes as $class ) {
		if ( empty( $class['status'] ) || 'opened' !== $class['status'] ) {
			continue;
		}

		$teachers = array();

		if ( ! empty( $class['managerIds'] ) ) {
			foreach ( $class['managerIds'] as $id ) {
				if ( isset( $manager_names[ (int) $id ] ) ) {
					$teachers[] = $manager_names[ (int) $id ];
				}
			}
		}

		$items[] = array(
			'id'      => (int) $class['id'],
			'name'    => isset( $class['name'] ) ? $class['name'] : '',
			'course'  => isset( $course_names[ (int) $class['courseId'] ] ) ? $course_names[ (int) $class['courseId'] ] : '',
			'teacher' => implode( ', ', $teachers ),
			'price'   => isset( $class['priceForWidget'] ) ? $class['priceForWidget'] : '',
			'begins'  => isset( $class['beginDate'] ) ? $class['beginDate'] : '',
		);
	}

	$data = array( 'ok' => true, 'items' => $items );

	set_transient( 'map_schedule_public', $data, 15 * MINUTE_IN_SECONDS );

	return $data;
}

/**
 * Свободные окна педагога.
 *
 * Наружу уходит только время: ни учеников, ни занятий, ни их названий.
 *
 * @param WP_REST_Request $request Запрос.
 * @return WP_REST_Response
 */
function map_rest_slots( WP_REST_Request $request ) {
	$id = (int) $request->get_param( 'teacher' );

	$texts = array(
		'title' => map_opt( 'slots_title', __( 'Свободное время', 'musicartplus' ) ),
		'note'  => map_opt( 'slots_note' ),
		'busy'  => map_opt( 'slots_busy' ),
		'none'  => map_opt( 'slots_none' ),
	);

	if ( $id ) {
		$post = get_post( $id );

		if ( ! $post || 'map_teacher' !== $post->post_type || 'publish' !== $post->post_status ) {
			return new WP_REST_Response( array( 'ok' => false ), 404 );
		}

		$days = map_slots_enabled() ? map_teacher_slots( $post ) : array();

		// ok — ответила ли CRM. Если нет, в карточке останется расписание,
		// заполненное руками: врать про свободное время нельзя.
		return new WP_REST_Response( array_merge( $texts, array(
			'ok'    => map_slots_answered(),
			'state' => map_slots_answered() ? map_teacher_slot_state( $post ) : 'none',
			'days'  => $days,
		) ), 200 );
	}

	$teachers = array();

	if ( map_slots_enabled() ) {
		foreach ( map_get_items( 'map_teacher' ) as $post ) {
			$state = map_teacher_slot_state( $post );

			$teachers[ (string) $post->ID ] = array(
				'state' => $state,
				'days'  => 'free' === $state ? map_teacher_slots( $post ) : array(),
			);
		}
	}

	return new WP_REST_Response( array_merge( $texts, array(
		'ok' => map_slots_answered(),
		// Пустой список должен уехать объектом, а не массивом: скрипт
		// обращается к нему по ключу.
		'teachers' => $teachers ? $teachers : new stdClass(),
	) ), 200 );
}
