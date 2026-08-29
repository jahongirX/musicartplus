<?php
/**
 * Навигация.
 *
 * Меню собирается стандартными средствами WordPress, но выводится в разметке
 * вёрстки: с классами nav__link и стрелками в мобильном меню.
 *
 * @package MusicArtPlus
 */

defined( 'ABSPATH' ) || exit;

/**
 * Выводит основное меню.
 *
 * Если меню в админке не назначено, показываем страницы верхнего уровня —
 * сайт остаётся рабочим сразу после установки темы.
 *
 * @param bool $mobile Разметка для мобильного меню.
 * @return void
 */
function map_nav( $mobile = false ) {
	if ( ! has_nav_menu( 'primary' ) ) {
		map_nav_fallback( $mobile );

		return;
	}

	$GLOBALS['map_nav_mobile'] = $mobile;

	wp_nav_menu( array(
		'theme_location' => 'primary',
		'container'      => false,
		'items_wrap'     => '%3$s',
		'depth'          => 1,
		'fallback_cb'    => false,
		'walker'         => new MAP_Nav_Walker( $mobile ),
	) );

	unset( $GLOBALS['map_nav_mobile'] );
}

/**
 * Меню из страниц, пока в админке ничего не назначено.
 *
 * @param bool $mobile Мобильная разметка.
 * @return void
 */
function map_nav_fallback( $mobile ) {
	$pages = get_pages( array(
		'parent'      => 0,
		'sort_column' => 'menu_order,post_title',
		'number'      => 6,
	) );

	foreach ( $pages as $page ) {
		$current = ( is_page( $page->ID ) || ( is_front_page() && (int) get_option( 'page_on_front' ) === $page->ID ) );

		printf(
			'<a class="%1$s" href="%2$s">%3$s%4$s</a>',
			esc_attr( $mobile ? ( $current ? 'is-active' : '' ) : 'nav__link' . ( $current ? ' is-active' : '' ) ),
			esc_url( get_permalink( $page->ID ) ),
			esc_html( $page->post_title ),
			$mobile ? map_icon( 'ar' ) : '' // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
		);
	}
}

/**
 * Ссылки в подвале.
 *
 * @param string $location Область меню.
 * @return void
 */
function map_footer_menu( $location ) {
	if ( has_nav_menu( $location ) ) {
		wp_nav_menu( array(
			'theme_location' => $location,
			'container'      => false,
			'items_wrap'     => '%3$s',
			'depth'          => 1,
			'fallback_cb'    => false,
			'walker'         => new MAP_Plain_Walker(),
		) );

		return;
	}

	$pages = get_pages( array( 'parent' => 0, 'sort_column' => 'menu_order,post_title', 'number' => 6 ) );

	foreach ( $pages as $page ) {
		printf( '<a href="%s">%s</a>', esc_url( get_permalink( $page->ID ) ), esc_html( $page->post_title ) );
	}
}

/**
 * Ссылки на направления в подвале.
 *
 * @return void
 */
function map_footer_directions() {
	$items = map_get_items( 'map_direction', (int) map_opt( 'footer_directions_count', 5 ) );

	if ( ! $items ) {
		map_footer_menu( 'dirs' );

		return;
	}

	foreach ( $items as $item ) {
		printf(
			'<a href="%s">%s</a>',
			esc_url( map_direction_link( $item ) ),
			esc_html( get_the_title( $item ) )
		);
	}
}

/**
 * Ссылка на направление: якорь на общей странице.
 *
 * @param WP_Post $item Направление.
 * @return string
 */
function map_direction_link( $item ) {
	$page = map_page_by_template( 'page-directions.php' );

	if ( $page ) {
		return get_permalink( $page ) . '#dir-' . $item->post_name;
	}

	// Страницы «Наши направления» ещё нет. Свой адрес у направления не
	// работает — тип записи закрыт от фронтенда, поэтому ведём на главную.
	return home_url( '/#directions' );
}

/**
 * Находит страницу по шаблону.
 *
 * @param string $template Имя файла шаблона.
 * @return int ID страницы или 0.
 */
