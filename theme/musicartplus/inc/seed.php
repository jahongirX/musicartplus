<?php
/**
 * Первичное наполнение сайта.
 *
 * Переносит контент из вёрстки в записи WordPress: страницы, педагогов,
 * направления, отзывы и новости вместе с изображениями. Запускается кнопкой
 * на экране «Интеграция с CRM» и повторный запуск ничего не дублирует —
 * каждая созданная запись помечается служебным полем.
 *
 * @package MusicArtPlus
 */

defined( 'ABSPATH' ) || exit;

/**
 * Мета-ключ, по которому находим ранее созданные записи.
 */
const MAP_SEED_KEY = '_map_seed';

/**
 * Читает файл с контентом.
 *
 * @return array|null
 */
function map_seed_data() {
	$file = MAP_DIR . '/seed/content.json';

	if ( ! is_readable( $file ) ) {
		return null;
	}

	$data = json_decode( file_get_contents( $file ), true ); // phpcs:ignore WordPress.WP.AlternativeFunctions.file_get_contents_file_get_contents -- локальный файл темы.

	return is_array( $data ) ? $data : null;
}

/**
 * Наполняет сайт контентом.
 *
 * @return string Отчёт для показа в админке.
 */
function map_seed_content() {
	$data = map_seed_data();

	if ( ! $data ) {
		return __( 'Файл с контентом не найден: seed/content.json.', 'musicartplus' );
	}

	// Импорт медиа требует функций админки — при вызове из формы они уже есть,
	// но подключим явно, чтобы работал и вызов из WP-CLI.
	require_once ABSPATH . 'wp-admin/includes/file.php';
	require_once ABSPATH . 'wp-admin/includes/media.php';
	require_once ABSPATH . 'wp-admin/includes/image.php';

	// Набор иконок темы — это SVG; загрузку разрешаем на время наполнения.
	add_filter( 'map_allow_svg', '__return_true' );

	// Настройки заполняем раньше страниц: в тексте правовых документов
	// подставляются адрес, телефон и почта центра.
	map_seed_options( $data['options'] );

	$legal = isset( $data['legal'] ) ? $data['legal'] : array();

	$counts = array(
		'pages'      => map_seed_pages() + map_seed_legal_pages( $legal ),
		'teachers'   => map_seed_posts( $data['teachers'], 'map_teacher', 'map_seed_teacher_fields' ),
		'guests'     => map_seed_posts( $data['guests'], 'map_guest', 'map_seed_guest_fields' ),
		'reviews'    => map_seed_reviews( $data['reviews'] ),
		'directions' => map_seed_posts( $data['directions'], 'map_direction', 'map_seed_direction_fields' ),
		'news'       => map_seed_news( $data['news'] ),
	);

	map_seed_front( $data['front'] );
	map_seed_menu();

	if ( isset( $data['about'] ) ) {
		map_seed_about( $data['about'] );
	}

	foreach ( (array) ( isset( $data['pages'] ) ? $data['pages'] : array() ) as $template => $values ) {
		map_seed_page_fields( $template, $values );
	}

	map_seed_favicon();
	map_seed_field_defaults();

	remove_filter( 'map_allow_svg', '__return_true' );

	return sprintf(
		/* translators: список количеств созданных записей. */
		__( 'Готово. Страниц: %1$d, педагогов: %2$d, приглашённых мастеров: %3$d, отзывов: %4$d, направлений: %5$d, новостей: %6$d.', 'musicartplus' ),
		$counts['pages'],
		$counts['teachers'],
		$counts['guests'],
		$counts['reviews'],
		$counts['directions'],
		$counts['news']
	);
}

/**
 * Находит ранее созданную запись по метке.
 *
 * @param string $type Тип записи.
 * @param string $slug Метка.
 * @return int ID записи или 0.
 */
