<?php
/**
 * Клиент API CRM «Мой класс».
 *
 * Все обращения к CRM идут только с сервера. Ключ доступа никогда не попадает
 * в разметку и в JavaScript: браузер общается с REST-маршрутами темы, а те уже
 * ходят в «Мой класс».
 *
 * Документация: https://api.moyklass.com/
 *
 * @package MusicArtPlus
 */

defined( 'ABSPATH' ) || exit;

/**
 * Обёртка над API «Моего класса».
 */
class MAP_Moyklass {

	/**
	 * Базовый адрес API.
	 */
	const API = 'https://api.moyklass.com/v1';

	/**
	 * Ключ транзиента с токеном доступа.
	 */
	const TOKEN_KEY = 'map_mk_token';

	/**
	 * Префикс ключей кэша ответов.
	 */
	const CACHE_PREFIX = 'map_mk_';

	/**
	 * Сколько ждать ответ обычного запроса, секунд.
	 */
	const TIMEOUT = 8;

	/**
	 * Сколько ждать ответ при отправке заявки, секунд.
	 */
	const TIMEOUT_WRITE = 15;

	/**
	 * Возвращает ключ API.
	 *
	 * Приоритет у константы в wp-config.php: так ключ не лежит в базе и не
	 * попадает в дамп или в репозиторий.
	 *
	 * @return string
	 */
	public static function api_key() {
		if ( defined( 'MOYKLASS_API_KEY' ) && MOYKLASS_API_KEY ) {
			return (string) MOYKLASS_API_KEY;
		}

		$key = get_option( 'map_mk_api_key', '' );

		return is_string( $key ) ? trim( $key ) : '';
	}

	/**
	 * Настроена ли интеграция.
	 *
	 * @return bool
	 */
	public static function is_configured() {
		return '' !== self::api_key();
	}

	/**
	 * Возвращает действующий токен доступа.
	 *
	 * Токен живёт семь дней; держим его в транзиенте шесть, чтобы не попасть
	 * на протухание в момент отправки заявки.
	 *
	 * @param bool $force Получить новый токен, не заглядывая в кэш.
	 * @return string|WP_Error
	 */
	public static function token( $force = false ) {
		if ( ! self::is_configured() ) {
			return new WP_Error( 'map_mk_no_key', __( 'Не задан ключ API «Моего класса».', 'musicartplus' ) );
		}

		if ( ! $force ) {
			$cached = get_transient( self::TOKEN_KEY );

			if ( $cached ) {
				return $cached;
			}
		}

		$response = wp_remote_post( self::API . '/company/auth/getToken', array(
			'timeout' => self::TIMEOUT,
			'headers' => array( 'Content-Type' => 'application/json' ),
			'body'    => wp_json_encode( array( 'apiKey' => self::api_key() ) ),
		) );

		if ( is_wp_error( $response ) ) {
			return $response;
		}

		$code = wp_remote_retrieve_response_code( $response );
		$body = json_decode( wp_remote_retrieve_body( $response ), true );

		if ( 200 !== $code || empty( $body['accessToken'] ) ) {
			return new WP_Error(
				'map_mk_auth',
				__( 'CRM не приняла ключ доступа.', 'musicartplus' ),
				array( 'status' => $code, 'body' => $body )
			);
		}

		set_transient( self::TOKEN_KEY, $body['accessToken'], 6 * DAY_IN_SECONDS );

		return $body['accessToken'];
	}

