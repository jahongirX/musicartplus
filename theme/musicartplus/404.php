<?php
/**
 * Страница «не найдено».
 *
 * @package MusicArtPlus
 */

defined( 'ABSPATH' ) || exit;

get_header();
?>
<section class="section" style="min-height:52vh;display:grid;place-items:center;text-align:center">
	<div class="container container--narrow">
		<span class="eyebrow"><?php echo esc_html( map_opt( 'err404_eyebrow', __( 'Ошибка 404', 'musicartplus' ) ) ); ?></span>
		<h1 class="h2"><?php echo esc_html( map_opt( 'err404_title', __( 'Такой страницы нет', 'musicartplus' ) ) ); ?></h1>
		<p class="lead" style="margin-top:16px"><?php echo esc_html( map_opt( 'err404_text', __( 'Возможно, она переехала. Вернитесь на главную или посмотрите новости центра.', 'musicartplus' ) ) ); ?></p>
		<div class="flex-center mt-l">
			<a class="btn btn--gold" href="<?php echo esc_url( home_url( '/' ) ); ?>"><?php echo esc_html( map_opt( 'err404_btn_home', __( 'На главную', 'musicartplus' ) ) ); ?></a>
			<a class="btn btn--ghost"<?php echo map_cta_attrs(); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>><?php echo esc_html( map_opt( 'err404_btn_cta', __( 'Записаться на урок', 'musicartplus' ) ) ); ?></a>
		</div>
	</div>
</section>
<?php
get_footer();