function map_seed_find( $type, $slug ) {
	$found = get_posts( array(
		'post_type'        => $type,
		'post_status'      => 'any',
		'posts_per_page'   => 1,
		'fields'           => 'ids',
		'meta_key'         => MAP_SEED_KEY,
		'meta_value'       => $slug,
		'no_found_rows'    => true,
		'suppress_filters' => false,
	) );

	return $found ? (int) $found[0] : 0;
}

/**
 * Загружает изображение темы в медиатеку.
 *
 * @param string $rel     Путь относительно папки темы.
 * @param int    $post_id К какой записи прикрепить.
 * @return int ID вложения или 0.
 */
function map_seed_image( $rel, $post_id = 0 ) {
	if ( ! $rel ) {
		return 0;
	}

	$path = MAP_DIR . '/' . ltrim( $rel, '/' );

	if ( ! file_exists( $path ) ) {
		return 0;
	}

	$name = basename( $path );

	// Второй запуск не должен плодить копии одного и того же файла.
	$existing = get_posts( array(
		'post_type'      => 'attachment',
		'post_status'    => 'inherit',
		'posts_per_page' => 1,
		'fields'         => 'ids',
		'meta_key'       => '_map_seed_file',
		'meta_value'     => $name,
		'no_found_rows'  => true,
	) );

	if ( $existing ) {
		return (int) $existing[0];
	}

	$tmp = wp_tempnam( $name );

	if ( ! $tmp || ! copy( $path, $tmp ) ) {
		return 0;
	}

	$file = array(
		'name'     => $name,
		'tmp_name' => $tmp,
	);

	$id = media_handle_sideload( $file, $post_id );

	if ( is_wp_error( $id ) ) {
		if ( file_exists( $tmp ) ) {
			wp_delete_file( $tmp );
		}

		return 0;
	}

	update_post_meta( $id, '_map_seed_file', $name );

	return (int) $id;
}

/**
 * Создаёт записи одного типа.
 *
 * @param array    $items    Данные.
 * @param string   $type     Тип записи.
 * @param callable $fields_cb Колбэк, заполняющий поля.
 * @return int Сколько создано.
 */
function map_seed_posts( $items, $type, $fields_cb ) {
	$made = 0;

	foreach ( (array) $items as $order => $item ) {
		$existing = map_seed_find( $type, $item['slug'] );

		if ( $existing ) {
			// Запись уже есть, но в теме могли появиться новые поля — заполняем
			// пустые. Заполненное руками остаётся нетронутым: см. map_seed_set().
			call_user_func( $fields_cb, $existing, $item );
			continue;
		}

		$post_id = wp_insert_post( array(
			'post_type'   => $type,
			'post_status' => 'publish',
			'post_title'  => isset( $item['name'] ) ? $item['name'] : $item['title'],
			'post_name'   => $item['slug'],
			'menu_order'  => $order,
		), true );

		if ( is_wp_error( $post_id ) ) {
			continue;
		}

		update_post_meta( $post_id, MAP_SEED_KEY, $item['slug'] );

		if ( ! empty( $item['photo'] ) ) {
			$image = map_seed_image( $item['photo'], $post_id );

			if ( $image ) {
				set_post_thumbnail( $post_id, $image );
			}
		}

		call_user_func( $fields_cb, $post_id, $item );

		$made++;
	}

	return $made;
}

/**
 * Поля педагога.
 *
 * @param int   $post_id ID записи.
 * @param array $item    Данные.
 * @return void
 */
function map_seed_teacher_fields( $post_id, $item ) {
	map_seed_set( $post_id, 'subject', $item['subject'] );
	map_seed_set( $post_id, 'role', $item['role'] );
	map_seed_set( $post_id, 'short', $item['short'] );
	map_seed_set( $post_id, 'bio', $item['bio'] );
	map_seed_set( $post_id, 'facts', implode( "\n", $item['facts'] ) );

	// Расписание — повторитель ACF; без плагина сохраняем как обычное поле.
	if ( function_exists( 'update_field' ) ) {
		update_field( 'schedule', $item['schedule'], $post_id );
	} else {
		update_post_meta( $post_id, 'schedule', $item['schedule'] );
	}
}