	/**
	 * Выполняет запрос к API.
	 *
	 * При ответе 401 токен считается протухшим: запрашиваем новый и повторяем
	 * запрос один раз.
	 *
	 * @param string $method Метод HTTP.
	 * @param string $path   Путь после /v1.
	 * @param array  $args   Параметры: query, body, timeout.
	 * @param bool   $retry  Внутренний флаг повтора.
	 * @return array|WP_Error
	 */
	public static function request( $method, $path, $args = array(), $retry = true ) {
		$token = self::token();

		if ( is_wp_error( $token ) ) {
			return $token;
		}

		$url = self::API . '/' . ltrim( $path, '/' );

		if ( ! empty( $args['query'] ) ) {
			// http_build_query, а не add_query_arg: часть маршрутов ждёт список
			// значений (date[]=…&date[]=…), а add_query_arg приводит массив к
			// строке «Array» — и CRM отвечает ошибкой проверки формата.
			$url .= ( false === strpos( $url, '?' ) ? '?' : '&' ) . http_build_query( $args['query'] );
		}

		$request = array(
			'method'  => $method,
			'timeout' => isset( $args['timeout'] ) ? $args['timeout'] : self::TIMEOUT,
			'headers' => array(
				'x-access-token' => $token,
				'Content-Type'   => 'application/json',
				'Accept'         => 'application/json',
			),
		);

		if ( isset( $args['body'] ) ) {
			$request['body'] = wp_json_encode( $args['body'] );
		}

		$response = wp_remote_request( $url, $request );

		if ( is_wp_error( $response ) ) {
			return $response;
		}

		$code = wp_remote_retrieve_response_code( $response );

		if ( 401 === $code && $retry ) {
			delete_transient( self::TOKEN_KEY );
			self::token( true );

			return self::request( $method, $path, $args, false );
		}

		$body = json_decode( wp_remote_retrieve_body( $response ), true );

		if ( $code < 200 || $code >= 300 ) {
			return new WP_Error(
				'map_mk_http',
				sprintf(
					/* translators: %d — код ответа HTTP. */
					__( 'CRM ответила ошибкой %d.', 'musicartplus' ),
					$code
				),
				array( 'status' => $code, 'body' => $body, 'path' => $path )
			);
		}

		return null === $body ? array() : $body;
	}

	/**
	 * GET-запрос с кэшированием ответа.
	 *
	 * @param string $path  Путь после /v1.
	 * @param array  $query Параметры запроса.
	 * @param int    $ttl   Время жизни кэша, секунд. 0 — не кэшировать.
	 * @return array|WP_Error
	 */
	public static function get( $path, $query = array(), $ttl = HOUR_IN_SECONDS ) {
		$key = self::CACHE_PREFIX . md5( $path . wp_json_encode( $query ) );

		if ( $ttl > 0 ) {
			$cached = get_transient( $key );

			if ( false !== $cached ) {
				return $cached;
			}
		}

		$data = self::request( 'GET', $path, array( 'query' => $query ) );

		if ( is_wp_error( $data ) ) {
			return $data;
		}

		if ( $ttl > 0 ) {
			set_transient( $key, $data, $ttl );
			self::remember_cache_key( $key );
		}

		return $data;
	}

	/**
	 * Запоминает ключ кэша, чтобы потом можно было всё сбросить.
	 *
	 * @param string $key Ключ транзиента.
	 * @return void
	 */
	protected static function remember_cache_key( $key ) {
		$keys = get_option( 'map_mk_cache_keys', array() );

		if ( ! is_array( $keys ) ) {
			$keys = array();
		}

		if ( ! in_array( $key, $keys, true ) ) {
			$keys[] = $key;
			update_option( 'map_mk_cache_keys', $keys, false );
		}
	}

	/**
	 * Сбрасывает кэш ответов CRM.
	 *
	 * @param bool $with_token Сбросить и токен доступа.
	 * @return int Сколько записей удалено.
	 */
	public static function flush_cache( $with_token = false ) {
		$keys = get_option( 'map_mk_cache_keys', array() );
		$n    = 0;

		if ( is_array( $keys ) ) {
			foreach ( $keys as $key ) {
				if ( delete_transient( $key ) ) {
					$n++;
				}
			}
		}

		delete_option( 'map_mk_cache_keys' );

		if ( $with_token ) {
			delete_transient( self::TOKEN_KEY );
		}

		return $n;
	}

	/**
	 * Филиалы центра.
	 *
	 * @return array|WP_Error
	 */
	public static function filials() {
		return self::get( 'company/filials', array(), DAY_IN_SECONDS );
	}

	/**
	 * ID основного филиала.
	 *
	 * @return int
	 */
	public static function filial_id() {
		$saved = (int) map_opt( 'crm_filial_id', 0 );

		if ( $saved ) {
			return $saved;
		}

		$filials = self::filials();

		if ( is_wp_error( $filials ) || empty( $filials[0]['id'] ) ) {
			return 0;
		}

		return (int) $filials[0]['id'];
	}

