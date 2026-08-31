<?php
/**
 * Иконки, загруженные файлом.
 *
 * Иконка в теме — обычная картинка из медиатеки: её выбирают в поле рядом
 * с текстом, как фотографию. Готовый набор темы лежит файлами
 * в assets/img/icons и попадает в медиатеку при наполнении.
 *
 * SVG вставляется в страницу разметкой, иначе иконка не подхватит цвет
 * темы, — поэтому файл проходит через белый список тегов и атрибутов.
 *
 * @package MusicArtPlus
 */

defined( 'ABSPATH' ) || exit;

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
 * Разметка иконки из медиатеки.
 *
 * @param int $id ID вложения.
 * @return string
 */
function map_icon_custom( $id ) {
	$id = (int) $id;

	if ( ! $id ) {
		return '';
	}

	$path = get_attached_file( $id );

	if ( ! $path || ! is_readable( $path ) ) {
		return '';
	}

	if ( 'image/svg+xml' !== get_post_mime_type( $id ) ) {
		// Растровую иконку вставить разметкой нельзя — она не примет цвет
		// темы, но показать её всё равно лучше, чем пустое место.
		$url = wp_get_attachment_image_url( $id, 'full' );

		return $url ? sprintf( '<img src="%s" alt="" width="32" height="32" loading="lazy">', esc_url( $url ) ) : '';
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

	// Без пространства имён файл не откроется отдельным документом —
	// например, предпросмотром в медиатеке.
	if ( ! $root->hasAttribute( 'xmlns' ) ) {
		$root->setAttribute( 'xmlns', 'http://www.w3.org/2000/svg' );
	}

	map_svg_recolor( $root );
	map_svg_paint( $root );

	$out = $doc->saveXML( $root );

	return is_string( $out ) ? $out : '';
}

/**
 * Одноцветную иконку перекрашивает в цвет темы.
 *
 * Наборы вроде Flaticon отдают файл с зашитым чёрным. На золотой плашке это
 * выглядит чужим, поэтому единственный цвет в файле заменяем на currentColor.
 * Если цветов несколько, это уже рисунок, а не значок — такой не трогаем.
 *
 * @param DOMElement $root Корневой узел SVG.
 * @return void
 */
function map_svg_recolor( $root ) {
	$nodes  = array();
	$colors = array();

	$walk = function ( $node ) use ( &$walk, &$nodes, &$colors ) {
		foreach ( array( 'fill', 'stroke' ) as $name ) {
			if ( ! $node->hasAttribute( $name ) ) {
				continue;
			}

			$value = strtolower( trim( $node->getAttribute( $name ) ) );

			if ( '' === $value || 'none' === $value || 'currentcolor' === $value
				|| 0 === strpos( $value, 'url(' ) ) {
				continue;
			}

			$nodes[]           = array( $node, $name );
			$colors[ $value ] = true;
		}

		foreach ( $node->childNodes as $child ) {
			if ( XML_ELEMENT_NODE === $child->nodeType ) {
				$walk( $child );
			}
		}
	};
	$walk( $root );

	if ( 1 !== count( $colors ) ) {
		return;
	}

	foreach ( $nodes as $item ) {
		$item[0]->setAttribute( $item[1], 'currentColor' );
	}
}

/**
 * Переносит заливку и обводку корня в inline-стиль.
 *
 * Гнёзда иконок в теме задают обводку правилом вида «.dir-tile__ico svg»,
 * а правило CSS сильнее атрибута — чужая заливка от него превратилась бы
 * в контур. Inline-стиль сильнее правила, поэтому файл решает сам.
 *
 * Если корень не сказал ничего, считаем иконку сплошной: так рисуют
 * в большинстве наборов, и на светлой плашке контур вместо силуэта
 * выглядел бы поломкой.
 *
 * @param DOMElement $root Корневой узел SVG.
 * @return void
 */
function map_svg_paint( $root ) {
	$paint = array(
		'fill', 'fill-rule', 'fill-opacity', 'stroke', 'stroke-width',
		'stroke-linecap', 'stroke-linejoin', 'stroke-miterlimit',
		'stroke-dasharray', 'stroke-opacity', 'opacity',
	);

	$style = array();

	foreach ( $paint as $name ) {
		if ( $root->hasAttribute( $name ) ) {
			$style[] = $name . ':' . $root->getAttribute( $name );
			$root->removeAttribute( $name );
		}
	}

	if ( ! $style ) {
		$style = array( 'fill:currentColor', 'stroke:none' );
	}

	$root->setAttribute( 'style', implode( ';', $style ) );
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
 * Право проверяется в map_svg_upload_allowed(): файл попадает на страницу
 * разметкой, и доверять его содержимому можно ровно настолько, насколько
 * доверяем автору.
 *
 * @param array<string,string> $mimes Типы файлов.
 * @return array<string,string>
 */
function map_allow_svg_upload( $mimes ) {
	if ( map_svg_upload_allowed() ) {
		$mimes['svg'] = 'image/svg+xml';
	}

	return $mimes;
}

/**
 * Можно ли сейчас загружать SVG.
 *
 * Обычно — только администратору: файл попадает на страницу разметкой.
 * Наполнение темы кладёт свой набор иконок и поднимает фильтр само,
 * потому что запускается в том числе из WP-CLI, где текущего пользователя
 * нет вовсе.
 *
 * @return bool
 */
function map_svg_upload_allowed() {
	return current_user_can( 'manage_options' ) || (bool) apply_filters( 'map_allow_svg', false );
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
	if ( ! empty( $data['ext'] ) || ! map_svg_upload_allowed() ) {
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
 * Собственные размеры SVG: из width/height или из viewBox.
 *
 * WordPress не умеет читать размер векторного файла и записывает в
 * метаданные пустоту. Из-за этого он же потом рисует превью размером
 * в один пиксель — то есть серый прямоугольник.
 *
 * @param string $path Путь к файлу.
 * @return array{0:float,1:float} Ширина и высота; 0, если не разобрали.
 */
function map_svg_dimensions( $path ) {
	if ( ! $path || ! is_readable( $path ) ) {
		return array( 0, 0 );
	}

	// Заголовка хватает: нужные атрибуты стоят в первом теге.
	$head = (string) file_get_contents( $path, false, null, 0, 4096 ); // phpcs:ignore WordPress.WP.AlternativeFunctions.file_get_contents_file_get_contents -- локальный файл медиатеки.

	if ( ! preg_match( '/<svg\b[^>]*>/i', $head, $tag ) ) {
		return array( 0, 0 );
	}

	$tag = $tag[0];

	$number = static function ( $value ) {
		return preg_match( '/^([\d.]+)(px|pt)?$/i', trim( (string) $value ), $m ) ? (float) $m[1] : 0;
	};

	$width  = preg_match( '/\bwidth="([^"]+)"/i', $tag, $m ) ? $number( $m[1] ) : 0;
	$height = preg_match( '/\bheight="([^"]+)"/i', $tag, $m ) ? $number( $m[1] ) : 0;

	if ( $width > 0 && $height > 0 ) {
		return array( $width, $height );
	}

	if ( preg_match( '/\bviewBox="([^"]+)"/i', $tag, $m ) ) {
		$box = preg_split( '/[\s,]+/', trim( $m[1] ) );

		if ( 4 === count( $box ) && (float) $box[2] > 0 && (float) $box[3] > 0 ) {
			return array( (float) $box[2], (float) $box[3] );
		}
	}

	return array( 0, 0 );
}

/**
 * Дописывает размеры в метаданные загруженного SVG.
 *
 * @param array<string,mixed> $meta Метаданные.
 * @param int                 $id   ID вложения.
 * @return array<string,mixed>
 */
function map_svg_metadata( $meta, $id ) {
	if ( 'image/svg+xml' !== get_post_mime_type( $id ) ) {
		return $meta;
	}

	list( $width, $height ) = map_svg_dimensions( get_attached_file( $id ) );

	if ( $width > 0 && $height > 0 ) {
		$meta['width']  = (int) round( $width );
		$meta['height'] = (int) round( $height );
	}

	$file = get_attached_file( $id );

	if ( $file && empty( $meta['file'] ) ) {
		$uploads      = wp_get_upload_dir();
		$meta['file'] = ltrim( str_replace( $uploads['basedir'], '', $file ), '/' );
	}

	return $meta;
}
add_filter( 'wp_generate_attachment_metadata', 'map_svg_metadata', 10, 2 );

/**
 * Размер картинки для показа SVG.
 *
 * Вектор масштабируется без потерь, поэтому отдаём не собственный размер
 * файла, а тот, который запросили, — сохраняя пропорции.
 *
 * @param int          $id   ID вложения.
 * @param string|array $size Имя размера или пара чисел.
 * @return array{0:int,1:int}
 */
function map_svg_size_box( $id, $size ) {
	list( $width, $height ) = map_svg_dimensions( get_attached_file( $id ) );

	if ( $width <= 0 || $height <= 0 ) {
		$width  = 1;
		$height = 1;
	}

	if ( is_array( $size ) ) {
		$box = array( (int) $size[0], (int) $size[1] );
	} elseif ( 'full' === $size ) {
		$box = array( (int) round( $width ), (int) round( $height ) );
	} else {
		$box = array(
			(int) get_option( $size . '_size_w', 150 ),
			(int) get_option( $size . '_size_h', 150 ),
		);
	}

	if ( $box[0] < 1 || $box[1] < 1 ) {
		$box = array( 150, 150 );
	}

	$scale = min( $box[0] / $width, $box[1] / $height );

	return array( max( 1, (int) round( $width * $scale ) ), max( 1, (int) round( $height * $scale ) ) );
}

/**
 * Показывает загруженный SVG вместо серого прямоугольника.
 *
 * @param array<string,mixed>|false $image Данные превью.
 * @param int                       $id    ID вложения.
 * @param string|array              $size  Запрошенный размер.
 * @return array<string,mixed>|false
 */
function map_svg_thumb( $image, $id, $size ) {
	if ( 'image/svg+xml' !== get_post_mime_type( $id ) ) {
		return $image;
	}

	$url = wp_get_attachment_url( $id );

	if ( ! $url ) {
		return $image;
	}

	$box = map_svg_size_box( $id, $size );

	return array( $url, $box[0], $box[1], false );
}
add_filter( 'wp_get_attachment_image_src', 'map_svg_thumb', 10, 3 );

/**
 * Отдаёт медиатеке набор размеров для SVG.
 *
 * Поле картинки в ACF строит превью на стороне браузера и берёт адрес из
 * sizes[…].url. У векторного файла этого списка нет — отсюда пустая рамка
 * вместо иконки.
 *
 * @param array<string,mixed> $response Данные вложения для JS.
 * @param WP_Post             $post     Вложение.
 * @return array<string,mixed>
 */
function map_svg_js( $response, $post ) {
	if ( 'image/svg+xml' !== $post->post_mime_type ) {
		return $response;
	}

	$url = wp_get_attachment_url( $post->ID );

	if ( ! $url ) {
		return $response;
	}

	$response['icon']  = $url;
	$response['sizes'] = array();

	foreach ( array( 'thumbnail', 'medium', 'large', 'full' ) as $size ) {
		$box = map_svg_size_box( $post->ID, $size );

		$response['sizes'][ $size ] = array(
			'url'         => $url,
			'width'       => $box[0],
			'height'      => $box[1],
			'orientation' => $box[0] >= $box[1] ? 'landscape' : 'portrait',
		);
	}

	$response['width']  = $response['sizes']['full']['width'];
	$response['height'] = $response['sizes']['full']['height'];

	return $response;
}
add_filter( 'wp_prepare_attachment_for_js', 'map_svg_js', 10, 2 );
