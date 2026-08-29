<?php
/**
 * Разметка повторяющихся элементов: карточки, слайдеры, секции.
 *
 * @package MusicArtPlus
 */

defined( 'ABSPATH' ) || exit;

/**
 * Значение поля записи с запасным вариантом.
 *
 * @param string $key     Имя поля.
 * @param int    $post_id ID записи.
 * @param mixed  $default Значение по умолчанию.
 * @return mixed
 */
function map_field( $key, $post_id = 0, $default = '' ) {
	$post_id = $post_id ? $post_id : get_the_ID();

	if ( function_exists( 'get_field' ) ) {
		$value = get_field( $key, $post_id );
	} else {
		$value = get_post_meta( $post_id, $key, true );
	}

	if ( null === $value || '' === $value || array() === $value ) {
		return $default;
	}

	return $value;
}

/**
 * Данные педагога для карточки и модального окна.
 *
 * @param WP_Post|int $post Запись педагога.
 * @return array
 */
function map_teacher_data( $post ) {
	$post = get_post( $post );

	if ( ! $post ) {
		return array();
	}

	$photo = get_the_post_thumbnail_url( $post, 'map-teacher' );

	$schedule = map_field( 'schedule', $post->ID, array() );
	$rows     = array();

	// Скрипт модального окна ждёт объекты вида {day, time} — см. main.js,
	// блок отрисовки расписания. Массив пар он не поймёт.
	foreach ( (array) $schedule as $row ) {
		if ( empty( $row['day'] ) ) {
			continue;
		}

		$rows[] = array(
			'day'  => $row['day'],
			'time' => isset( $row['time'] ) ? $row['time'] : '',
		);
	}

	return array(
		'slug'     => $post->post_name,
		'name'     => get_the_title( $post ),
		'role'     => map_field( 'role', $post->ID ),
		'subject'  => map_field( 'subject', $post->ID ),
		'photo'    => $photo ? $photo : map_asset( 'assets/img/ui/logo-color.png' ),
		'short'    => map_field( 'short', $post->ID ),
		'bio'      => map_field( 'bio', $post->ID ),
		'facts'    => map_lines( map_field( 'facts', $post->ID ) ),
		'schedule' => $rows,
	);
}

/**
 * Карточка педагога.
 *
 * Данные дублируются в data-атрибутах: по ним скрипт собирает модальное окно
 * без дополнительного запроса к серверу.
 *
 * @param WP_Post|int $post   Запись.
 * @param int         $delay  Задержка появления.
 * @param bool        $reveal Анимировать появление.
 * @return void
 */