	/**
	 * Программы обучения.
	 *
	 * @return array|WP_Error
	 */
	public static function courses() {
		return self::get( 'company/courses', array(), 6 * HOUR_IN_SECONDS );
	}

	/**
	 * Группы.
	 *
	 * @return array|WP_Error
	 */
	public static function classes() {
		return self::get( 'company/classes', array(), HOUR_IN_SECONDS );
	}

	/**
	 * Сотрудники (в CRM педагоги заведены как сотрудники).
	 *
	 * @return array|WP_Error
	 */
	public static function managers() {
		return self::get( 'company/managers', array(), 6 * HOUR_IN_SECONDS );
	}

	/**
	 * Занятия за период.
	 *
	 * @param string $from Дата начала, Y-m-d.
	 * @param string $to   Дата конца, Y-m-d.
	 * @return array|WP_Error
	 */
	public static function lessons( $from, $to ) {
		return self::get( 'company/lessons', array(
			// Две даты в параметре date — это диапазон «с … по …».
			'date'  => array( $from, $to ),
			'limit' => 500,
		), 15 * MINUTE_IN_SECONDS );
	}

	/**
	 * Нерабочее время педагогов за период.
	 *
	 * Так CRM хранит выходные и перерывы: рабочим считается всё, что не попало
	 * в этот список и не занято уроком.
	 *
	 * @param string $from Дата начала, Y-m-d.
	 * @param string $to   Дата конца, Y-m-d.
	 * @return array|WP_Error
	 */
	public static function busy_times( $from, $to ) {
		return self::get( 'company/busyTimes', array(
			'date' => array( $from, $to ),
		), 15 * MINUTE_IN_SECONDS );
	}

	/**
	 * Часовой пояс филиала.
	 *
	 * Свободные окна считаются по времени центра, а не по времени сервера.
	 *
	 * @return string
	 */
	public static function timezone() {
		$filials = self::filials();

		if ( is_wp_error( $filials ) ) {
			return 'Europe/Moscow';
		}

		foreach ( (array) $filials as $filial ) {
			if ( ! empty( $filial['timezone'] ) ) {
				return (string) $filial['timezone'];
			}
		}

		return 'Europe/Moscow';
	}

	/**
	 * Создаёт заявку — карточку ученика в CRM.
	 *
	 * @param array $lead Данные заявки: name, phone, email, comment, direction, utms.
	 * @return array|WP_Error Ответ CRM с id созданного ученика.
	 */
	public static function create_lead( $lead ) {
		$payload = array( 'name' => $lead['name'] );

		// CRM принимает телефон строго цифрами: ^[0-9]{10,15}$ — плюс и разделители
		// нужно снять, иначе весь запрос отклоняется с ошибкой 400.
		$digits = preg_replace( '/\D+/', '', (string) $lead['phone'] );

		if ( strlen( $digits ) >= 10 && strlen( $digits ) <= 15 ) {
			$payload['phone'] = $digits;
		}

		if ( ! empty( $lead['email'] ) ) {
			$payload['email'] = $lead['email'];
		}

		$filial = self::filial_id();

		if ( $filial ) {
			$payload['filials'] = array( $filial );
		}

		// «Виджет: Форма» — так заявки с сайта видно отдельно от заведённых вручную.
		$payload['createSourceId'] = (int) map_opt( 'crm_source_id', 3 );

		$adv = (int) map_opt( 'crm_adv_source_id', 0 );

		if ( $adv ) {
			$payload['advSourceId'] = $adv;
		}

		if ( ! empty( $lead['utms'] ) && is_array( $lead['utms'] ) ) {
			$payload['utms'] = $lead['utms'];
		}

		// Повторная отправка не должна плодить карточки. Типичный случай:
		// «Мой класс» принял запрос и создал ученика, но ответ не успел дойти
		// за отведённые 15 секунд — сайт считает заявку неотправленной и через
		// четверть часа пробует снова.
		$existing = self::find_by_phone( isset( $payload['phone'] ) ? $payload['phone'] : '' );

		if ( $existing ) {
			$result = $existing;
		} else {
			$result = self::request( 'POST', 'company/users', array(
				'body'    => $payload,
				'timeout' => self::TIMEOUT_WRITE,
			) );

			if ( is_wp_error( $result ) ) {
				return $result;
			}
		}

		// Направление и комментарий кладём отдельной заметкой в карточку.
		if ( ! empty( $result['id'] ) ) {
			$note = self::compose_note( $lead );

			if ( $note ) {
				$comment = self::add_comment( (int) $result['id'], $note );

				// Сама заявка уже создана — неудачная заметка её не отменяет,
				// но знать о проблеме нужно.
				if ( is_wp_error( $comment ) && defined( 'WP_DEBUG' ) && WP_DEBUG ) {
					error_log( 'MusicArtPlus: заметка к заявке не записана — ' . $comment->get_error_message() ); // phpcs:ignore WordPress.PHP.DevelopmentFunctions.error_log_error_log
				}
			}
		}

		return $result;
	}