/**
 * Поля приглашённого мастера.
 *
 * @param int   $post_id ID записи.
 * @param array $item    Данные.
 * @return void
 */
function map_seed_guest_fields( $post_id, $item ) {
	map_seed_set( $post_id, 'guest_role', $item['role'] );
	map_seed_set( $post_id, 'guest_note', implode( "\n", $item['facts'] ) );

	if ( ! empty( $item['video'] ) ) {
		map_seed_set( $post_id, 'guest_video', $item['video'] );
	}
}

/**
 * Поля направления.
 *
 * @param int   $post_id ID записи.
 * @param array $item    Данные.
 * @return void
 */
function map_seed_direction_fields( $post_id, $item ) {
	map_seed_set( $post_id, 'dir_icon', map_seed_icon_id( $item['icon'] ) );
	map_seed_set( $post_id, 'dir_short', $item['text'] );
	map_seed_set( $post_id, 'dir_age', $item['age'] );
	map_seed_set( $post_id, 'dir_format', $item['format'] );
	map_seed_set( $post_id, 'dir_duration', $item['duration'] );

	if ( ! empty( $item['short_title'] ) && $item['short_title'] !== $item['title'] ) {
		map_seed_set( $post_id, 'dir_title_short', $item['short_title'] );
	}
	map_seed_set( $post_id, 'dir_featured', 1 );

	// Текст перезаписываем только у пустой записи: у существующей его мог
	// поправить заказчик.
	if ( ! trim( (string) get_post_field( 'post_content', $post_id ) ) ) {
		wp_update_post( array(
			'ID'           => $post_id,
			'post_content' => $item['text'],
		) );
	}
}

/**
 * Создаёт отзывы.
 *
 * @param array $items Данные.
 * @return int
 */
function map_seed_reviews( $items ) {
	$made = 0;

	foreach ( (array) $items as $order => $item ) {
		$slug     = sanitize_title( $item['name'] . '-' . $order );
		$existing = map_seed_find( 'map_review', $slug );

		if ( $existing ) {
			// Запись уже есть — досыпаем поля, которых в теме раньше не было.
			map_seed_set( $existing, 'review_author', $item['name'] );
			map_seed_set( $existing, 'review_role', $item['role'] );
			map_seed_set( $existing, 'review_rating', 5 );

			if ( ! empty( $item['long'] ) ) {
				map_seed_set( $existing, 'review_long', 1 );
			}

			continue;
		}

		$post_id = wp_insert_post( array(
			'post_type'    => 'map_review',
			'post_status'  => 'publish',
			'post_title'   => $item['name'],
			'post_content' => implode( "\n\n", $item['text'] ),
			'menu_order'   => $order,
		), true );

		if ( is_wp_error( $post_id ) ) {
			continue;
		}

		update_post_meta( $post_id, MAP_SEED_KEY, $slug );
		map_seed_set( $post_id, 'review_author', $item['name'] );
		map_seed_set( $post_id, 'review_role', $item['role'] );
		map_seed_set( $post_id, 'review_rating', 5 );

		if ( ! empty( $item['long'] ) ) {
			map_seed_set( $post_id, 'review_long', 1 );
		}

		$made++;
	}

	return $made;
}

/**
 * Создаёт новости.
 *
 * @param array $items Данные.
 * @return int
 */
