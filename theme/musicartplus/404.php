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
		<span class="eyebrow"><?php esc_html_e( 'Ошибка 404', 'musicartplus' ); ?></span>
		<h1 class="h2"><?php esc_html_e( 'Такой страницы нет', 'musicartplus' ); ?></h1>
		<p class="lead" style="margin-top:16px"><?php esc_html_e( 'Возможно, она переехала. Вернитесь на главную или посмотрите новости центра.', 'musicartplus' ); ?></p>
		<div class="flex-center mt-l">
			<a class="btn btn--gold" href="<?php echo esc_url( home_url( '/' ) ); ?>"><?php esc_html_e( 'На главную', 'musicartplus' ); ?></a>
			<a class="btn btn--ghost"<?php echo map_cta_attrs(); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>><?php esc_html_e( 'Записаться на урок', 'musicartplus' ); ?></a>
		</div>
	</div>
</section>
<?php
get_footer();
