<?php
/**
 * Модальное окно записи на пробный урок.
 *
 * @package MusicArtPlus
 */

defined( 'ABSPATH' ) || exit;

$map_directions = map_lines( map_opt( 'directions_list' ) );

if ( ! $map_directions ) {
	$map_directions = map_default_directions();
}

$map_privacy = get_privacy_policy_url();
?>
<div class="modal" id="booking-modal" role="dialog" aria-modal="true" aria-labelledby="bk-title">
	<div class="modal__backdrop"></div>
	<div class="modal__box modal__box--form">
		<button class="modal__close" type="button" data-close aria-label="<?php esc_attr_e( 'Закрыть', 'musicartplus' ); ?>"><?php map_the_icon( 'close' ); ?></button>

		<div class="bk">
			<aside class="bk__aside">
				<img src="<?php echo esc_url( map_asset( 'assets/img/ui/logo-color.png' ) ); ?>" alt="<?php echo esc_attr( get_bloginfo( 'name' ) ); ?>" width="176" height="103">
				<h4><?php esc_html_e( 'Первый урок — чтобы просто попробовать', 'musicartplus' ); ?></h4>
				<p><?php esc_html_e( 'Знакомство с педагогом и инструментом. Ни к чему не обязывает.', 'musicartplus' ); ?></p>

				<ul class="about-list">
					<?php
					$map_points = array(
						__( 'Подберём педагога под возраст и характер', 'musicartplus' ),
						__( 'Заниматься можно на инструментах центра', 'musicartplus' ),
						__( 'Очно у метро Минская или онлайн', 'musicartplus' ),
					);

					foreach ( $map_points as $map_point ) :
						?>
						<li><span class="tick"><?php map_the_icon( 'check' ); ?></span><span><?php echo esc_html( $map_point ); ?></span></li>
					<?php endforeach; ?>
				</ul>

				<div class="bk__phone">
					<span><?php esc_html_e( 'Или позвоните', 'musicartplus' ); ?></span>
					<a href="tel:<?php echo esc_attr( map_phone_href() ); ?>"><?php echo esc_html( map_opt( 'phone' ) ); ?></a>
				</div>
			</aside>

			<div class="bk__main">
				<h3 class="bk__title" id="bk-title"><?php esc_html_e( 'Записаться на пробный урок', 'musicartplus' ); ?></h3>
				<p class="bk__sub"><?php esc_html_e( 'Заполните два поля — остальное уточним по телефону.', 'musicartplus' ); ?></p>

				<div class="bk__ctx" data-bk-ctx>
					<img alt="" data-bk-photo>
					<div><span data-bk-label><?php esc_html_e( 'Педагог', 'musicartplus' ); ?></span><b data-bk-name></b></div>
				</div>

				<form class="form" data-form id="form-booking" novalidate>
					<input type="hidden" name="teacher" data-bk-input>
					<input type="hidden" name="t" value="<?php echo esc_attr( time() ); ?>">
					<?php // Ловушка для ботов: поле скрыто и человеком не заполняется. ?>
					<div class="hp" aria-hidden="true">
						<label for="bk-website"><?php esc_html_e( 'Не заполняйте это поле', 'musicartplus' ); ?></label>
						<input id="bk-website" name="website" type="text" tabindex="-1" autocomplete="off">
					</div>

					<div class="field">
						<label for="bk-name"><?php esc_html_e( 'Как вас зовут', 'musicartplus' ); ?></label>
						<input id="bk-name" name="name" type="text" placeholder="<?php esc_attr_e( 'Имя', 'musicartplus' ); ?>" required autocomplete="name">
						<span class="field__err"><?php esc_html_e( 'Пожалуйста, укажите имя', 'musicartplus' ); ?></span>
					</div>

					<div class="field">
						<label for="bk-tel"><?php esc_html_e( 'Телефон', 'musicartplus' ); ?></label>
						<input id="bk-tel" name="phone" type="tel" placeholder="+7 (___) ___-__-__" required autocomplete="tel">
						<span class="field__err"><?php esc_html_e( 'Укажите телефон полностью', 'musicartplus' ); ?></span>
					</div>

					<div class="field">
						<label for="bk-dir"><?php esc_html_e( 'Направление', 'musicartplus' ); ?></label>
						<select id="bk-dir" name="direction" data-bk-dir>
							<?php foreach ( $map_directions as $map_direction ) : ?>
								<option value="<?php echo esc_attr( $map_direction ); ?>"><?php echo esc_html( $map_direction ); ?></option>
							<?php endforeach; ?>
						</select>
					</div>

					<label class="check">
						<input type="checkbox" name="consent" required>
						<span>
							<?php esc_html_e( 'Я согласен(-на) на обработку персональных данных', 'musicartplus' ); ?>
							<?php if ( $map_privacy ) : ?>
								<?php esc_html_e( 'и принимаю', 'musicartplus' ); ?>
								<a href="<?php echo esc_url( $map_privacy ); ?>" target="_blank" rel="noopener"><?php esc_html_e( 'политику конфиденциальности', 'musicartplus' ); ?></a>
							<?php endif; ?>
						</span>
					</label>

					<button class="btn btn--gold btn--block btn--lg" type="submit" data-label="<?php esc_attr_e( 'Отправить заявку', 'musicartplus' ); ?>"><?php esc_html_e( 'Отправить заявку', 'musicartplus' ); ?></button>

					<div class="form__ok"><?php esc_html_e( 'Спасибо! Заявка принята — мы перезвоним в ближайшее рабочее время.', 'musicartplus' ); ?></div>

					<p class="form__note">
						<?php esc_html_e( 'Или выберите время сами в системе «Мой класс» —', 'musicartplus' ); ?>
						<a data-bk-crm href="<?php echo esc_url( map_crm_url() ); ?>" target="_blank" rel="noopener" style="color:var(--gold-dark);font-weight:600"><?php esc_html_e( 'открыть расписание', 'musicartplus' ); ?></a>
					</p>
				</form>
			</div>
		</div>
	</div>
</div>