function map_seed_news( $items ) {
	$made = 0;

	foreach ( (array) $items as $item ) {
		if ( map_seed_find( 'post', $item['slug'] ) ) {
			continue;
		}

		$body = '';

		foreach ( (array) $item['body'] as $paragraph ) {
			$body .= "\n\n" . $paragraph;
		}

		$post_id = wp_insert_post( array(
			'post_type'    => 'post',
			'post_status'  => 'publish',
			'post_title'   => $item['title'],
			'post_name'    => $item['slug'],
			'post_excerpt' => $item['excerpt'],
			'post_content' => trim( $body ),
			'post_date'    => $item['date'],
		), true );

		if ( is_wp_error( $post_id ) ) {
			continue;
		}

		update_post_meta( $post_id, MAP_SEED_KEY, $item['slug'] );
		map_seed_set( $post_id, 'news_lead', $item['excerpt'] );

		if ( ! empty( $item['tag'] ) ) {
			// У иерархических таксономий wp_set_post_terms ждёт ID, а не названия:
			// строку он попытается считать числом и связь не создастся.
			$term = term_exists( $item['tag'], 'category' );

			if ( ! $term ) {
				$term = wp_insert_term( $item['tag'], 'category' );
			}

			if ( ! is_wp_error( $term ) && ! empty( $term['term_id'] ) ) {
				wp_set_post_terms( $post_id, array( (int) $term['term_id'] ), 'category' );
			}
		}

		$image = map_seed_image( $item['image'], $post_id );

		if ( $image ) {
			set_post_thumbnail( $post_id, $image );
		}

		$made++;
	}

	return $made;
}

/**
 * Создаёт страницы сайта и настраивает главную.
 *
 * @return int Сколько страниц создано.
 */
function map_seed_pages() {
	$pages = array(
		'home'       => array( 'Главная', '' ),
		'about'      => array( 'О нас', 'page-about.php' ),
		'directions' => array( 'Наши направления', 'page-directions.php' ),
		'teachers'   => array( 'Педагоги', 'page-teachers.php' ),
		'news'       => array( 'Новости', '' ),
	);

	$made = 0;
	$ids  = array();

	foreach ( $pages as $slug => $meta ) {
		$existing = map_seed_find( 'page', $slug );

		if ( $existing ) {
			$ids[ $slug ] = $existing;
			continue;
		}

		$post_id = wp_insert_post( array(
			'post_type'   => 'page',
			'post_status' => 'publish',
			'post_title'  => $meta[0],
			'post_name'   => 'home' === $slug ? 'glavnaya' : $slug,
		), true );

		if ( is_wp_error( $post_id ) ) {
			continue;
		}

		update_post_meta( $post_id, MAP_SEED_KEY, $slug );

		if ( $meta[1] ) {
			update_post_meta( $post_id, '_wp_page_template', $meta[1] );
		}

		$ids[ $slug ] = $post_id;
		$made++;
	}

	if ( ! empty( $ids['home'] ) && ! empty( $ids['news'] ) ) {
		update_option( 'show_on_front', 'page' );
		update_option( 'page_on_front', $ids['home'] );
		update_option( 'page_for_posts', $ids['news'] );
	}

	return $made;
}

/**
 * Иконка набора темы: кладёт файл в медиатеку и возвращает его номер.
 *
 * Иконка перестала быть ключом в коде — теперь это картинка из медиатеки.
 * Файлы набора лежат в assets/img/icons и попадают туда при наполнении,
 * по одному разу на ключ.
 *
 * @param string $key Имя файла без расширения: piano, violin, check…
 * @return int ID вложения или 0.
 */
function map_seed_icon_id( $key ) {
	static $cache = array();

	$key = preg_replace( '/[^a-z0-9_-]/', '', (string) $key );

	if ( ! $key ) {
		return 0;
	}

	if ( isset( $cache[ $key ] ) ) {
		return $cache[ $key ];
	}

	$id = map_seed_image( 'assets/img/icons/' . $key . '.svg' );

	if ( $id ) {
		update_post_meta( $id, '_map_icon_key', $key );
	}

	$cache[ $key ] = (int) $id;

	return $cache[ $key ];
}

/**
 * Переводит значения полей-иконок из ключей набора в номера вложений.
 *
 * @param mixed $value Значение поля или повторителя.
 * @param string $name Имя поля.
 * @return mixed
 */
