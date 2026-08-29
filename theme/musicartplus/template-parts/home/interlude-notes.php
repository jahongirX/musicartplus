<?php
/**
 * Разделитель с нотным станом.
 *
 * @package MusicArtPlus
 */

defined( 'ABSPATH' ) || exit;
?>
<div class="interlude">
	<div class="interlude__line draw draw--slow" aria-hidden="true">
		<svg viewBox="0 0 1000 140" preserveAspectRatio="xMidYMid meet">
			<path d="M8 84C120 40 214 118 322 84c108-34 190 34 300 6 92-24 190 30 370-24"/>
			<path d="M322 84V30"/>
			<ellipse cx="311" cy="86" rx="13" ry="9.5" transform="rotate(-18 311 86)"/>
			<path d="M322 30c14 4 26 10 32 20"/>
			<path d="M622 90V34"/>
			<ellipse cx="611" cy="92" rx="13" ry="9.5" transform="rotate(-18 611 92)"/>
			<path d="M686 74V22"/>
			<ellipse cx="675" cy="76" rx="13" ry="9.5" transform="rotate(-18 675 76)"/>
			<path d="M622 34h64"/>
			<path d="M866 62V16c14 4 26 12 30 24"/>
			<ellipse cx="855" cy="64" rx="13" ry="9.5" transform="rotate(-18 855 64)"/>
		</svg>
	</div>
	<p class="interlude__caption"><?php echo esc_html( map_home_field( 'notes_caption', 'каждый урок начинается с первой ноты' ) ); ?></p>
</div>