function map_teacher_card( $post, $delay = 0, $reveal = true ) {
	$t = map_teacher_data( $post );

	if ( ! $t ) {
		return;
	}
	?>
	<article class="teacher<?php echo $reveal ? ' reveal' : ''; ?>" data-delay="<?php echo (int) $delay; ?>"
		data-teacher="<?php echo esc_attr( $t['slug'] ); ?>"
		data-name="<?php echo esc_attr( $t['name'] ); ?>"
		data-role="<?php echo esc_attr( $t['role'] ); ?>"
		data-subject="<?php echo esc_attr( $t['subject'] ); ?>"
		data-photo="<?php echo esc_url( $t['photo'] ); ?>"
		data-bio="<?php echo esc_attr( $t['bio'] ); ?>"
		data-facts="<?php echo esc_attr( wp_json_encode( $t['facts'] ) ); ?>"
		data-schedule="<?php echo esc_attr( wp_json_encode( $t['schedule'] ) ); ?>">
		<button class="teacher__ava" type="button" data-teacher-open aria-label="<?php echo esc_attr( sprintf( __( 'Подробнее: %s', 'musicartplus' ), $t['name'] ) ); ?>">
			<img src="<?php echo esc_url( $t['photo'] ); ?>" alt="<?php echo esc_attr( $t['name'] ); ?>" loading="lazy" width="330" height="330">
		</button>
		<h3 class="teacher__name"><?php echo esc_html( $t['name'] ); ?></h3>
		<div class="teacher__role"><?php echo esc_html( $t['subject'] ); ?></div>
		<p class="teacher__desc"><?php echo esc_html( $t['short'] ); ?></p>
		<div class="teacher__actions">
			<a class="btn btn--gold btn--sm"<?php echo map_cta_attrs( $t['slug'] ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>><?php esc_html_e( 'Записаться', 'musicartplus' ); ?></a>
			<button class="btn btn--ghost btn--sm" type="button" data-teacher-open><?php esc_html_e( 'Подробнее', 'musicartplus' ); ?></button>
		</div>
	</article>
	<?php
}

/**
 * Карточка приглашённого мастера.
 *
 * @param WP_Post|int $post   Запись.
 * @param int         $delay  Задержка появления.
 * @param bool        $reveal Анимировать появление.
 * @return void
 */
function map_guest_card( $post, $delay = 0, $reveal = true ) {
	$post  = get_post( $post );
	$photo = get_the_post_thumbnail_url( $post, 'map-teacher' );
	$facts = map_lines( map_field( 'guest_note', $post->ID ) );
	?>
	<article class="teacher teacher--guest<?php echo $reveal ? ' reveal' : ''; ?>" data-delay="<?php echo (int) $delay; ?>">
		<div class="teacher__ava">
			<?php if ( $photo ) : ?>
				<img src="<?php echo esc_url( $photo ); ?>" alt="<?php echo esc_attr( get_the_title( $post ) ); ?>" loading="lazy" width="330" height="330">
			<?php endif; ?>
		</div>
		<h3 class="teacher__name"><?php echo esc_html( get_the_title( $post ) ); ?></h3>
		<div class="teacher__role"><?php echo esc_html( map_field( 'guest_role', $post->ID ) ); ?></div>
		<?php if ( map_field( 'guest_org', $post->ID ) ) : ?>
			<p class="teacher__desc"><?php echo esc_html( map_field( 'guest_org', $post->ID ) ); ?></p>
		<?php endif; ?>
		<?php if ( $facts ) : ?>
			<ul class="tm__list" style="text-align:left;margin-top:14px">
				<?php foreach ( $facts as $fact ) : ?>
					<li><?php echo esc_html( $fact ); ?></li>
				<?php endforeach; ?>
			</ul>
		<?php endif; ?>
	</article>
	<?php
}

/**
 * Карточка отзыва.
 *
 * @param WP_Post|int $post   Запись.
 * @param int         $delay  Задержка появления.
 * @param bool        $reveal Анимировать появление.
 * @return void
 */
function map_review_card( $post, $delay = 0, $reveal = true ) {
	$post   = get_post( $post );
	$author = map_field( 'review_author', $post->ID, get_the_title( $post ) );
	$role   = map_field( 'review_role', $post->ID );
	$rating = (int) map_field( 'review_rating', $post->ID, 5 );
	$text   = trim( (string) $post->post_content );

	// Длинные отзывы сворачиваем — иначе карточки в слайдере разной высоты.
	$long = mb_strlen( wp_strip_all_tags( $text ) ) > 420;

	$classes = 'review' . ( $reveal ? ' reveal' : '' ) . ( $long ? ' review--clamped' : '' );
	?>
	<article class="<?php echo esc_attr( $classes ); ?>" data-delay="<?php echo (int) $delay; ?>">
		<span class="review__quote" aria-hidden="true">&ldquo;</span>
		<div class="review__stars" aria-label="<?php echo esc_attr( sprintf( __( 'Оценка %d из 5', 'musicartplus' ), $rating ) ); ?>">
			<?php for ( $i = 0; $i < $rating; $i++ ) : ?>
				<?php map_the_icon( 'star' ); ?>
			<?php endfor; ?>
		</div>
		<div class="review__text"><?php echo wp_kses_post( wpautop( $text ) ); ?></div>
		<?php if ( $long ) : ?>
			<button class="review__more" type="button"><?php esc_html_e( 'Читать полностью', 'musicartplus' ); ?></button>
		<?php endif; ?>
		<div class="review__author">
			<span class="review__ava"><?php echo esc_html( mb_substr( $author, 0, 1 ) ); ?></span>
			<div><b><?php echo esc_html( $author ); ?></b><span><?php echo esc_html( $role ); ?></span></div>
		</div>
	</article>
	<?php
}

/**
 * Карточка новости.
 *
 * @param WP_Post|int $post   Запись.
 * @param int         $delay  Задержка появления.
 * @param bool        $reveal Анимировать появление.
 * @return void
 */
function map_news_card( $post, $delay = 0, $reveal = true ) {
	$post  = get_post( $post );
	$badge = map_news_badge( $post );
	$image = get_the_post_thumbnail_url( $post, 'map-card' );
	?>
	<a class="card<?php echo $reveal ? ' reveal' : ''; ?>" data-delay="<?php echo (int) $delay; ?>" data-news-item data-tag="<?php echo esc_attr( $badge ); ?>" href="<?php echo esc_url( get_permalink( $post ) ); ?>">
		<div class="card__media">
			<div class="card__date">
				<b><?php echo esc_html( get_the_date( 'd', $post ) ); ?></b>
				<span><?php echo esc_html( mb_substr( map_month_genitive( (int) get_the_date( 'n', $post ) ), 0, 3 ) ); ?></span>
			</div>
			<?php if ( $image ) : ?>
				<img src="<?php echo esc_url( $image ); ?>" alt="<?php echo esc_attr( get_the_title( $post ) ); ?>" loading="lazy" width="640" height="440">
			<?php endif; ?>
		</div>
		<div class="card__body">
			<?php if ( $badge ) : ?>
				<span class="chip chip--outline"><?php echo esc_html( $badge ); ?></span>
			<?php endif; ?>
			<h3 class="card__title"><?php echo esc_html( get_the_title( $post ) ); ?></h3>
			<p class="card__text"><?php echo esc_html( map_news_excerpt( $post ) ); ?></p>
			<div class="card__foot"><span class="link-arrow"><?php esc_html_e( 'Читать', 'musicartplus' ); ?><?php map_the_icon( 'ar' ); ?></span></div>
		</div>
	</a>
	<?php
}

/**
 * Метка новости: своё поле или первая рубрика.
 *
 * @param WP_Post|int $post Запись.
 * @return string
 */
function map_news_badge( $post ) {
	$post  = get_post( $post );
	$badge = map_field( 'news_badge', $post->ID );

	if ( $badge ) {
		return $badge;
	}

	$terms = get_the_terms( $post, 'category' );

	if ( $terms && ! is_wp_error( $terms ) ) {
		return $terms[0]->name;
	}

	return '';
}

/**
 * Краткий текст новости.
 *
 * @param WP_Post|int $post Запись.
 * @return string
 */
function map_news_excerpt( $post ) {
	$post = get_post( $post );
	$lead = map_field( 'news_lead', $post->ID );

	if ( $lead ) {
		return $lead;
	}

	if ( $post->post_excerpt ) {
		return $post->post_excerpt;
	}

	return wp_trim_words( wp_strip_all_tags( $post->post_content ), 24 );
}

/**
 * Название месяца в родительном падеже.
 *
 * Стандартный date_i18n даёт именительный падеж — «Август» вместо «августа».
 *
 * @param int $month Номер месяца.
 * @return string
 */
function map_month_genitive( $month ) {
	$months = array(
		1 => 'января', 2 => 'февраля', 3 => 'марта', 4 => 'апреля',
		5 => 'мая', 6 => 'июня', 7 => 'июля', 8 => 'августа',
		9 => 'сентября', 10 => 'октября', 11 => 'ноября', 12 => 'декабря',
	);

	return isset( $months[ $month ] ) ? $months[ $month ] : '';
}

/**
 * Плитка направления.
 *
 * @param WP_Post|int $post  Запись.
 * @param int         $delay Задержка появления.
 * @return void
 */
function map_direction_tile( $post, $delay = 0 ) {
	$post = get_post( $post );
	$icon = map_field( 'dir_icon', $post->ID, 'note' );
	?>
	<article class="dir-tile reveal" id="dir-<?php echo esc_attr( $post->post_name ); ?>" data-delay="<?php echo (int) $delay; ?>">
		<span class="dir-tile__ico"><?php map_the_icon( $icon ); ?></span>
		<?php if ( map_field( 'dir_age', $post->ID ) ) : ?>
			<span class="chip"><?php echo esc_html( map_field( 'dir_age', $post->ID ) ); ?></span>
		<?php endif; ?>
		<h3><?php echo esc_html( get_the_title( $post ) ); ?></h3>
		<p><?php echo esc_html( map_field( 'dir_short', $post->ID ) ); ?></p>
		<?php if ( map_field( 'dir_format', $post->ID ) ) : ?>
			<div class="dir-tile__meta"><span><?php esc_html_e( 'Формат:', 'musicartplus' ); ?> <b><?php echo esc_html( map_field( 'dir_format', $post->ID ) ); ?></b></span></div>
		<?php endif; ?>
		<div style="margin-top:16px">
			<a class="btn btn--gold btn--sm" data-crm="true" data-crm-subject="<?php echo esc_attr( get_the_title( $post ) ); ?>" href="<?php echo esc_url( map_crm_url() ); ?>"><?php esc_html_e( 'Записаться', 'musicartplus' ); ?></a>
		</div>
	</article>
	<?php
}

/**
 * Открывает слайдер.
 *
 * @param string $name   Имя слайдера.
 * @param string $preset Пресет настроек.
 * @param string $grid   Дополнительный класс.
 * @return void
 */
function map_slider_open( $name, $preset = 'cards', $grid = '' ) {
	printf(
		'<div class="slider%1$s" data-swiper="%2$s" data-swiper-preset="%3$s"><div class="swiper"><div class="swiper-wrapper">',
		$grid ? ' ' . esc_attr( $grid ) : '',
		esc_attr( $name ),
		esc_attr( $preset )
	);
}

/**
 * Закрывает слайдер.
 *
 * @return void
 */
function map_slider_close() {
	echo '</div></div><div class="swiper-pagination"></div></div>';
}

/**
 * Стрелки слайдера.
 *
 * @param string $name Имя слайдера.
 * @return void
 */
function map_slider_nav( $name ) {
	?>
	<div class="slider__nav" data-swiper-nav="<?php echo esc_attr( $name ); ?>">
		<button class="c-arrow" type="button" data-c-prev aria-label="<?php esc_attr_e( 'Назад', 'musicartplus' ); ?>"><?php map_the_icon( 'al' ); ?></button>
		<button class="c-arrow" type="button" data-c-next aria-label="<?php esc_attr_e( 'Вперёд', 'musicartplus' ); ?>"><?php map_the_icon( 'ar' ); ?></button>
	</div>
	<?php
}

/**
 * Секция виджета расписания «Мой класс».
 *
 * @return void
 */
function map_widget_section() {
	if ( ! map_page_has_widget() ) {
		return;
	}
	?>
	<section class="section section--cream mk-section" id="raspisanie">
		<div class="container">
			<div class="sec-head reveal">
				<div>
					<span class="eyebrow"><?php echo esc_html( map_opt( 'schedule_eyebrow', __( 'ЗАПИСЬ ОНЛАЙН', 'musicartplus' ) ) ); ?></span>
					<h2 class="h2"><?php echo esc_html( map_opt( 'schedule_title', __( 'Расписание занятий', 'musicartplus' ) ) ); ?></h2>
					<p class="lead" style="max-width:640px"><?php echo esc_html( map_opt( 'schedule_text', __( 'Актуальные группы и свободное время — напрямую из системы «Мой класс».', 'musicartplus' ) ) ); ?></p>
				</div>
			</div>
			<div class="mk-widget reveal" data-delay="1">
				<div id="SiteWidgetMoyklass<?php echo esc_attr( map_widget_id() ); ?>"></div>
			</div>
		</div>
	</section>
	<?php
}

/**
 * Полоса с призывом записаться.
 *
 * @param string $title Заголовок.
 * @param string $text  Текст.
 * @return void
 */
function map_cta_band( $title, $text ) {
	?>
	<section class="cta-band">
		<div class="container cta-band__inner">
			<div>
				<h2 class="h2" style="color:#fff"><?php echo esc_html( $title ); ?></h2>
				<p class="lead" style="color:rgba(255,255,255,.72)"><?php echo esc_html( $text ); ?></p>
			</div>
			<div class="cta-band__actions">
				<a class="btn btn--gold btn--lg"<?php echo map_cta_attrs(); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>><?php echo esc_html( map_cta_label() ); ?></a>
				<a class="btn btn--light btn--lg" href="tel:<?php echo esc_attr( map_phone_href() ); ?>"><?php echo esc_html( map_opt( 'phone' ) ); ?></a>
			</div>
		</div>
	</section>
	<?php
}

/**
 * Декоративный росчерк в углу секции.
 *
 * @param string $kind note | star | brush | swirl.
 * @return void
 */
function map_deco( $kind ) {
	$shapes = array(
		'note'  => array( 'deco--bl', '0 0 200 200', '<circle cx="42" cy="150" r="15"/><circle cx="120" cy="132" r="15"/><path d="M57 150V44l78-16v104"/><path d="M57 76l78-16"/>' ),
		'star'  => array( 'deco--tl deco--gold', '0 0 200 200', '<path d="M100 20l16 46 46 16-46 16-16 46-16-46-46-16 46-16z"/><path d="M158 118l7 20 20 7-20 7-7 20-7-20-20-7 20-7z"/>' ),
		'brush' => array( 'deco--tr', '0 0 220 140', '<path d="M8 96C60 46 108 118 152 74c26-26 44-8 60 2"/><path d="M28 118c40-30 78 22 116-10"/>' ),
		'swirl' => array( 'deco--tr', '0 0 200 200', '<path d="M120 12c-30 22-44 52-26 76 18 24 58 18 62-8 4-26-24-40-46-26-22 14-20 50 4 66"/><circle cx="60" cy="150" r="16"/><path d="M76 150V54"/>' ),
	);

	if ( ! isset( $shapes[ $kind ] ) ) {
		return;
	}

	list( $class, $viewbox, $paths ) = $shapes[ $kind ];

	printf(
		'<span class="deco %s draw" aria-hidden="true"><svg viewBox="%s">%s</svg></span>',
		esc_attr( $class ),
		esc_attr( $viewbox ),
		$paths // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- статичная разметка SVG.
	);
}

/**
 * Форма записи в отдельной карточке.
 *
 * @param string $title    Заголовок.
 * @param string $subtitle Подпись.
 * @param string $ident    Идентификатор формы (должен быть уникальным на странице).
 * @return void
 */
function map_form_card( $title, $subtitle, $ident = 'form-main' ) {
	$privacy = get_privacy_policy_url();
	?>
	<div class="form-card reveal reveal--right">
		<h3 class="h3"><?php echo esc_html( $title ); ?></h3>
		<p class="muted" style="margin-top:10px;font-size:16px"><?php echo esc_html( $subtitle ); ?></p>

		<form class="form" data-form id="<?php echo esc_attr( $ident ); ?>" novalidate style="margin-top:24px">
			<input type="hidden" name="t" value="<?php echo esc_attr( time() ); ?>">
			<div class="hp" aria-hidden="true">
				<label for="<?php echo esc_attr( $ident ); ?>-website"><?php esc_html_e( 'Не заполняйте это поле', 'musicartplus' ); ?></label>
				<input id="<?php echo esc_attr( $ident ); ?>-website" name="website" type="text" tabindex="-1" autocomplete="off">
			</div>

			<div class="field">
				<label for="<?php echo esc_attr( $ident ); ?>-name"><?php esc_html_e( 'Как вас зовут', 'musicartplus' ); ?></label>
				<input id="<?php echo esc_attr( $ident ); ?>-name" name="name" type="text" placeholder="<?php esc_attr_e( 'Имя', 'musicartplus' ); ?>" required autocomplete="name">
				<span class="field__err"><?php esc_html_e( 'Пожалуйста, укажите имя', 'musicartplus' ); ?></span>
			</div>

			<div class="field">
				<label for="<?php echo esc_attr( $ident ); ?>-tel"><?php esc_html_e( 'Телефон', 'musicartplus' ); ?></label>
				<input id="<?php echo esc_attr( $ident ); ?>-tel" name="phone" type="tel" placeholder="+7 (___) ___-__-__" required autocomplete="tel">
				<span class="field__err"><?php esc_html_e( 'Укажите телефон полностью', 'musicartplus' ); ?></span>
			</div>

			<label class="check">
				<input type="checkbox" name="consent" required>
				<span>
					<?php echo esc_html( map_opt( 'booking_consent_text', __( 'Я согласен(-на) на обработку персональных данных', 'musicartplus' ) ) ); ?>
					<?php if ( $privacy ) : ?>
						<?php esc_html_e( 'и принимаю', 'musicartplus' ); ?>
						<a href="<?php echo esc_url( $privacy ); ?>" target="_blank" rel="noopener"><?php esc_html_e( 'политику конфиденциальности', 'musicartplus' ); ?></a>
					<?php endif; ?>
				</span>
			</label>

			<button class="btn btn--gold btn--block btn--lg" type="submit" data-label="<?php echo esc_attr( map_opt( 'booking_submit_label', map_cta_label() ) ); ?>"><?php echo esc_html( map_opt( 'booking_submit_label', map_cta_label() ) ); ?></button>

			<div class="form__ok"><?php echo esc_html( map_opt( 'booking_success_text', __( 'Спасибо! Заявка принята — мы перезвоним в ближайшее рабочее время.', 'musicartplus' ) ) ); ?></div>

			<p class="form__note">
				<?php echo esc_html( map_opt( 'booking_crm_note', __( 'Или запишитесь сами в системе «Мой класс» —', 'musicartplus' ) ) ); ?>
				<a data-crm="true" href="<?php echo esc_url( map_crm_url() ); ?>" style="color:var(--gold-dark);font-weight:600"><?php esc_html_e( 'открыть расписание', 'musicartplus' ); ?></a>
			</p>
		</form>
	</div>
	<?php
}

/**
 * Ролик внутри новости.
 *
 * Открывается тем же окном, что и видео на главной: разметка карточки общая,
 * скрипту достаточно атрибутов data-video.
 *
 * @param int $post_id Запись; по умолчанию текущая.
 * @return void
 */
function map_the_article_video( $post_id = 0 ) {
	$post_id = $post_id ? (int) $post_id : get_the_ID();

	$url  = (string) map_field( 'news_video_url', $post_id, '' );
	$file = (string) map_field( 'news_video_file', $post_id, '' );
	$src  = $url ? $url : $file;

	if ( ! $src ) {
		return;
	}

	// Файл проигрываем сами, ссылку отдаём во встроенный проигрыватель сервиса.
	$kind = $url ? 'iframe' : 'file';

	$poster_id = (int) map_field( 'news_video_poster', $post_id, 0 );
	$poster    = $poster_id ? wp_get_attachment_image_url( $poster_id, 'map-card' ) : '';

	if ( ! $poster ) {
		$poster = get_the_post_thumbnail_url( $post_id, 'map-card' );
	}

	if ( ! $poster ) {
		return;
	}

	$title    = (string) map_field( 'news_video_title', $post_id, __( 'Смотреть видео', 'musicartplus' ) );
	$subtitle = (string) map_field( 'news_video_subtitle', $post_id, '' );
	$vertical = (bool) map_field( 'news_video_vertical', $post_id, false );

	$attrs = array(
		'data-video'      => $src,
		'data-video-type' => $kind,
	);

	if ( 'iframe' === $kind ) {
		$attrs['data-video-page'] = str_replace( '/embed/', '/', $src );
		$attrs['data-video-host'] = map_video_host( $src );
	}

	if ( $vertical ) {
		$attrs['data-video-ratio'] = 'vertical';
	}

	$out = '';

	foreach ( $attrs as $key => $value ) {
		$out .= sprintf( ' %s="%s"', esc_attr( $key ), esc_attr( $value ) );
	}

	printf(
		'<figure class="article__video">'
			. '<button class="video-card video-card--wide%1$s" type="button"%2$s aria-label="%3$s">'
			. '<img src="%4$s" alt="" loading="lazy">'
			. '<span class="video-card__play">%5$s</span>'
			. '<span class="video-card__cap"><b>%6$s</b><span>%7$s</span></span>'
			. '</button></figure>',
		$vertical ? ' video-card--v' : '',
		$out, // экранировано выше
		esc_attr( sprintf( __( 'Смотреть: %s', 'musicartplus' ), $title ) ),
		esc_url( $poster ),
		map_icon( 'play' ),
		esc_html( $title ),
		esc_html( $subtitle )
	);
}

/**
 * Название сервиса по адресу ролика — для запасной ссылки в окне просмотра.
 *
 * @param string $url Адрес.
 * @return string
 */
function map_video_host( $url ) {
	$host = wp_parse_url( $url, PHP_URL_HOST );

	$known = array(
		'kinescope' => 'Kinescope',
		'rutube'    => 'Rutube',
		'vk'        => 'VK Видео',
		'youtube'   => 'YouTube',
		'youtu.be'  => 'YouTube',
	);

	foreach ( $known as $needle => $label ) {
		if ( false !== strpos( (string) $host, $needle ) ) {
			return $label;
		}
	}

	return __( 'первоисточник', 'musicartplus' );
}
