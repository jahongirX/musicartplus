<?php
/**
 * Модальное окно с видео.
 *
 * @package MusicArtPlus
 */

defined( 'ABSPATH' ) || exit;
?>
<div class="modal" id="video-modal" role="dialog" aria-modal="true" aria-label="<?php esc_attr_e( 'Видео', 'musicartplus' ); ?>">
	<div class="modal__backdrop"></div>
	<div class="modal__box modal__box--video">
		<button class="modal__close" type="button" data-close aria-label="<?php esc_attr_e( 'Закрыть', 'musicartplus' ); ?>"><?php map_the_icon( 'close' ); ?></button>
		<div class="modal__video"></div>
		<div class="v-foot" style="display:none"></div>
	</div>
</div>
