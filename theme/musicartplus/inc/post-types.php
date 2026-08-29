<?php
/**
 * Типы записей и таксономии.
 *
 * Новости живут в стандартных записях WordPress — так работают привычные
 * рубрики, лента RSS и поиск. Отдельные типы заведены под то, чего в ядре нет.
 *
 * @package MusicArtPlus
 */

defined( 'ABSPATH' ) || exit;

/**
 * Регистрирует типы записей темы.
 *
 * @return void
 */
function map_register_post_types() {
	register_post_type( 'map_teacher', array(
		'labels'              => array(
			'name'               => __( 'Педагоги', 'musicartplus' ),
			'singular_name'      => __( 'Педагог', 'musicartplus' ),
			'add_new'            => __( 'Добавить педагога', 'musicartplus' ),
			'add_new_item'       => __( 'Новый педагог', 'musicartplus' ),
			'edit_item'          => __( 'Редактировать педагога', 'musicartplus' ),
			'search_items'       => __( 'Искать педагогов', 'musicartplus' ),
			'not_found'          => __( 'Педагоги не найдены', 'musicartplus' ),
			'menu_name'          => __( 'Педагоги', 'musicartplus' ),
		),
		// Отдельных страниц у педагогов нет: карточка открывается модальным
		// окном на странице «Педагоги». Оставить тип публичным — значит отдать
		// 9 адресов вида /pedagogi/имя/, которые WordPress отрисует шаблоном
		// новости с пустым телом и заодно положит в карту сайта.
		'public'              => false,
		'show_ui'             => true,
		'publicly_queryable'  => false,
		'exclude_from_search' => true,
		'has_archive'         => false,
		'rewrite'             => false,
		'show_in_rest'        => true,
		'menu_icon'           => 'dashicons-groups',
		'menu_position'       => 21,
		'supports'            => array( 'title', 'thumbnail', 'page-attributes', 'revisions' ),
	) );

	register_post_type( 'map_direction', array(
		'labels'        => array(
			'name'          => __( 'Направления', 'musicartplus' ),
			'singular_name' => __( 'Направление', 'musicartplus' ),
			'add_new_item'  => __( 'Новое направление', 'musicartplus' ),
			'edit_item'     => __( 'Редактировать направление', 'musicartplus' ),
			'menu_name'     => __( 'Направления', 'musicartplus' ),
		),
		// Как и у педагогов: направления показываются плитками на странице
		// «Наши направления», отдельных адресов у них быть не должно.
		'public'              => false,
		'show_ui'             => true,
		'publicly_queryable'  => false,
		'exclude_from_search' => true,
		'has_archive'         => false,
		'rewrite'             => false,
		'show_in_rest'        => true,
		'menu_icon'           => 'dashicons-art',
		'menu_position'       => 22,
		'supports'            => array( 'title', 'editor', 'thumbnail', 'page-attributes', 'revisions' ),
	) );

	register_post_type( 'map_review', array(
		'labels'        => array(
			'name'          => __( 'Отзывы', 'musicartplus' ),
			'singular_name' => __( 'Отзыв', 'musicartplus' ),
			'add_new_item'  => __( 'Новый отзыв', 'musicartplus' ),
			'edit_item'     => __( 'Редактировать отзыв', 'musicartplus' ),
			'menu_name'     => __( 'Отзывы', 'musicartplus' ),
		),
		'public'             => false,
		'show_ui'            => true,
		'show_in_rest'       => true,
		'menu_icon'          => 'dashicons-format-quote',
		'menu_position'      => 23,
		'supports'           => array( 'title', 'editor', 'page-attributes' ),
	) );

	register_post_type( 'map_guest', array(
		'labels'        => array(
			'name'          => __( 'Приглашённые мастера', 'musicartplus' ),
			'singular_name' => __( 'Приглашённый мастер', 'musicartplus' ),
			'add_new_item'  => __( 'Новый мастер', 'musicartplus' ),
			'menu_name'     => __( 'Приглашённые мастера', 'musicartplus' ),
		),
		'public'        => false,
		'show_ui'       => true,
		'show_in_rest'  => true,
		'menu_icon'     => 'dashicons-star-filled',
		'menu_position' => 24,
		'supports'      => array( 'title', 'thumbnail', 'page-attributes' ),
	) );

	// Заявки с сайта. Пишем их у себя до отправки в CRM: если CRM недоступна,
	// заявка не теряется, а уходит позже по расписанию.
	register_post_type( 'map_lead', array(
		'labels'        => array(
			'name'          => __( 'Заявки', 'musicartplus' ),
			'singular_name' => __( 'Заявка', 'musicartplus' ),
			'edit_item'     => __( 'Заявка', 'musicartplus' ),
			'menu_name'     => __( 'Заявки', 'musicartplus' ),
			'not_found'     => __( 'Заявок пока нет', 'musicartplus' ),
		),
		'public'        => false,
		'show_ui'       => true,
		'show_in_rest'  => false,
		'menu_icon'     => 'dashicons-email-alt',
		'menu_position' => 25,
		'supports'      => array( 'title' ),
		'capabilities'  => array( 'create_posts' => 'do_not_allow' ),
		'map_meta_cap'  => true,
	) );
}
add_action( 'init', 'map_register_post_types' );

