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
function map_lines( $value, $default = array() ) {
	if ( is_array( $value ) ) {
		$lines = array_values( array_filter( array_map( 'trim', $value ), 'strlen' ) );

		return $lines ? $lines : $default;
	}

	$lines = preg_split( '/\r\n|\r|\n/', (string) $value );
	$lines = array_values( array_filter( array_map( 'trim', (array) $lines ), 'strlen' ) );

	return $lines ? $lines : $default;
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

/**
 * Подзаголовок внутренней страницы.
 *
 * Берём только отрывок, введённый руками. get_the_excerpt() при пустом
 * отрывке возвращает машинную обрезку контента с многоточием — в шапке
 * страницы это выглядит как дубль первого абзаца.
 *
 * @param int $post_id ID страницы.
 * @return string
 */
function map_page_subtitle( $post_id = 0 ) {
	$post = get_post( $post_id ? $post_id : get_the_ID() );

	if ( ! $post ) {
		return '';
	}

	return trim( (string) $post->post_excerpt );
}

/**
 * Адрес логотипа.
 *
 * Берётся из настроек темы; пока картинку не загрузили — из файлов темы,
 * чтобы шапка не оставалась пустой на свежей установке.
 *
 * @param string $variant 'color' для светлой шапки, 'white' для тёмной.
 * @return string
 */
function map_logo_url( $variant = 'color' ) {
	$white = ( 'white' === $variant );
	$id    = (int) map_opt( $white ? 'logo_white' : 'logo_color', 0 );

	if ( $id ) {
		$url = wp_get_attachment_image_url( $id, 'full' );

		if ( $url ) {
			return $url;
		}
	}

	return map_asset( 'assets/img/ui/logo-' . ( $white ? 'white' : 'color' ) . '.png' );
}

/**
 * Надпись на кнопке записи.
 *
 * @param bool $short Короткий вариант — для шапки, где места мало.
 * @return string
 */
function map_cta_label( $short = false ) {
	if ( $short ) {
		return (string) map_opt( 'cta_label_short', __( 'Записаться', 'musicartplus' ) );
	}

	return (string) map_opt( 'cta_label', __( 'Записаться на пробный урок', 'musicartplus' ) );
}

/**
 * Картинка из поля-изображения ACF с запасным вариантом.
 *
 * @param int|string $id       ID вложения (поле возвращает id).
 * @param string     $size     Размер.
 * @param string     $fallback Что подставить, если картинки нет.
 * @return string
 */
function map_image_url( $id, $size = 'full', $fallback = '' ) {
	$id = (int) $id;

	if ( $id ) {
		$url = wp_get_attachment_image_url( $id, $size );

		if ( $url ) {
			return $url;
		}
	}

	return $fallback;
}

/**
 * Поле главной страницы.
 *
 * Секции главной содержат свои циклы (новости, отзывы), внутри которых
 * get_the_ID() указывает уже не на страницу. Поэтому запись берём явно.
 *
 * @param string $key     Имя поля.
 * @param mixed  $default Что вернуть, если поле пустое.
 * @return mixed
 */
function map_home_field( $key, $default = '' ) {
	static $home = null;

	if ( null === $home ) {
		$home = (int) get_option( 'page_on_front' );
	}

	return map_field( $key, $home ? $home : get_the_ID(), $default );
}

/**
 * Заголовок секции, в который можно подставить число.
 *
 * Заказчик волен убрать %d из заголовка — тогда просто вернём текст как есть.
 * Без этой проверки sprintf на строке без плейсхолдера молча съел бы число,
 * а на строке с процентом от акции («Скидка 20%») выдал бы предупреждение.
 *
 * @param string $template Шаблон заголовка.
 * @param int    $count    Число для подстановки.
 * @return string
 */
function map_sec_title( $template, $count ) {
	$template = (string) $template;

	if ( false === strpos( $template, '%d' ) ) {
		return $template;
	}

	return sprintf( $template, (int) $count );
}

/**
 * Поле страницы новостей.
 *
 * На самой странице новостей WordPress не выставляет её как текущую запись,
 * а на странице отдельной новости get_the_ID() указывает на новость. Поэтому
 * запись берём из настроек чтения.
 *
 * @param string $key     Имя поля.
 * @param mixed  $default Что вернуть, если поле пустое.
 * @return mixed
 */
function map_blog_field( $key, $default = '' ) {
	static $blog = null;

	if ( null === $blog ) {
		$blog = (int) get_option( 'page_for_posts' );
	}

	if ( ! $blog ) {
		return $default;
	}

	return map_field( $key, $blog, $default );
}

/**
 * Сокращает ФИО до «Фамилия И. О.».
 *
 * В подписи под видео полное имя не помещается в одну строку, а обрезка
 * многоточием читается хуже инициалов.
 *
 * @param string $name Полное имя.
 * @return string
 */
function map_short_name( $name ) {
	$parts = preg_split( '/\s+/u', trim( (string) $name ), -1, PREG_SPLIT_NO_EMPTY );

	if ( ! $parts ) {
		return '';
	}

	$short = array_shift( $parts );

	foreach ( $parts as $part ) {
		$short .= ' ' . mb_substr( $part, 0, 1 ) . '.';
	}

	return $short;
}

/**
 * Подставляет в текст ссылку на фонд со знаком.
 *
 * В списке преимуществ название фонда — единственное место, где внутри
 * обычной строки нужна ссылка с картинкой. Заводить ради неё поле с
 * редактором значит дать редактору возможность сломать вёрстку строки,
 * поэтому имя фонда подсвечивается по совпадению.
 *
 * @param string $text Строка списка.
 * @return string
 */
function map_fund_link( $text ) {
	$name = (string) map_opt( 'fund_name' );
	$url  = (string) map_opt( 'fund_url' );

	if ( ! $name || ! $url || false === strpos( $text, $name ) ) {
		return $text;
	}

	$logo = map_image_url( map_opt( 'fund_logo' ), 'full', map_asset( 'assets/img/ui/forteforma.svg' ) );

	$link = sprintf(
		'<a class="ff-inline" href="%s" target="_blank" rel="noopener"><img src="%s" alt="" width="45" height="34">%s</a>',
		esc_url( $url ),
		esc_url( $logo ),
		esc_html( $name )
	);

	return str_replace( $name, $link, $text );
}
