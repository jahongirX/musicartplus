<?php
/**
 * Свои иконки из админки.
 *
 * Набор в inc/icons.php задан кодом, и добавить туда картинку из Flaticon
 * можно только правкой темы. Здесь то же самое делается через «Настройки
 * сайта → Иконки»: загруженный SVG появляется во всех списках выбора иконки
 * наравне со встроенными.
 *
 * Файл вставляется в страницу как есть — иначе иконка не подхватит цвет
 * текста, — поэтому разметка проходит через белый список тегов и атрибутов.
 *
 * @package MusicArtPlus
 */

defined( 'ABSPATH' ) || exit;

/**
 * Префикс ключа загруженной иконки.
 */
const MAP_ICON_UPLOAD = 'upload:';

/**
 * Разрешённые теги внутри SVG.
 *
 * @var string[]
 */
const MAP_SVG_TAGS = array(
	'svg', 'g', 'path', 'circle', 'ellipse', 'rect', 'line', 'polyline',
	'polygon', 'defs', 'lineargradient', 'radialgradient', 'stop', 'clippath',
	'mask', 'symbol', 'use', 'title', 'desc', 'text', 'tspan',
);

/**
 * Разрешённые атрибуты.
 *
 * @var string[]
 */
const MAP_SVG_ATTRS = array(
	'd', 'fill', 'fill-rule', 'fill-opacity', 'stroke', 'stroke-width',
	'stroke-linecap', 'stroke-linejoin', 'stroke-miterlimit', 'stroke-dasharray',
	'stroke-opacity', 'opacity', 'cx', 'cy', 'r', 'rx', 'ry', 'x', 'y',
	'x1', 'x2', 'y1', 'y2', 'width', 'height', 'points', 'transform',
	'viewbox', 'xmlns', 'preserveaspectratio', 'clip-rule', 'clip-path',
	'mask', 'offset', 'stop-color', 'stop-opacity', 'gradientunits',
	'gradienttransform', 'id', 'class', 'font-size', 'font-family',
	'text-anchor', 'aria-hidden', 'focusable', 'role',
);

/**
 * Иконки, загруженные через настройки сайта.
 *
 * @return array<string,string> Ключ вида upload:12 => название.
 */
function map_icon_uploads() {
	static $list = null;

	if ( null !== $list ) {
		return $list;
	}

	$found = array();

	if ( ! function_exists( 'get_field' ) ) {
		return $found;
	}

	foreach ( (array) get_field( 'icon_set', 'option' ) as $item ) {
		$id = isset( $item['icon_file'] ) ? (int) ( is_array( $item['icon_file'] ) ? $item['icon_file']['ID'] : $item['icon_file'] ) : 0;

		if ( ! $id ) {
			continue;
		}

		$label = isset( $item['icon_name'] ) && $item['icon_name'] ? $item['icon_name'] : get_the_title( $id );

		$found[ MAP_ICON_UPLOAD . $id ] = $label;
	}

	// До acf/init поля ещё не зарегистрированы и список выйдет пустым —
	// такой ответ запоминать нельзя.
	if ( did_action( 'acf/init' ) ) {
		$list = $found;
	}

	return $found;
}

/**
 * Разметка загруженной иконки.
 *
 * @param string $name Ключ вида upload:12.
 * @return string Пустая строка, если это не загруженная иконка.
 */
function map_icon_custom( $name ) {
	if ( 0 !== strpos( (string) $name, MAP_ICON_UPLOAD ) ) {
		return '';
	}

	$id = (int) substr( $name, strlen( MAP_ICON_UPLOAD ) );

	if ( ! $id ) {
		return '';
	}

	$path = get_attached_file( $id );

	if ( ! $path || ! is_readable( $path ) ) {
		return '';
	}

	// Чистка стоит дороже вывода, а файл меняется редко — держим результат
	// в мета-поле вложения и пересобираем только после замены файла.
	$stamp  = (string) filemtime( $path ) . ':' . (string) filesize( $path );
	$cached = get_post_meta( $id, '_map_icon_svg', true );

	if ( is_array( $cached ) && isset( $cached['stamp'] ) && $cached['stamp'] === $stamp ) {
		return $cached['svg'];
	}

	$svg = map_svg_sanitize( file_get_contents( $path ) ); // phpcs:ignore WordPress.WP.AlternativeFunctions.file_get_contents_file_get_contents -- локальный файл медиатеки.

	update_post_meta( $id, '_map_icon_svg', array( 'stamp' => $stamp, 'svg' => $svg ) );

	return $svg;
}

/**
 * Оставляет в SVG только безопасную разметку.
 *
 * @param string $svg Исходный файл.
 * @return string
 */
