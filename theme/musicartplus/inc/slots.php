<?php
/**
 * Свободные окна педагогов для записи.
 *
 * В API «Моего класса» нет рабочих часов: маршрут со свободным временем
 * доступен только из личного кабинета ученика, а ключ компании туда не пускают.
 * Зато есть нерабочее время и занятия — из них окна и складываются:
 *
 *     рабочий день центра − нерабочее время педагога − его занятия
 *
 * Рабочий день задаётся в настройках сайта: в CRM он живёт в настройках
 * компании, наружу не отдаётся, и синхронизировать его нечем.
 *
 * @package MusicArtPlus
 */

defined( 'ABSPATH' ) || exit;

/**
 * Показывать ли свободные окна.
 *
 * @return bool
 */
function map_slots_enabled() {
	if ( ! MAP_Moyklass::is_configured() ) {
		return false;
	}

	// Пока настройку ни разу не сохраняли, окна показываем: ради них всё и
	// затевалось. После первого сохранения решает галочка в настройках.
	$saved = get_option( 'options_slots_enabled', null );

	if ( null === $saved || '' === $saved ) {
		return true;
	}

	return (bool) $saved;
}

/**
 * Настройки расчёта окон.
 *
 * @return array{from:int,to:int,step:int,days:int,lead:int}
 */
function map_slots_config() {
	$from = map_minutes( map_opt( 'slots_from', '10:00' ), 600 );
	$to   = map_minutes( map_opt( 'slots_to', '22:00' ), 1320 );

	// Перепутанные местами часы — не повод показывать пустоту весь день.
	if ( $to <= $from ) {
		$from = 600;
		$to   = 1320;
	}

	return array(
		'from' => $from,
		'to'   => $to,
		'step' => max( 15, min( 180, (int) map_opt( 'slots_step', 45 ) ) ),
		'days' => max( 1, min( 21, (int) map_opt( 'slots_days', 7 ) ) ),
		'lead' => max( 0, min( 72, (int) map_opt( 'slots_lead', 3 ) ) ),
	);
}

/**
 * «ЧЧ:ММ» в минуты от начала суток.
 *
 * @param string $time    Время.
 * @param int    $default Значение, если разобрать не вышло.
 * @return int
 */
function map_minutes( $time, $default = 0 ) {
	if ( preg_match( '/^(\d{1,2}):(\d{2})/', trim( (string) $time ), $m ) ) {
		return min( 1440, (int) $m[1] * 60 + (int) $m[2] );
	}

	return $default;
}

/**
 * Минуты обратно в «ЧЧ:ММ».
 *
 * @param int $minutes Минуты от начала суток.
 * @return string
 */
function map_hhmm( $minutes ) {
	return sprintf( '%02d:%02d', intdiv( (int) $minutes, 60 ), (int) $minutes % 60 );
}

/**
 * Свободные окна всех педагогов.
 *
 * Считается один раз на все карточки: занятия и нерабочее время приезжают
 * из CRM целиком за период, поэтому отдельный запрос на каждого педагога
 * ничего бы не дал, кроме нагрузки.
 *
 * @return array<int,array<string,string[]>> ID педагога в CRM → дата → время начала.
 */
function map_slots_all() {
	static $cache = null;

	if ( null !== $cache ) {
		return $cache;
	}

	$cache = array();

	if ( ! map_slots_enabled() ) {
		return $cache;
	}

	map_slots_answered( null );

	$cfg = map_slots_config();

	try {
		$tz = new DateTimeZone( MAP_Moyklass::timezone() );
	} catch ( Exception $e ) {
		$tz = new DateTimeZone( 'Europe/Moscow' );
	}

	$now   = new DateTimeImmutable( 'now', $tz );
	$dates = array();

	for ( $i = 0; $i < $cfg['days']; $i++ ) {
		$dates[] = $now->modify( '+' . $i . ' days' )->format( 'Y-m-d' );
	}

	$busy    = MAP_Moyklass::busy_times( $dates[0], end( $dates ) );
	$lessons = MAP_Moyklass::lessons( $dates[0], end( $dates ) );

	// CRM недоступна — лучше не показывать ничего, чем показать несвободное
	// время как свободное.
	if ( is_wp_error( $busy ) || is_wp_error( $lessons ) ) {
		return $cache;
	}

	map_slots_answered( true );

	$taken = array();

	foreach ( (array) ( isset( $busy['busyTimes'] ) ? $busy['busyTimes'] : array() ) as $row ) {
		$id = isset( $row['teacherId'] ) ? (int) $row['teacherId'] : 0;

		if ( $id && ! empty( $row['date'] ) ) {
			$taken[ $id ][ $row['date'] ][] = array(
				map_minutes( isset( $row['beginTime'] ) ? $row['beginTime'] : '00:00' ),
				map_minutes( isset( $row['endTime'] ) ? $row['endTime'] : '24:00', 1440 ),
			);
		}
	}

	foreach ( (array) ( isset( $lessons['lessons'] ) ? $lessons['lessons'] : array() ) as $row ) {
		if ( empty( $row['date'] ) ) {
			continue;
		}

		foreach ( (array) ( isset( $row['teacherIds'] ) ? $row['teacherIds'] : array() ) as $id ) {
			$taken[ (int) $id ][ $row['date'] ][] = array(
				map_minutes( isset( $row['beginTime'] ) ? $row['beginTime'] : '00:00' ),
				map_minutes( isset( $row['endTime'] ) ? $row['endTime'] : '24:00', 1440 ),
			);
		}
	}

	// Педагоги берутся из CRM: у кого нет ни одной записи о занятости, тот
	// свободен весь рабочий день, и его тоже надо показать.
	$managers = MAP_Moyklass::managers();
	$ids      = array();

	if ( ! is_wp_error( $managers ) ) {
		foreach ( (array) $managers as $manager ) {
			if ( ! empty( $manager['id'] ) && empty( $manager['blocked'] ) ) {
				$ids[] = (int) $manager['id'];
			}
		}
	}

	foreach ( array_keys( $taken ) as $id ) {
		$ids[] = (int) $id;
	}

	$today = $now->format( 'Y-m-d' );
	$edge  = (int) $now->format( 'H' ) * 60 + (int) $now->format( 'i' ) + $cfg['lead'] * 60;

	foreach ( array_unique( $ids ) as $id ) {
		foreach ( $dates as $date ) {
			$busy_day = isset( $taken[ $id ][ $date ] ) ? $taken[ $id ][ $date ] : array();
			$free     = array();

			for ( $start = $cfg['from']; $start + $cfg['step'] <= $cfg['to']; $start += $cfg['step'] ) {
				$end = $start + $cfg['step'];

				// Сегодняшние окна, до которых человек уже не успеет доехать,
				// показывать незачем.
				if ( $date === $today && $start < $edge ) {
					continue;
				}

				$ok = true;

				foreach ( $busy_day as $range ) {
					if ( $start < $range[1] && $end > $range[0] ) {
						$ok = false;
						break;
					}
				}

				if ( $ok ) {
					$free[] = map_hhmm( $start );
				}
			}

			if ( $free ) {
				$cache[ $id ][ $date ] = $free;
			}
		}
	}

	return $cache;
}