function map_seed_icon_value( $value, $name ) {
	if ( is_array( $value ) ) {
		foreach ( $value as $i => $row ) {
			if ( ! is_array( $row ) ) {
				continue;
			}

			foreach ( $row as $sub => $sub_value ) {
				$value[ $i ][ $sub ] = map_seed_icon_value( $sub_value, $sub );
			}
		}

		return $value;
	}

	if ( ! is_string( $value ) || ! $value || ! preg_match( '/(^|_)icon$/', $name ) ) {
		return $value;
	}

	$id = map_seed_icon_id( $value );

	return $id ? $id : $value;
}

/**
 * Значок вкладки: кладём файл темы в медиатеку и назначаем его.
 *
 * @return void
 */
function map_seed_favicon() {
	if ( get_option( 'site_icon' ) || ! function_exists( 'update_field' ) ) {
		return;
	}

	$id = map_seed_image( 'assets/img/ui/favicon.png' );

	if ( ! $id ) {
		return;
	}

	// Поле темы само проставит стандартный «Значок сайта» — см. inc/acf.php.
	update_field( 'favicon', $id, 'option' );
}

/**
 * Правовые страницы и ссылки на них в подвале.
 *
 * Текст здесь — только заглушка: политику и согласие юрист центра пишет сам.
 * Страницы всё равно нужны сразу, иначе в подвале и в форме записи не на что
 * ссылаться, а согласие на обработку данных собирать без документа нельзя.
 *
 * @return int Сколько страниц создано.
 */
function map_seed_legal_pages( $legal = array() ) {
	$pages = array(
		'privacy' => array( __( 'Политика конфиденциальности', 'musicartplus' ), 'politika-konfidencialnosti' ),
		'consent' => array( __( 'Согласие на обработку персональных данных', 'musicartplus' ), 'soglasie-na-obrabotku-dannyh' ),
	);

	$made = 0;
	$ids  = array();

	// WordPress при установке сам создаёт черновик политики. Забираем его
	// себе, иначе на сайте окажутся две страницы с одним смыслом.
	$wp_privacy = (int) get_option( 'wp_page_for_privacy_policy' );

	if ( $wp_privacy && get_post( $wp_privacy ) && ! map_seed_find( 'page', 'privacy' ) ) {
		update_post_meta( $wp_privacy, MAP_SEED_KEY, 'privacy' );

		wp_update_post( array(
			'ID'          => $wp_privacy,
			'post_status' => 'publish',
			'post_name'   => $pages['privacy'][1],
		) );

		// В черновике WordPress лежит рыба про комментарии и Gravatar —
		// к этому сайту она отношения не имеет.
		if ( false !== strpos( (string) get_post_field( 'post_content', $wp_privacy ), 'privacy-policy-tutorial' ) ) {
			wp_update_post( array( 'ID' => $wp_privacy, 'post_content' => '' ) );
		}
	}

	foreach ( $pages as $slug => $meta ) {
		$existing = map_seed_find( 'page', $slug );

		if ( $existing ) {
			$ids[ $slug ] = $existing;
			continue;
		}

		$post_id = wp_insert_post( array(
			'post_type'   => 'page',
			'post_status' => 'publish',
			'post_title'  => $meta[0],
			'post_name'   => $meta[1],
		), true );

		if ( is_wp_error( $post_id ) ) {
			continue;
		}

		update_post_meta( $post_id, MAP_SEED_KEY, $slug );
		$ids[ $slug ] = $post_id;
		$made++;
	}

	if ( ! empty( $ids['privacy'] ) && ! get_option( 'wp_page_for_privacy_policy' ) ) {
		update_option( 'wp_page_for_privacy_policy', $ids['privacy'] );
	}

	if ( ! empty( $ids['consent'] ) && function_exists( 'update_field' ) && ! get_field( 'consent_url', 'option' ) ) {
		// page_link отдаёт адрес по ID — при смене домена ссылка не протухнет.
		update_field( 'consent_url', $ids['consent'], 'option' );
	}

	map_seed_legal_text( $ids, $legal );

	return $made;
}

