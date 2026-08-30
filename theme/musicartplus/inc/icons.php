<?php
/**
 * Иконки темы.
 *
 * Файл сгенерирован из tools/generator.py — правьте генератор и пересоберите:
 * python3 tools/build-theme-icons.py
 *
 * @package MusicArtPlus
 */

defined( 'ABSPATH' ) || exit;

/**
 * Возвращает разметку SVG-иконки.
 *
 * @param string $name  Ключ иконки.
 * @param string $class Дополнительный CSS-класс.
 * @return string
 */
function map_icon( $name, $class = '' ) {
	static $icons = null;

	if ( null === $icons ) {
		$icons = array(
			'al'         => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M19 12H5M11 18l-6-6 6-6"/></svg>',
			'ar'         => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M5 12h14M13 6l6 6-6 6"/></svg>',
			'book'       => '<svg viewBox="0 0 24 24"><path d="M3.6 4.6h5.8a3.2 3.2 0 013.2 3.2v12a2.6 2.6 0 00-2.6-2.6H3.6z"/><path d="M20.4 4.6h-5.8a3.2 3.2 0 00-3.2 3.2v12a2.6 2.6 0 012.6-2.6h6.4z"/></svg>',
			'cal'        => '<svg viewBox="0 0 24 24"><rect x="3.2" y="5" width="17.6" height="15.4" rx="3"/><path d="M8 2.8v4.4M16 2.8v4.4M3.2 10.4h17.6"/><path d="M8 14.2h.01M12 14.2h.01M16 14.2h.01"/></svg>',
			'cap'        => '<svg viewBox="0 0 24 24"><path d="M12 4.2 22 9l-10 4.8L2 9z"/><path d="M6.4 11.2v4.6c0 1.7 2.5 3 5.6 3s5.6-1.3 5.6-3v-4.6"/></svg>',
			'chat'       => '<svg viewBox="0 0 24 24"><path d="M20.4 12.6c0 3.7-3.6 6.7-8 6.7-1 0-2-.15-2.9-.43L4 21l1.3-3.5c-1.2-1.2-1.9-2.8-1.9-4.6 0-3.7 3.6-6.7 8-6.7s9 3 9 6.4z"/></svg>',
			'check'      => '<svg viewBox="0 0 24 24" aria-hidden="true"><path d="M4 12.6l5.2 5.2L20 6.6"/></svg>',
			'child'      => '<svg viewBox="0 0 24 24"><circle cx="12" cy="7.2" r="3.3"/><path d="M5 20.6c0-3.7 3.1-6.6 7-6.6s7 2.9 7 6.6"/><path d="M18.6 3.4l.7 1.6 1.7.2-1.3 1.2.4 1.7-1.5-.9-1.5.9.4-1.7-1.3-1.2 1.7-.2z"/></svg>',
			'clock'      => '<svg viewBox="0 0 24 24" aria-hidden="true"><circle cx="12" cy="12" r="9"/><path d="M12 6.8v5.4l3.4 2"/></svg>',
			'close'      => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" aria-hidden="true"><path d="M5 5l14 14M19 5L5 19"/></svg>',
			'copy'       => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><rect x="9" y="9" width="12" height="12" rx="2.6"/><path d="M6 15H4.6A1.6 1.6 0 013 13.4V4.6A1.6 1.6 0 014.6 3h8.8A1.6 1.6 0 0115 4.6V6"/></svg>',
			'heart'      => '<svg viewBox="0 0 24 24"><path d="M12 20.4S3.6 15.6 3.6 9.8a4.6 4.6 0 018.4-2.6 4.6 4.6 0 018.4 2.6c0 5.8-8.4 10.6-8.4 10.6z"/></svg>',
			'ig'         => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" aria-hidden="true"><rect x="3" y="3" width="18" height="18" rx="5.2"/><circle cx="12" cy="12" r="4"/><circle cx="17.3" cy="6.7" r="1.15" fill="currentColor" stroke="none"/></svg>',
			'link'       => '<svg viewBox="0 0 24 24"><path d="M10.2 13.8a4 4 0 006 .4l2.4-2.4a4.2 4.2 0 00-6-6l-1.3 1.3"/><path d="M13.8 10.2a4 4 0 00-6-.4L5.4 12.2a4.2 4.2 0 006 6l1.3-1.3"/></svg>',
			'mail'       => '<svg viewBox="0 0 24 24" aria-hidden="true"><rect x="3" y="5" width="18" height="14" rx="3.2"/><path d="M4 7.4l8 5.4 8-5.4"/></svg>',
			'masks'      => '<svg viewBox="0 0 24 24"><path d="M3.6 5.2h9.2v5.6a4.6 4.6 0 01-9.2 0z"/><path d="M11.4 5.2h9v5.6a4.6 4.6 0 01-6 4.4"/><path d="M6.4 8h.01M10 8h.01"/></svg>',
			'mic'        => '<svg viewBox="0 0 24 24"><rect x="9" y="2.8" width="6" height="11" rx="3"/><path d="M5.6 11.4a6.4 6.4 0 0012.8 0M12 17.8v3.4M8.6 21.2h6.8"/></svg>',
			'note'       => '<svg viewBox="0 0 24 24"><circle cx="6.6" cy="17.4" r="2.9"/><circle cx="17.6" cy="15" r="2.9"/><path d="M9.5 17.4V6.2l11-2.2V15"/></svg>',
			'online'     => '<svg viewBox="0 0 24 24"><rect x="2.6" y="4.4" width="18.8" height="12.4" rx="2.6"/><path d="M8 20.6h8M12 16.8v3.8"/><path d="M10.4 8.6l4.2 2.4-4.2 2.4z"/></svg>',
			'palette'    => '<svg viewBox="0 0 24 24"><path d="M12 3a9 9 0 100 18c1.2 0 1.9-1 1.5-2-.4-1.2.4-2.2 1.7-2.2h1.6a4.2 4.2 0 004.2-4.2C21 7.3 16.8 3 12 3z"/><circle cx="7.6" cy="11.4" r="1"/><circle cx="10.6" cy="7.6" r="1"/><circle cx="15.2" cy="8.4" r="1"/></svg>',
			'person'     => '<svg viewBox="0 0 24 24"><circle cx="12" cy="8" r="3.6"/><path d="M4.8 20.4c0-3.7 3.2-6.6 7.2-6.6s7.2 2.9 7.2 6.6"/></svg>',
			'phone'      => '<svg viewBox="0 0 24 24" aria-hidden="true"><path d="M6.6 3h3l1.5 4-2 1.5a12.2 12.2 0 006.4 6.4l1.5-2 4 1.5v3a2 2 0 01-2.2 2A17.2 17.2 0 014.6 5.2 2 2 0 016.6 3z"/></svg>',
			'piano'      => '<svg viewBox="0 0 24 24"><rect x="2.6" y="4.4" width="18.8" height="15.2" rx="2.6"/><path d="M9 4.4v9M15 4.4v9M2.6 13.4h18.8"/></svg>',
			'pin'        => '<svg viewBox="0 0 24 24" aria-hidden="true"><path d="M12 21.2s7-5.7 7-11.2a7 7 0 10-14 0c0 5.5 7 11.2 7 11.2z"/><circle cx="12" cy="10" r="2.6"/></svg>',
			'pin2'       => '<svg viewBox="0 0 512 512" aria-hidden="true"><path d="M256 0C153.755 0 70.573 83.182 70.573 185.426c0 126.888 165.939 313.167 173.004 321.035 6.636 7.391 18.222 7.378 24.846 0 7.065-7.868 173.004-194.147 173.004-321.035C441.425 83.182 358.244 0 256 0m0 278.719c-51.442 0-93.292-41.851-93.292-93.293S204.559 92.134 256 92.134s93.291 41.851 93.291 93.293-41.85 93.292-93.291 93.292"/></svg>',
			'play'       => '<svg viewBox="0 0 24 24" aria-hidden="true"><path d="M7 4.6v14.8L20.2 12z"/></svg>',
			'plus'       => '<svg viewBox="0 0 24 24" aria-hidden="true"><path d="M12 5v14M5 12h14"/></svg>',
			'rt'         => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" aria-hidden="true"><rect x="2.4" y="4.6" width="19.2" height="14.8" rx="4.2"/><path d="M10.2 9.1 15.4 12l-5.2 2.9z" fill="currentColor" stroke="none"/></svg>',
			'scale'      => '<svg viewBox="0 0 24 24"><path d="M12 3.6v16.8M5 20.4h14"/><path d="M4 9.4h6l-3-4.2zM14 9.4h6l-3-4.2z"/><path d="M4 9.4a3 3 0 006 0M14 9.4a3 3 0 006 0"/></svg>',
			'spark'      => '<svg viewBox="0 0 24 24"><path d="M12 2.8l2 5.2 5.2 2-5.2 2-2 5.2-2-5.2-5.2-2 5.2-2z"/><path d="M18.6 15.4l.9 2.3 2.3.9-2.3.9-.9 2.3-.9-2.3-2.3-.9 2.3-.9z"/></svg>',
			'star'       => '<svg viewBox="0 0 24 24" aria-hidden="true"><path d="M12 2.6l2.92 6.02 6.58.92-4.78 4.6 1.16 6.5L12 17.5l-5.88 3.14 1.16-6.5-4.78-4.6 6.58-.92z"/></svg>',
			'tg'         => '<svg viewBox="0 0 24 24" fill="currentColor" aria-hidden="true"><path d="M21.9 4.3 19 20.1c-.2 1-.8 1.2-1.6.8l-4.5-3.3-2.2 2.1c-.25.25-.45.45-.92.45l.33-4.66 8.3-7.5c.36-.32-.08-.5-.56-.18l-10.26 6.46-4.42-1.38c-.96-.3-.98-.96.2-1.42l17.28-6.66c.8-.3 1.5.18 1.24 1.5z"/></svg>',
			'trumpet'    => '<svg viewBox="0 0 24 24"><circle cx="2.8" cy="14.4" r="1.3"/><path d="M4.1 14.4h8.8"/><path d="M12.9 14.4c1.7-3.1 4.4-5 8-5.8v11.6c-3.6-.8-6.3-2.7-8-5.8z"/><path d="M6.9 14.4V9.8M9.3 14.4V9.8M11.7 14.4V9.8"/></svg>',
			'users'      => '<svg viewBox="0 0 24 24"><circle cx="9" cy="8" r="3.3"/><path d="M2.6 20.4c0-3.4 2.9-6.2 6.4-6.2s6.4 2.8 6.4 6.2"/><path d="M16.2 5.2a3.3 3.3 0 010 5.9M17.6 14.6c2.3.7 3.8 2.7 3.8 5.1"/></svg>',
			'violin'     => '<svg viewBox="0 0 24 24"><path d="M12 8.8c-2.6 0-3.9 1.3-3.9 2.9 0 1.2 1.4 1.8 1.4 3 0 1.2-2 1.9-2 3.8 0 2 2 3.4 4.5 3.4s4.5-1.4 4.5-3.4c0-1.9-2-2.6-2-3.8 0-1.2 1.4-1.8 1.4-3 0-1.6-1.3-2.9-3.9-2.9z"/><path d="M12 8.8V4.4"/><path d="M12 4.4c0-1.2 1.7-1.5 2-.3.3 1.1-1 1.7-1.7 1.1"/><path d="M10.2 16.3v2.2M13.8 16.3v2.2"/></svg>',
			'vk'         => '<svg viewBox="0 0 24 24" fill="currentColor"><path d="M12.8 16.6c-5.2 0-8.4-3.7-8.5-9.8h2.7c.1 4.5 2.1 6.4 3.6 6.8V6.8h2.5v3.9c1.5-.2 3.1-1.9 3.6-3.9h2.5c-.4 2.4-2 4.1-3.2 4.8 1.2.6 3 2.1 3.7 4.9h-2.7c-.5-1.7-2-3-3.9-3.2v3.3z"/></svg>',
		);
	}

	// Иконки, загруженные через настройки сайта, лежат отдельно:
	// см. inc/icons-custom.php.
	$svg = isset( $icons[ $name ] ) ? $icons[ $name ] : map_icon_custom( $name );

	if ( ! $svg ) {
		return '';
	}

	if ( '' !== $class ) {
		$svg = preg_replace( '/<svg /', '<svg class="' . esc_attr( $class ) . '" ', $svg, 1 );
	}

	return $svg;
}

/**
 * Печатает SVG-иконку.
 *
 * @param string $name  Ключ иконки.
 * @param string $class Дополнительный CSS-класс.
 * @return void
 */
function map_the_icon( $name, $class = '' ) {
	// Статичная разметка SVG из кода темы, экранирование не требуется.
	echo map_icon( $name, $class ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
}