/**
 * ID педагога в CRM.
 *
 * Сначала смотрим поле в карточке, потом ищем по имени: заводить номер руками
 * на каждого педагога заказчику незачем, пока имена совпадают.
 *
 * @param WP_Post|int $post Запись педагога.
 * @return int 0 — сопоставить не удалось.
 */
function map_teacher_crm_id( $post ) {
	$post = get_post( $post );

	if ( ! $post ) {
		return 0;
	}

	$manual = (int) map_field( 'crm_teacher_id', $post->ID, 0 );

	if ( $manual ) {
		return $manual;
	}

	static $by_name = null;

	if ( null === $by_name ) {
		$by_name  = array();
		$managers = MAP_Moyklass::managers();

		if ( ! is_wp_error( $managers ) ) {
			foreach ( (array) $managers as $manager ) {
				if ( ! empty( $manager['name'] ) ) {
					$by_name[ map_name_key( $manager['name'] ) ] = (int) $manager['id'];
				}
			}
		}
	}

	$key = map_name_key( $post->post_title );

	return isset( $by_name[ $key ] ) ? $by_name[ $key ] : 0;
}

/**
 * Имя в виде, пригодном для сравнения.
 *
 * @param string $name Имя.
 * @return string
 */
function map_name_key( $name ) {
	$name = preg_replace( '/\s+/u', ' ', trim( (string) $name ) );
	$name = str_replace( 'ё', 'е', function_exists( 'mb_strtolower' ) ? mb_strtolower( $name ) : strtolower( $name ) );

	return $name;
}

/**
 * Ответила ли CRM на запрос расписания.
 *
 * Без этого «окон нет» и «CRM молчит» выглядят одинаково, а сказать посетителю
 * нужно разное: в первом случае — что времени пока не добавили, во втором —
 * ничего, пусть остаётся расписание из карточки.
 *
 * @param bool|null $set true — ответила, null — сбросить отметку.
 * @return bool
 */
function map_slots_answered( $set = false ) {
	static $ok = false;

	if ( null === $set ) {
		$ok = false;
	} elseif ( true === $set ) {
		$ok = true;
	}

	return $ok;
}

/**
 * Что показать в карточке педагога.
 *
 * free — есть свободные окна, busy — CRM знает педагога, но свободного
 * времени нет, none — педагога в CRM не нашли, off — блок выключен в карточке.
 *
 * @param WP_Post|int $post Запись педагога.
 * @return string
 */
function map_teacher_slot_state( $post ) {
	$post = get_post( $post );

	// Галочка в карточке: к этому педагогу через сайт не записывают.
	if ( $post && map_field( 'hide_slots', $post->ID ) ) {
		return 'off';
	}

	$id = map_teacher_crm_id( $post );

	if ( ! $id ) {
		return 'none';
	}

	$all = map_slots_all();

	if ( ! empty( $all[ $id ] ) ) {
		return 'free';
	}

	// Педагог в CRM есть, а окон нет: либо всё занято, либо рабочее время
	// на эти дни не задали. Посетителю это одно и то же — записаться не на что.
	return 'busy';
}

/**
 * Свободные окна одного педагога — в виде, готовом для показа.
 *
 * @param WP_Post|int $post Запись педагога.
 * @return array<int,array{date:string,label:string,weekday:string,times:string[]}>
 */
function map_teacher_slots( $post ) {
	$id = map_teacher_crm_id( $post );

	if ( ! $id ) {
		return array();
	}

	$all = map_slots_all();

	if ( empty( $all[ $id ] ) ) {
		return array();
	}

	$out = array();

	foreach ( $all[ $id ] as $date => $times ) {
		$stamp = strtotime( $date . ' 12:00:00' );

		$out[] = array(
			'date'    => $date,
			'label'   => wp_date( 'j F', $stamp ),
			'weekday' => wp_date( 'D', $stamp ),
			'times'   => $times,
		);
	}

	return $out;
}