/**
 * Заполняет правовые страницы текстом.
 *
 * Контакты подставляются из настроек сайта: держать их и в настройках,
 * и в тексте документа значит однажды поменять только в одном месте.
 * Уже написанный текст не трогаем — заказчик мог править его сам.
 *
 * @param array<string,int>    $ids   ID страниц по ключу.
 * @param array<string,string> $legal Заготовки текстов.
 * @return void
 */
function map_seed_legal_text( $ids, $legal ) {
	if ( ! $legal ) {
		return;
	}

	$vars = array(
		'{address}' => map_opt( 'address' ),
		'{phone}'   => map_opt( 'phone' ),
		'{email}'   => map_opt( 'email' ),
		// Относительный адрес: абсолютный протух бы при переезде на домен центра.
		'{privacy}' => ! empty( $ids['privacy'] ) ? wp_make_link_relative( get_permalink( $ids['privacy'] ) ) : '',
	);

	foreach ( $ids as $slug => $post_id ) {
		if ( empty( $legal[ $slug ] ) ) {
			continue;
		}

		if ( trim( wp_strip_all_tags( (string) get_post_field( 'post_content', $post_id ) ) ) ) {
			continue;
		}

		wp_update_post( array(
			'ID'           => $post_id,
			'post_content' => strtr( $legal[ $slug ], $vars ),
		) );
	}
}

/**
 * Заполняет настройки сайта.
 *
 * @param array $options Значения.
 * @return void
 */
function map_seed_options( $options ) {
	if ( ! function_exists( 'update_field' ) ) {
		return;
	}

	foreach ( (array) $options as $key => $value ) {
		if ( '' === get_field( $key, 'option' ) || null === get_field( $key, 'option' ) ) {
			update_field( $key, $value, 'option' );
		}
	}
}

/**
 * Заполняет поля главной страницы.
 *
 * @param array $front Значения.
 * @return void
 */
function map_seed_front( $front ) {
	$home = map_seed_find( 'page', 'home' );

	if ( ! $home || ! function_exists( 'update_field' ) ) {
		return;
	}

	if ( ! get_field( 'hero_title', $home ) ) {
		update_field( 'hero_eyebrow', $front['hero_eyebrow'], $home );
		update_field( 'hero_title', $front['hero_title'], $home );
		update_field( 'hero_text', $front['hero_text'], $home );
		update_field( 'hero_facts', $front['hero_facts'], $home );

		$slides = array();

		foreach ( $front['hero_slides'] as $slide ) {
			$id = map_seed_image( $slide, $home );

			if ( $id ) {
				$slides[] = $id;
			}
		}

		if ( $slides ) {
			update_field( 'hero_slides', $slides, $home );
		}

		$videos = array();

		foreach ( $front['videos'] as $video ) {
			$videos[] = array(
				'title' => $video['title'],
				'url'   => $video['url'],
				'cover' => map_seed_image( $video['cover'], $home ),
			);
		}

		if ( $videos ) {
			update_field( 'videos', $videos, $home );
		}

		update_field( 'about_title', $front['about_title'], $home );
		update_field( 'about_text', wpautop( $front['about_text'] ), $home );
		update_field( 'about_points', implode( "\n", $front['about_points'] ), $home );
		update_field( 'cta_title', $front['cta_title'], $home );
		update_field( 'cta_text', $front['cta_text'], $home );

		$about_image = map_seed_image( $front['about_image'], $home );

		if ( $about_image ) {
			update_field( 'about_image', $about_image, $home );
		}
	}

	// Иконки первого экрана и списка преимуществ. Пустое поле шаблон
	// переживёт — подставит набор темы, — но в админке лучше видеть
	// текущую картинку и менять её на свою.
	foreach ( array( 'hero_eyebrow_icon' => 'pin2', 'about_point_icon' => 'check' ) as $field => $icon ) {
		if ( get_field( $field, $home ) ) {
			continue;
		}

		$id = map_seed_icon_id( $icon );

		if ( $id ) {
			update_field( $field, $id, $home );
		}
	}
}

