<?php
/**
 * Подвал сайта и модальные окна.
 *
 * @package MusicArtPlus
 */

defined( 'ABSPATH' ) || exit;
?>
</main>

<footer class="footer">
	<div class="footer__deco" aria-hidden="true">
		<svg viewBox="0 0 300 300"><circle cx="60" cy="230" r="34"/><circle cx="200" cy="196" r="34"/><path d="M94 230V60l140-28v164"/><path d="M94 96l140-28"/></svg>
	</div>
	<div class="container">
		<div class="footer__grid">
			<div>
				<img class="footer__logo" src="<?php echo esc_url( map_logo_url( 'white' ) ); ?>" alt="<?php echo esc_attr( get_bloginfo( 'name' ) ); ?>" width="176" height="103" loading="lazy">
				<p><?php echo esc_html( map_opt( 'footer_about', 'Центр искусств для детей и взрослых в Москве. Музыка, живопись и сцена — в атмосфере, где хочется творить.' ) ); ?></p>
				<?php if ( map_opt( 'fund_url' ) ) : ?>
					<a class="footer__fund" href="<?php echo esc_url( map_opt( 'fund_url' ) ); ?>" target="_blank" rel="noopener">
						<img src="<?php echo esc_url( map_image_url( map_opt( 'fund_logo' ), 'full', map_asset( 'assets/img/ui/forteforma.svg' ) ) ); ?>" alt="<?php echo esc_attr( sprintf( __( 'Фонд %s', 'musicartplus' ), map_opt( 'fund_name' ) ) ); ?>" width="45" height="34" loading="lazy">
						<span><?php echo esc_html( map_opt( 'fund_prefix', __( 'При поддержке фонда', 'musicartplus' ) ) ); ?> <b><?php echo esc_html( map_opt( 'fund_name' ) ); ?></b></span>
					</a>
				<?php endif; ?>
			</div>

			<div>
				<h4><?php echo esc_html( map_opt( 'footer_menu_title', __( 'Разделы', 'musicartplus' ) ) ); ?></h4>
				<div class="footer__links"><?php map_footer_menu( 'footer' ); ?></div>
			</div>

			<div>
				<h4><?php echo esc_html( map_opt( 'footer_directions_title', __( 'Направления', 'musicartplus' ) ) ); ?></h4>
				<div class="footer__links"><?php map_footer_directions(); ?></div>
			</div>

			<div>
				<h4><?php echo esc_html( map_opt( 'footer_contacts_title', __( 'Контакты', 'musicartplus' ) ) ); ?></h4>
				<div class="footer__contact">
					<span><?php echo esc_html( map_opt( 'address' ) ); ?>
						<?php if ( map_opt( 'address_note' ) ) : ?>
							<br><small style="opacity:.7"><?php echo esc_html( map_opt( 'address_note' ) ); ?></small>
						<?php endif; ?>
					</span>
					<a href="tel:<?php echo esc_attr( map_phone_href() ); ?>"><?php echo esc_html( map_opt( 'phone' ) ); ?></a>
					<a href="mailto:<?php echo esc_attr( map_opt( 'email' ) ); ?>"><?php echo esc_html( map_opt( 'email' ) ); ?></a>
				</div>
				<div style="margin-top:18px"><?php map_html( map_socials( 'socials--footer' ) ); ?></div>
			</div>
		</div>

		<div class="footer__bottom">
			<span>&copy; <span data-year><?php echo esc_html( gmdate( 'Y' ) ); ?></span> <?php echo esc_html( get_bloginfo( 'name' ) ); ?>. <?php echo esc_html( map_opt( 'footer_copyright', __( 'Все права защищены.', 'musicartplus' ) ) ); ?></span>
			<span><?php map_privacy_links(); ?></span>
		</div>
	</div>
</footer>

<a class="btn btn--gold fab"<?php echo map_cta_attrs(); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>><?php echo esc_html( map_cta_label() ); ?></a>

<?php
get_template_part( 'template-parts/modal/booking' );
get_template_part( 'template-parts/modal/teacher' );
get_template_part( 'template-parts/modal/video' );

wp_footer();
?>
</body>
</html>