	/**
	 * Ищет ученика по номеру телефона.
	 *
	 * Возвращает первую найденную карточку. Ошибки поиска намеренно
	 * проглатываются: не нашли — значит создадим новую, это безопаснее,
	 * чем потерять заявку.
	 *
	 * @param string $phone Телефон цифрами.
	 * @return array|null
	 */
	protected static function find_by_phone( $phone ) {
		$digits = preg_replace( '/\D+/', '', (string) $phone );

		if ( strlen( $digits ) < 10 ) {
			return null;
		}

		$found = self::request( 'GET', 'company/users', array(
			'query' => array( 'phone' => $digits, 'limit' => 1 ),
		) );

		if ( is_wp_error( $found ) || empty( $found['users'][0]['id'] ) ) {
			return null;
		}

		return $found['users'][0];
	}

	/**
	 * Собирает текст заметки к карточке ученика.
	 *
	 * @param array $lead Данные заявки.
	 * @return string
	 */
	protected static function compose_note( $lead ) {
		$parts = array();

		if ( ! empty( $lead['direction'] ) ) {
			$parts[] = 'Направление: ' . $lead['direction'];
		}

		if ( ! empty( $lead['teacher'] ) ) {
			$parts[] = 'Педагог: ' . $lead['teacher'];
		}

		if ( ! empty( $lead['slot'] ) ) {
			$parts[] = 'Выбранное время: ' . $lead['slot'];
		}

		if ( ! empty( $lead['comment'] ) ) {
			$parts[] = 'Комментарий: ' . $lead['comment'];
		}

		if ( ! empty( $lead['page'] ) ) {
			$parts[] = 'Страница: ' . $lead['page'];
		}

		return implode( "\n", $parts );
	}

	/**
	 * Добавляет комментарий в карточку ученика.
	 *
	 * @param int    $user_id ID ученика в CRM.
	 * @param string $text    Текст комментария.
	 * @return array|WP_Error
	 */
	public static function add_comment( $user_id, $text ) {
		return self::request( 'POST', 'company/userComments', array(
			'body'    => array(
				'userId'     => $user_id,
				'comment'    => $text,
				// Заметка для администратора, ученику её видеть не нужно.
				'showToUser' => false,
			),
			'timeout' => self::TIMEOUT_WRITE,
		) );
	}

	/**
	 * Проверяет связь с CRM.
	 *
	 * @return array|WP_Error Краткая сводка для экрана настроек.
	 */
	public static function ping() {
		$token = self::token( true );

		if ( is_wp_error( $token ) ) {
			return $token;
		}

		$filials = self::request( 'GET', 'company/filials' );

		if ( is_wp_error( $filials ) ) {
			return $filials;
		}

		$managers = self::request( 'GET', 'company/managers' );
		$classes  = self::request( 'GET', 'company/classes' );

		return array(
			'filial'   => isset( $filials[0]['name'] ) ? $filials[0]['name'] : '—',
			'address'  => isset( $filials[0]['address'] ) ? $filials[0]['address'] : '',
			'managers' => is_wp_error( $managers ) ? 0 : count( $managers ),
			'classes'  => is_wp_error( $classes ) ? 0 : count( $classes ),
		);
	}
}
