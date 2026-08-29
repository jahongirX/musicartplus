<?php
/**
 * Модальное окно карточки педагога. Наполняется скриптом.
 *
 * @package MusicArtPlus
 */

defined( 'ABSPATH' ) || exit;
?>
<div class="modal" id="teacher-modal" role="dialog" aria-modal="true" aria-label="<?php esc_attr_e( 'Карточка педагога', 'musicartplus' ); ?>">
	<div class="modal__backdrop"></div>
	<div class="modal__box">
		<button class="modal__close" type="button" data-close aria-label="<?php esc_attr_e( 'Закрыть', 'musicartplus' ); ?>"><?php map_the_icon( 'close' ); ?></button>
		<div data-teacher-slot></div>
	</div>
</div>