function map_page_by_template( $template ) {
	$cache_key = 'map_tpl_' . md5( $template );
	$cached    = wp_cache_get( $cache_key, 'musicartplus' );

	if ( false !== $cached ) {
		return (int) $cached;
	}

	$pages = get_posts( array(
		'post_type'      => 'page',
		'posts_per_page' => 1,
		'fields'         => 'ids',
		'meta_key'       => '_wp_page_template',
		'meta_value'     => $template,
		'no_found_rows'  => true,
	) );

	$id = $pages ? (int) $pages[0] : 0;

	wp_cache_set( $cache_key, $id, 'musicartplus', HOUR_IN_SECONDS );

	return $id;
}

/**
 * Ссылки на правовые документы в подвале.
 *
 * @return void
 */
function map_privacy_links() {
	$privacy = get_privacy_policy_url();

	if ( ! $privacy && map_opt( 'privacy_url' ) ) {
		$privacy = map_opt( 'privacy_url' );
	}

	if ( ! $privacy ) {
		return;
	}

	printf(
		'<a href="%s">%s</a>',
		esc_url( $privacy ),
		esc_html__( 'Политика конфиденциальности', 'musicartplus' )
	);
}

/**
 * Обходчик основного меню.
 */
class MAP_Nav_Walker extends Walker_Nav_Menu {

	/**
	 * Нужна ли мобильная разметка.
	 *
	 * @var bool
	 */
	protected $mobile;

	/**
	 * Конструктор.
	 *
	 * @param bool $mobile Мобильная разметка.
	 */
	public function __construct( $mobile = false ) {
		$this->mobile = (bool) $mobile;
	}

	/**
	 * Пункт меню.
	 *
	 * @param string   $output Накопленная разметка.
	 * @param WP_Post  $item   Пункт меню.
	 * @param int      $depth  Уровень вложенности.
	 * @param stdClass $args   Аргументы.
	 * @param int      $id     ID.
	 * @return void
	 */
	public function start_el( &$output, $item, $depth = 0, $args = null, $id = 0 ) {
		$classes = (array) $item->classes;
		$current = in_array( 'current-menu-item', $classes, true ) || in_array( 'current_page_item', $classes, true );

		if ( $this->mobile ) {
			$output .= sprintf(
				'<a href="%1$s"%2$s>%3$s%4$s</a>',
				esc_url( $item->url ),
				$current ? ' class="is-active"' : '',
				esc_html( $item->title ),
				map_icon( 'ar' )
			);

			return;
		}

		$output .= sprintf(
			'<a class="nav__link%1$s" href="%2$s">%3$s</a>',
			$current ? ' is-active' : '',
			esc_url( $item->url ),
			esc_html( $item->title )
		);
	}

	/**
	 * Закрытия пункта не требуется — разметка плоская.
	 *
	 * @param string   $output Разметка.
	 * @param WP_Post  $item   Пункт.
	 * @param int      $depth  Уровень.
	 * @param stdClass $args   Аргументы.
	 * @return void
	 */
	public function end_el( &$output, $item, $depth = 0, $args = null ) {}
}

/**
 * Обходчик простых списков ссылок в подвале.
 */
class MAP_Plain_Walker extends Walker_Nav_Menu {

	/**
	 * Пункт меню.
	 *
	 * @param string   $output Разметка.
	 * @param WP_Post  $item   Пункт.
	 * @param int      $depth  Уровень.
	 * @param stdClass $args   Аргументы.
	 * @param int      $id     ID.
	 * @return void
	 */
	public function start_el( &$output, $item, $depth = 0, $args = null, $id = 0 ) {
		$output .= sprintf( '<a href="%s">%s</a>', esc_url( $item->url ), esc_html( $item->title ) );
	}

	/**
	 * Закрытие не требуется.
	 *
	 * @param string   $output Разметка.
	 * @param WP_Post  $item   Пункт.
	 * @param int      $depth  Уровень.
	 * @param stdClass $args   Аргументы.
	 * @return void
	 */
	public function end_el( &$output, $item, $depth = 0, $args = null ) {}
}