/**
 * Рубрика новостей — стандартные категории, но с понятным названием в меню.
 *
 * @param array  $args     Аргументы таксономии.
 * @param string $taxonomy Имя таксономии.
 * @return array
 */
function map_category_labels( $args, $taxonomy ) {
	if ( 'category' === $taxonomy ) {
		$args['labels']['menu_name'] = __( 'Рубрики новостей', 'musicartplus' );
	}

	return $args;
}
add_filter( 'register_taxonomy_args', 'map_category_labels', 10, 2 );

/**
 * Сортирует служебные типы по полю «Порядок».
 *
 * @param WP_Query $query Запрос.
 * @return void
 */
function map_order_admin_lists( $query ) {
	if ( ! is_admin() || ! $query->is_main_query() ) {
		return;
	}

	$ordered = array( 'map_teacher', 'map_direction', 'map_review', 'map_guest' );

	if ( in_array( $query->get( 'post_type' ), $ordered, true ) && ! $query->get( 'orderby' ) ) {
		$query->set( 'orderby', 'menu_order title' );
		$query->set( 'order', 'ASC' );
	}
}
add_action( 'pre_get_posts', 'map_order_admin_lists' );

/**
 * Возвращает записи служебного типа в порядке, заданном в админке.
 *
 * @param string $type  Тип записи.
 * @param int    $limit Сколько вернуть (-1 — все).
 * @return WP_Post[]
 */
function map_get_items( $type, $limit = -1 ) {
	$posts = get_posts( array(
		'post_type'        => $type,
		'posts_per_page'   => $limit,
		'orderby'          => array( 'menu_order' => 'ASC', 'date' => 'ASC' ),
		'suppress_filters' => false,
		'no_found_rows'    => true,
	) );

	return $posts ? $posts : array();
}

/**
 * Переадресует старые адреса педагогов и направлений.
 *
 * До версии 1.0.1 эти типы были публичными, и адреса вида /pedagogi/имя/
 * могли попасть в закладки и в поисковую выдачу. Отдавать по ним 404 незачем —
 * ведём на страницу со списком.
 *
 * @return void
 */
function map_redirect_legacy_cpt_urls() {
	if ( ! is_404() ) {
		return;
	}

	$path = isset( $_SERVER['REQUEST_URI'] ) ? wp_parse_url( sanitize_text_field( wp_unslash( $_SERVER['REQUEST_URI'] ) ), PHP_URL_PATH ) : '';

	if ( ! $path ) {
		return;
	}

	if ( preg_match( '#^/pedagogi/#', $path ) ) {
		$page = map_page_by_template( 'page-teachers.php' );
		wp_safe_redirect( $page ? get_permalink( $page ) : home_url( '/' ), 301 );
		exit;
	}

	if ( preg_match( '#^/napravleniya/([^/]+)/?#', $path, $m ) ) {
		$page = map_page_by_template( 'page-directions.php' );
		$url  = $page ? get_permalink( $page ) . '#dir-' . sanitize_title( $m[1] ) : home_url( '/' );
		wp_safe_redirect( $url, 301 );
		exit;
	}
}
add_action( 'template_redirect', 'map_redirect_legacy_cpt_urls' );

/**
 * Направления, отмеченные для показа на главной.
 *
 * @param int $limit Сколько вернуть.
 * @return WP_Post[]
 */
function map_get_featured_directions( $limit = 8 ) {
	$featured = get_posts( array(
		'post_type'      => 'map_direction',
		'posts_per_page' => $limit,
		'orderby'        => array( 'menu_order' => 'ASC', 'date' => 'ASC' ),
		'no_found_rows'  => true,
		'meta_query'     => array( // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_query
			array(
				'key'     => 'dir_featured',
				'value'   => '1',
				'compare' => '=',
			),
		),
	) );

	if ( $featured ) {
		return $featured;
	}

	// Ни одно направление не отмечено — показываем начало списка.
	return map_get_items( 'map_direction', $limit );
}