function map_svg_sanitize( $svg ) {
	$svg = (string) $svg;

	if ( '' === trim( $svg ) ) {
		return '';
	}

	// Внешние сущности — путь к чтению файлов сервера; отключаем загрузку сети
	// и не разворачиваем сущности вовсе.
	$doc = new DOMDocument();
	$doc->preserveWhiteSpace = false;

	$prev = libxml_use_internal_errors( true );
	$ok   = $doc->loadXML( $svg, LIBXML_NONET | LIBXML_NOBLANKS );
	libxml_clear_errors();
	libxml_use_internal_errors( $prev );

	if ( ! $ok || ! $doc->documentElement || 'svg' !== strtolower( $doc->documentElement->nodeName ) ) {
		return '';
	}

	map_svg_clean_node( $doc->documentElement );

	$root = $doc->documentElement;

	// Размер задаётся стилями темы, поэтому жёсткие width/height убираем,
	// а viewBox оставляем — без него иконка не масштабируется.
	$root->removeAttribute( 'width' );
	$root->removeAttribute( 'height' );
	$root->setAttribute( 'aria-hidden', 'true' );

	$out = $doc->saveXML( $root );

	return is_string( $out ) ? $out : '';
}

/**
 * Рекурсивно вычищает узел: лишние теги и атрибуты.
 *
 * @param DOMElement $node Узел.
 * @return void
 */
function map_svg_clean_node( $node ) {
	$queue = iterator_to_array( $node->childNodes );

	while ( $queue ) {
		$child = array_shift( $queue );

		if ( XML_ELEMENT_NODE !== $child->nodeType ) {
			if ( XML_TEXT_NODE !== $child->nodeType && XML_CDATA_SECTION_NODE !== $child->nodeType ) {
				$node->removeChild( $child );
			}
			continue;
		}

		if ( ! in_array( strtolower( $child->nodeName ), MAP_SVG_TAGS, true ) ) {
			// Ссылку и <switch> разворачиваем, а не выбрасываем: внутри обычно
			// лежит сама фигура, и удаление узла стёрло бы рисунок целиком.
			// Перенесённое кладём обратно в очередь — его тоже надо проверить.
			if ( in_array( strtolower( $child->nodeName ), array( 'a', 'switch' ), true ) ) {
				foreach ( iterator_to_array( $child->childNodes ) as $inner ) {
					$node->insertBefore( $inner, $child );
					$queue[] = $inner;
				}
			}

			$node->removeChild( $child );
			continue;
		}

		map_svg_clean_node( $child );
	}

	foreach ( iterator_to_array( $node->attributes ) as $attr ) {
		$name = strtolower( $attr->nodeName );

		// Ссылка допустима только внутрь самой иконки: <use href="#id">.
		if ( 'href' === $name || 'xlink:href' === $name ) {
			if ( 0 !== strpos( trim( $attr->nodeValue ), '#' ) ) {
				$node->removeAttribute( $attr->nodeName );
			}
			continue;
		}

		if ( ! in_array( $name, MAP_SVG_ATTRS, true ) ) {
			$node->removeAttribute( $attr->nodeName );
		}
	}
}

/**
 * Разрешает загрузку SVG.
 *
 * Только администратору: файл попадает на страницу как разметка, и доверять
 * его содержимому можно ровно настолько, насколько доверяем автору.
 *
 * @param array<string,string> $mimes Типы файлов.
 * @return array<string,string>
 */
function map_allow_svg_upload( $mimes ) {
	if ( current_user_can( 'manage_options' ) ) {
		$mimes['svg'] = 'image/svg+xml';
	}

	return $mimes;
}
add_filter( 'upload_mimes', 'map_allow_svg_upload' );

/**
 * Проверка типа: WordPress не узнаёт SVG по содержимому и отдаёт пустой тип.
 *
 * @param array<string,mixed> $data     Результат проверки.
 * @param string              $file     Путь к файлу.
 * @param string              $filename Имя файла.
 * @return array<string,mixed>
 */
function map_check_svg_filetype( $data, $file, $filename ) {
	if ( ! empty( $data['ext'] ) || ! current_user_can( 'manage_options' ) ) {
		return $data;
	}

	if ( 'svg' !== strtolower( (string) pathinfo( $filename, PATHINFO_EXTENSION ) ) ) {
		return $data;
	}

	// Файл принимаем, только если это действительно разбираемый SVG.
	if ( ! map_svg_sanitize( file_get_contents( $file ) ) ) { // phpcs:ignore WordPress.WP.AlternativeFunctions.file_get_contents_file_get_contents -- временный файл загрузки.
		return $data;
	}

	$data['ext']  = 'svg';
	$data['type'] = 'image/svg+xml';

	return $data;
}
add_filter( 'wp_check_filetype_and_ext', 'map_check_svg_filetype', 10, 3 );

/**
 * Показывает загруженный SVG в медиатеке.
 *
 * Без размеров WordPress рисует вместо превью серый прямоугольник.
 *
 * @param array<string,mixed>|false $image      Данные превью.
 * @param int                       $attachment ID вложения.
 * @return array<string,mixed>|false
 */
function map_svg_thumb( $image, $attachment ) {
	if ( $image || 'image/svg+xml' !== get_post_mime_type( $attachment ) ) {
		return $image;
	}

	$url = wp_get_attachment_url( $attachment );

	return $url ? array( $url, 60, 60, false ) : $image;
}
add_filter( 'wp_get_attachment_image_src', 'map_svg_thumb', 10, 2 );