/**
 * Собирает основное меню, если его ещё нет.
 *
 * @return void
 */
function map_seed_menu() {
	$name = __( 'Основное меню', 'musicartplus' );
	$menu = wp_get_nav_menu_object( $name );

	if ( $menu ) {
		return;
	}

	$menu_id = wp_create_nav_menu( $name );

	if ( is_wp_error( $menu_id ) ) {
		return;
	}

	foreach ( array( 'home', 'about', 'directions', 'teachers', 'news' ) as $slug ) {
		$page_id = map_seed_find( 'page', $slug );

		if ( ! $page_id ) {
			continue;
		}

		wp_update_nav_menu_item( $menu_id, 0, array(
			'menu-item-object-id' => $page_id,
			'menu-item-object'    => 'page',
			'menu-item-type'      => 'post_type',
			'menu-item-status'    => 'publish',
			'menu-item-title'     => get_the_title( $page_id ),
		) );
	}

	$locations = get_theme_mod( 'nav_menu_locations', array() );
	$locations['primary'] = $menu_id;
	$locations['footer']  = $menu_id;
	set_theme_mod( 'nav_menu_locations', $locations );

	map_seed_dirs_menu();
}

/**
 * Меню направлений в подвале.
 *
 * В подвале названия короче, чем в каталоге, и берётся не весь список —
 * поэтому это отдельное меню, а не выборка из направлений.
 *
 * @return void
 */
function map_seed_dirs_menu() {
	$name = __( 'Направления в подвале', 'musicartplus' );

	if ( wp_get_nav_menu_object( $name ) ) {
		return;
	}

	$page = map_page_by_template( 'page-directions.php' );

	if ( ! $page ) {
		return;
	}

	$menu_id = wp_create_nav_menu( $name );

	if ( is_wp_error( $menu_id ) ) {
		return;
	}

	$items = array(
		'piano'  => __( 'Фортепиано', 'musicartplus' ),
		'violin' => __( 'Скрипка', 'musicartplus' ),
		'vocal'  => __( 'Вокал и сцена', 'musicartplus' ),
		'art'    => __( 'Изобразительное искусство', 'musicartplus' ),
		'early'  => __( 'Раннее развитие 3–7 лет', 'musicartplus' ),
	);

	// Адрес храним относительным: у произвольной ссылки WordPress запоминает
	// его как есть, и при переезде на домен заказчика ссылки бы протухли.
	$base = wp_make_link_relative( get_permalink( $page ) );

	foreach ( $items as $slug => $title ) {
		wp_update_nav_menu_item( $menu_id, 0, array(
			'menu-item-type'   => 'custom',
			'menu-item-url'    => $base . '#dir-' . $slug,
			'menu-item-title'  => $title,
			'menu-item-status' => 'publish',
		) );
	}

	$locations = get_theme_mod( 'nav_menu_locations', array() );
	$locations['dirs'] = $menu_id;
	set_theme_mod( 'nav_menu_locations', $locations );
}

/**
 * Пишет значение поля: через ACF, если он есть, иначе в мета.
 *
 * @param int    $post_id ID записи.
 * @param string $key     Имя поля.
 * @param mixed  $value   Значение.
 * @return void
 */
function map_seed_set( $post_id, $key, $value ) {
	if ( '' === $value || null === $value ) {
		return;
	}

	if ( function_exists( 'update_field' ) ) {
		update_field( $key, $value, $post_id );

		return;
	}

	update_post_meta( $post_id, $key, $value );
}

