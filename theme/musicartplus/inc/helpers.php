<?php
/**
 * Хелперы шаблонов.
 *
 * @package MusicArtPlus
 */

defined( 'ABSPATH' ) || exit;

/**
 * Читает настройку темы со страницы опций ACF.
 *
 * Работает и без ACF: тогда возвращается значение по умолчанию, а сайт
 * продолжает выводить корректные реквизиты.
 *
 * @param string $key     Имя поля.
 * @param mixed  $default Значение по умолчанию.
 * @return mixed
 */
function map_opt( $key, $default = '' ) {
	static $cache = array();

	if ( array_key_exists( $key, $cache ) ) {
		return $cache[ $key ];
	}

	$value = '';

	if ( function_exists( 'get_field' ) ) {
		$value = get_field( $key, 'option' );
	}

	if ( null === $value || '' === $value || array() === $value ) {
		$defaults = map_defaults();
		$value    = isset( $defaults[ $key ] ) ? $defaults[ $key ] : $default;
	}

	$cache[ $key ] = $value;

	return $value;
}

/**
 * Телефон в виде, пригодном для href="tel:".
 *
 * @return string
 */
function map_phone_href() {
	$raw = map_opt( 'phone_href' );

	if ( ! $raw ) {
		$raw = map_opt( 'phone' );
	}

	$digits = preg_replace( '/[^\d+]/', '', (string) $raw );

	return $digits ? $digits : '+79031025111';
}

/**
 * Ссылка на онлайн-запись в «Моём классе».
 *
 * @param string $teacher Слаг педагога, если запись к конкретному педагогу.
 * @return string
 */
function map_crm_url( $teacher = '' ) {
	$base = map_opt( 'crm_url', 'https://app.moyklass.com/school/musicartplus' );

	if ( $teacher ) {
		$base = add_query_arg( 'teacher', rawurlencode( $teacher ), $base );
	}

	return $base;
}

/**
 * Блок иконок соцсетей.
 *
 * @param string $class Дополнительный класс контейнера.
 * @return string
 */
function map_socials( $class = '' ) {
	$links = array(
		'telegram'  => array( 'tg', 'Telegram' ),
		'instagram' => array( 'ig', 'Instagram' ),
		'rutube'    => array( 'rt', 'Rutube' ),
	);

	$out = '';

	foreach ( $links as $option => $meta ) {
		$url = map_opt( $option );

		if ( ! $url ) {
			continue;
		}

		$out .= sprintf(
			'<a href="%1$s" target="_blank" rel="noopener" aria-label="%2$s">%3$s</a>',
			esc_url( $url ),
			esc_attr( $meta[1] ),
			map_icon( $meta[0] )
		);
	}

	if ( ! $out ) {
		return '';
	}

	return '<div class="' . esc_attr( trim( 'socials ' . $class ) ) . '">' . $out . '</div>';
}

/**
 * Атрибуты кнопки, открывающей модальное окно записи.
 *
 * Ссылка ведёт в CRM: если JavaScript не сработает, посетитель всё равно
 * попадёт на страницу записи.
 *
 * @param string $teacher Слаг педагога.
 * @return string
 */
function map_cta_attrs( $teacher = '' ) {
	return sprintf(
		' href="%s" data-crm="%s"',
		esc_url( map_crm_url( $teacher ) ),
		esc_attr( $teacher ? $teacher : 'true' )
	);
}

/**
 * Класс отложенного появления блока при прокрутке.
 *
 * @param int $delay Шаг задержки (0–4).
 * @return string
 */
function map_reveal( $delay = 0 ) {
	$out = ' class="reveal"';

	if ( $delay > 0 ) {
		$out = ' class="reveal" data-delay="' . (int) $delay . '"';
	}

	return $out;
}

/**
 * Разбирает многострочное поле в массив непустых строк.
 *
 * @param string $value Значение поля.
 * @return string[]
 */
function map_lines( $value ) {
	if ( is_array( $value ) ) {
		return array_values( array_filter( array_map( 'trim', $value ), 'strlen' ) );
	}

	$lines = preg_split( '/\r\n|\r|\n/', (string) $value );

	return array_values( array_filter( array_map( 'trim', (array) $lines ), 'strlen' ) );
}

/**
 * Безопасно печатает подготовленную разметку темы.
 *
 * @param string $html Разметка.
 * @return void
 */
function map_html( $html ) {
	echo wp_kses( $html, map_allowed_html() );
}

/**
 * Разрешённые теги для вывода пользовательской разметки.
 *
 * @return array<string,array>
 */
function map_allowed_html() {
	static $allowed = null;

	if ( null !== $allowed ) {
		return $allowed;
	}

	$allowed = wp_kses_allowed_html( 'post' );

	$svg = array(
		'svg'      => array(
			'class'       => true,
			'viewbox'     => true,
			'fill'        => true,
			'stroke'      => true,
			'stroke-width' => true,
			'aria-hidden' => true,
			'width'       => true,
			'height'      => true,
			'xmlns'       => true,
		),
		'path'     => array( 'd' => true, 'fill' => true, 'stroke' => true ),
		'circle'   => array( 'cx' => true, 'cy' => true, 'r' => true, 'fill' => true ),
		'rect'     => array( 'x' => true, 'y' => true, 'width' => true, 'height' => true, 'rx' => true ),
		'line'     => array( 'x1' => true, 'y1' => true, 'x2' => true, 'y2' => true ),
		'polyline' => array( 'points' => true ),
	);

	$allowed = array_merge( $allowed, $svg );

	return $allowed;
}