/**
 * Проставляет полям значения по умолчанию из описания группы.
 *
 * Заголовки секций и подписи кнопок заданы прямо в определениях полей
 * (default_value). Шаблоны и так подставят их, если поле пустое, но в админке
 * пустые поля выглядят как «ничего не настроено» — заполняем, чтобы редактору
 * было что править, а не угадывать.
 *
 * Уже заполненное не трогает, поэтому повторный запуск безвреден.
 *
 * @return int Сколько полей заполнено.
 */
function map_seed_field_defaults() {
	if ( ! function_exists( 'acf_get_field_group' ) ) {
		return 0;
	}

	$targets = array(
		'group_map_settings'   => 'option',
		'group_map_front'      => (int) get_option( 'page_on_front' ),
		'group_map_page_news'  => (int) get_option( 'page_for_posts' ),
		'group_map_page_about' => map_page_by_template( 'page-about.php' ),
		'group_map_page_dirs'  => map_page_by_template( 'page-directions.php' ),
		'group_map_page_teach' => map_page_by_template( 'page-teachers.php' ),
	);

	$filled = 0;

	foreach ( $targets as $group_key => $where ) {
		if ( ! $where ) {
			continue;
		}

		$group = acf_get_field_group( $group_key );

		if ( ! $group ) {
			continue;
		}

		foreach ( acf_get_fields( $group ) as $field ) {
			if ( 'tab' === $field['type'] || '' === (string) $field['name'] ) {
				continue;
			}

			$default = isset( $field['default_value'] ) ? $field['default_value'] : '';

			if ( '' === $default || array() === $default ) {
				continue;
			}

			$current = get_field( $field['name'], $where );

			if ( null !== $current && '' !== $current && array() !== $current ) {
				continue;
			}

			update_field( $field['name'], $default, $where );
			$filled++;
		}
	}

	return $filled;
}

/**
 * Наполняет страницу «О нас».
 *
 * Значения-пути к картинкам загружаются в медиатеку, остальное кладётся как
 * есть. Заполняются только пустые поля — введённое заказчиком не трогаем.
 *
 * @param array $about Значения полей.
 * @return void
 */
function map_seed_about( $about ) {
	map_seed_page_fields( 'page-about.php', $about );
}

/**
 * Заполняет поля страницы по её шаблону.
 *
 * Заполняются только пустые поля: то, что заказчик уже правил в админке,
 * повторный запуск не трогает.
 *
 * @param string $template Файл шаблона страницы.
 * @param array  $values   Значения полей.
 * @return void
 */
function map_seed_page_fields( $template, $values ) {
	// Лента новостей выбирается настройкой чтения, шаблона у неё нет.
	$page = 'posts_page' === $template
		? (int) get_option( 'page_for_posts' )
		: map_page_by_template( $template );

	if ( ! $page || ! function_exists( 'update_field' ) ) {
		return;
	}

	// Поля, в которых лежат пути к картинкам — их надо превратить во вложения.
	$images    = array( 'about_hero_image', 'steps_card_image', 'dirs_hero_image', 'teachers_hero_image', 'news_hero_image' );
	$galleries = array( 'intro_gallery', 'mood_gallery', 'shots_gallery' );

	foreach ( (array) $values as $key => $value ) {
		$current = get_field( $key, $page );

		if ( null !== $current && '' !== $current && array() !== $current ) {
			continue;
		}

		if ( in_array( $key, $images, true ) ) {
			$value = map_seed_image( $value, $page );

			if ( ! $value ) {
				continue;
			}
		} elseif ( in_array( $key, $galleries, true ) ) {
			$ids = array();

			foreach ( (array) $value as $rel ) {
				$id = map_seed_image( $rel, $page );

				if ( $id ) {
					$ids[] = $id;
				}
			}

			if ( ! $ids ) {
				continue;
			}

			$value = $ids;
		}

		update_field( $key, map_seed_icon_value( $value, $key ), $page );
	}
}
