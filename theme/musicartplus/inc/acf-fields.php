<?php
/**
 * Группы полей ACF, объявленные в коде.
 *
 * Поля живут здесь, а не в базе: их видно в истории правок, они одинаковы на
 * тестовой и боевой копии, и при отрисовке страницы не нужен запрос за их
 * описанием.
 *
 * @package MusicArtPlus
 */

defined( 'ABSPATH' ) || exit;

/**
 * Регистрирует все группы полей.
 *
 * @return void
 */
function map_register_fields() {
	if ( ! function_exists( 'acf_add_local_field_group' ) ) {
		return;
	}

	map_fields_settings();
	map_fields_teacher();
	map_fields_direction();
	map_fields_review();
	map_fields_guest();
	map_fields_news();
	map_fields_front();
}
add_action( 'acf/init', 'map_register_fields' );

/**
 * Короткая запись поля ACF.
 *
 * @param string $key   Ключ поля.
 * @param string $label Подпись.
 * @param string $type  Тип поля.
 * @param array  $args  Прочие параметры.
 * @return array
 */
function map_acf_field( $key, $label, $type = 'text', $args = array() ) {
	return array_merge( array(
		'key'   => 'field_map_' . $key,
		'name'  => $key,
		'label' => $label,
		'type'  => $type,
	), $args );
}

/**
 * Настройки сайта: реквизиты, соцсети, интеграция с CRM.
 *
 * @return void
 */
function map_fields_settings() {
	acf_add_local_field_group( array(
		'key'      => 'group_map_settings',
		'title'    => __( 'Настройки сайта', 'musicartplus' ),
		'location' => array( array( array(
			'param'    => 'options_page',
			'operator' => '==',
			'value'    => 'map-settings',
		) ) ),
		'fields'   => array(
			map_acf_field( 'tab_contacts', __( 'Контакты', 'musicartplus' ), 'tab' ),
			map_acf_field( 'phone', __( 'Телефон', 'musicartplus' ), 'text', array(
				'placeholder' => '+7 903 102-51-11',
				'instructions' => __( 'Как показывать на сайте.', 'musicartplus' ),
			) ),
			map_acf_field( 'phone_href', __( 'Телефон для набора', 'musicartplus' ), 'text', array(
				'placeholder'  => '+79031025111',
				'instructions' => __( 'Без пробелов и скобок — по нему звонят с телефона.', 'musicartplus' ),
			) ),
			map_acf_field( 'email', __( 'Почта', 'musicartplus' ), 'email' ),
			map_acf_field( 'address', __( 'Адрес', 'musicartplus' ), 'text' ),
			map_acf_field( 'address_note', __( 'Уточнение к адресу', 'musicartplus' ), 'text', array(
				'placeholder' => 'вход со стороны запасного входа',
			) ),
			map_acf_field( 'work_hours', __( 'Часы работы', 'musicartplus' ), 'text', array(
				'placeholder'  => 'Пн–Вс, 10:00–20:00',
				'instructions' => __( 'Показывается в блоке «Как нас найти».', 'musicartplus' ),
			) ),
			map_acf_field( 'footer_about', __( 'Текст в подвале', 'musicartplus' ), 'textarea', array(
				'rows'         => 3,
				'instructions' => __( 'Абзац под логотипом в подвале.', 'musicartplus' ),
			) ),
			map_acf_field( 'map_embed', __( 'Код карты', 'musicartplus' ), 'textarea', array(
				'instructions' => __( 'Ссылка на карту из Яндекс.Конструктора. Подставится в блок «Как нас найти».', 'musicartplus' ),
				'rows'         => 3,
			) ),

			map_acf_field( 'tab_social', __( 'Соцсети', 'musicartplus' ), 'tab' ),
			map_acf_field( 'telegram', 'Telegram', 'url' ),
			map_acf_field( 'instagram', 'Instagram', 'url' ),
			map_acf_field( 'rutube', 'Rutube', 'url' ),
			map_acf_field( 'fund_url', __( 'Сайт фонда', 'musicartplus' ), 'url' ),
			map_acf_field( 'fund_name', __( 'Название фонда', 'musicartplus' ), 'text' ),

			map_acf_field( 'tab_crm', __( 'CRM «Мой класс»', 'musicartplus' ), 'tab' ),
			map_acf_field( 'crm_url', __( 'Ссылка на запись', 'musicartplus' ), 'url', array(
				'instructions' => __( 'Куда вести посетителя, если на сайте не сработал JavaScript.', 'musicartplus' ),
			) ),
			map_acf_field( 'crm_widget_enabled', __( 'Показывать виджет расписания', 'musicartplus' ), 'true_false', array(
				'default_value' => 1,
				'ui'            => 1,
			) ),
			map_acf_field( 'crm_widget_key', __( 'Ключ виджета', 'musicartplus' ), 'text', array(
				'instructions' => __( 'Из кода виджета «Мой класс»: параметр id в адресе скрипта.', 'musicartplus' ),
			) ),
			map_acf_field( 'crm_widget_id', __( 'Номер контейнера виджета', 'musicartplus' ), 'text', array(
				'instructions' => __( 'Цифры из id="SiteWidgetMoyklass…".', 'musicartplus' ),
			) ),
			map_acf_field( 'crm_filial_id', __( 'ID филиала', 'musicartplus' ), 'number', array(
				'instructions' => __( 'Оставьте пустым — сайт возьмёт первый филиал из CRM.', 'musicartplus' ),
			) ),
			map_acf_field( 'crm_source_id', __( 'Способ заведения заявки', 'musicartplus' ), 'number', array(
				'default_value' => 3,
				'instructions'  => __( '3 — «Виджет: Форма». По нему в CRM видно заявки с сайта.', 'musicartplus' ),
			) ),
			map_acf_field( 'crm_adv_source_id', __( 'Источник обращения', 'musicartplus' ), 'number', array(
				'instructions' => __( 'Необязательно. ID из справочника источников в CRM.', 'musicartplus' ),
			) ),
			map_acf_field( 'notify_email', __( 'Куда слать заявки', 'musicartplus' ), 'email', array(
				'instructions' => __( 'Пусто — на почту администратора сайта.', 'musicartplus' ),
			) ),

			map_acf_field( 'tab_form', __( 'Форма записи', 'musicartplus' ), 'tab' ),
			map_acf_field( 'directions_list', __( 'Направления в форме', 'musicartplus' ), 'textarea', array(
				'instructions' => __( 'По одному в строке. Пусто — список по умолчанию.', 'musicartplus' ),
				'rows'         => 10,
			) ),
			map_acf_field( 'privacy_url', __( 'Ссылка на политику конфиденциальности', 'musicartplus' ), 'page_link', array(
				'allow_null' => 1,
			) ),
		),
	) );
}

/**
 * Поля педагога.
 *
 * @return void
 */
function map_fields_teacher() {
	acf_add_local_field_group( array(
		'key'      => 'group_map_teacher',
		'title'    => __( 'Карточка педагога', 'musicartplus' ),
		'location' => array( array( array(
			'param'    => 'post_type',
			'operator' => '==',
			'value'    => 'map_teacher',
		) ) ),
		'fields'   => array(
			map_acf_field( 'subject', __( 'Предмет', 'musicartplus' ), 'text', array(
				'instructions' => __( 'Короткая подпись на карточке: «Фортепиано», «Вокал».', 'musicartplus' ),
				'required'     => 1,
			) ),
			map_acf_field( 'role', __( 'Должность', 'musicartplus' ), 'text', array(
				'instructions' => __( 'Строка под именем: «Преподаватель высшей категории».', 'musicartplus' ),
			) ),
			map_acf_field( 'short', __( 'Кратко для карточки', 'musicartplus' ), 'textarea', array( 'rows' => 3 ) ),
			map_acf_field( 'bio', __( 'Текст в окне педагога', 'musicartplus' ), 'textarea', array( 'rows' => 5 ) ),
			map_acf_field( 'facts', __( 'Регалии', 'musicartplus' ), 'textarea', array(
				'instructions' => __( 'По одной в строке.', 'musicartplus' ),
				'rows'         => 6,
			) ),
			map_acf_field( 'schedule', __( 'Расписание', 'musicartplus' ), 'repeater', array(
				'instructions' => __( 'Показывается в окне педагога. Актуальные слоты посетитель видит в «Моём классе».', 'musicartplus' ),
				'layout'       => 'table',
				'button_label' => __( 'Добавить день', 'musicartplus' ),
				'sub_fields'   => array(
					array(
						'key'     => 'field_map_sub_schedule_day',
						'name'    => 'day',
						'label'   => __( 'День', 'musicartplus' ),
						'type'    => 'select',
						'choices' => array(
							'Пн' => 'Пн', 'Вт' => 'Вт', 'Ср' => 'Ср', 'Чт' => 'Чт',
							'Пт' => 'Пт', 'Сб' => 'Сб', 'Вс' => 'Вс',
						),
					),
					array(
						'key'         => 'field_map_sub_schedule_time',
						'name'        => 'time',
						'label'       => __( 'Время', 'musicartplus' ),
						'type'        => 'text',
						'placeholder' => '15:00 – 20:00',
					),
				),
			) ),
		),
	) );
}

/**
 * Поля направления обучения.
 *
 * @return void
 */
function map_fields_direction() {
	acf_add_local_field_group( array(
		'key'      => 'group_map_direction',
		'title'    => __( 'Направление', 'musicartplus' ),
		'location' => array( array( array(
			'param'    => 'post_type',
			'operator' => '==',
			'value'    => 'map_direction',
		) ) ),
		'fields'   => array(
			map_acf_field( 'dir_icon', __( 'Иконка', 'musicartplus' ), 'select', array(
				'choices' => map_icon_choices(),
			) ),
			map_acf_field( 'dir_short', __( 'Кратко', 'musicartplus' ), 'textarea', array( 'rows' => 3 ) ),
			map_acf_field( 'dir_age', __( 'Возраст', 'musicartplus' ), 'text', array( 'placeholder' => 'с 5 лет' ) ),
			map_acf_field( 'dir_format', __( 'Формат', 'musicartplus' ), 'text', array( 'placeholder' => 'индивидуально' ) ),
			map_acf_field( 'dir_featured', __( 'Показывать на главной', 'musicartplus' ), 'true_false', array( 'ui' => 1 ) ),
		),
	) );
}

/**
 * Поля отзыва.
 *
 * @return void
 */
function map_fields_review() {
	acf_add_local_field_group( array(
		'key'      => 'group_map_review',
		'title'    => __( 'Отзыв', 'musicartplus' ),
		'location' => array( array( array(
			'param'    => 'post_type',
			'operator' => '==',
			'value'    => 'map_review',
		) ) ),
		'fields'   => array(
			map_acf_field( 'review_author', __( 'Кто оставил', 'musicartplus' ), 'text', array( 'required' => 1 ) ),
			map_acf_field( 'review_role', __( 'Подпись', 'musicartplus' ), 'text', array( 'placeholder' => 'мама ученика' ) ),
			map_acf_field( 'review_rating', __( 'Оценка', 'musicartplus' ), 'number', array(
				'default_value' => 5,
				'min'           => 1,
				'max'           => 5,
			) ),
		),
	) );
}

/**
 * Поля приглашённого мастера.
 *
 * @return void
 */
function map_fields_guest() {
	acf_add_local_field_group( array(
		'key'      => 'group_map_guest',
		'title'    => __( 'Приглашённый мастер', 'musicartplus' ),
		'location' => array( array( array(
			'param'    => 'post_type',
			'operator' => '==',
			'value'    => 'map_guest',
		) ) ),
		'fields'   => array(
			map_acf_field( 'guest_role', __( 'Кто это', 'musicartplus' ), 'text' ),
			map_acf_field( 'guest_org', __( 'Где преподаёт', 'musicartplus' ), 'text' ),
			map_acf_field( 'guest_note', __( 'Кратко', 'musicartplus' ), 'textarea', array( 'rows' => 3 ) ),
		),
	) );
}

/**
 * Дополнительные поля новости.
 *
 * @return void
 */
function map_fields_news() {
	acf_add_local_field_group( array(
		'key'      => 'group_map_news',
		'title'    => __( 'Оформление новости', 'musicartplus' ),
		'location' => array( array( array(
			'param'    => 'post_type',
			'operator' => '==',
			'value'    => 'post',
		) ) ),
		'fields'   => array(
			map_acf_field( 'news_badge', __( 'Метка', 'musicartplus' ), 'text', array(
				'instructions' => __( 'Короткая подпись на карточке: «Концерт», «Мастер-класс». Пусто — возьмётся рубрика.', 'musicartplus' ),
			) ),
			map_acf_field( 'news_lead', __( 'Вступление', 'musicartplus' ), 'textarea', array(
				'instructions' => __( 'Крупный абзац под заголовком. Пусто — начало текста.', 'musicartplus' ),
				'rows'         => 3,
			) ),
			map_acf_field( 'news_gallery', __( 'Галерея', 'musicartplus' ), 'gallery', array(
				'instructions' => __( 'Фотографии под текстом. Открываются во весь экран.', 'musicartplus' ),
			) ),
		),
	) );
}

/**
 * Поля главной страницы.
 *
 * @return void
 */
function map_fields_front() {
	acf_add_local_field_group( array(
		'key'      => 'group_map_front',
		'title'    => __( 'Главная страница', 'musicartplus' ),
		'location' => array( array( array(
			'param'    => 'page_type',
			'operator' => '==',
			'value'    => 'front_page',
		) ) ),
		'fields'   => array(
			map_acf_field( 'tab_hero', __( 'Первый экран', 'musicartplus' ), 'tab' ),
			map_acf_field( 'hero_eyebrow', __( 'Надпись над заголовком', 'musicartplus' ), 'text', array(
				'placeholder' => 'Москва · м. Минская',
			) ),
			map_acf_field( 'hero_title', __( 'Заголовок', 'musicartplus' ), 'textarea', array( 'rows' => 3 ) ),
			map_acf_field( 'hero_text', __( 'Текст под заголовком', 'musicartplus' ), 'textarea', array( 'rows' => 3 ) ),
			map_acf_field( 'hero_slides', __( 'Фотографии', 'musicartplus' ), 'gallery', array(
				'instructions' => __( 'Меняются сами. Первая — самая важная, её видит посетитель сразу.', 'musicartplus' ),
			) ),
			map_acf_field( 'hero_facts', __( 'Цифры', 'musicartplus' ), 'repeater', array(
				'layout'       => 'table',
				'button_label' => __( 'Добавить цифру', 'musicartplus' ),
				'sub_fields'   => array(
					array( 'key' => 'field_map_sub_fact_num', 'name' => 'num', 'label' => __( 'Число', 'musicartplus' ), 'type' => 'text' ),
					array( 'key' => 'field_map_sub_fact_label', 'name' => 'label', 'label' => __( 'Подпись', 'musicartplus' ), 'type' => 'text' ),
				),
			) ),

			map_acf_field( 'tab_about', __( 'О центре', 'musicartplus' ), 'tab' ),
			map_acf_field( 'about_title', __( 'Заголовок', 'musicartplus' ), 'text' ),
			map_acf_field( 'about_text', __( 'Текст', 'musicartplus' ), 'wysiwyg', array(
				'media_upload' => 0,
				'toolbar'      => 'basic',
			) ),
			map_acf_field( 'about_image', __( 'Фотография', 'musicartplus' ), 'image', array( 'return_format' => 'id' ) ),
			map_acf_field( 'about_points', __( 'Пункты списка', 'musicartplus' ), 'textarea', array(
				'instructions' => __( 'По одному в строке.', 'musicartplus' ),
				'rows'         => 6,
			) ),

			map_acf_field( 'tab_video', __( 'Видео', 'musicartplus' ), 'tab' ),
			map_acf_field( 'videos', __( 'Ролики', 'musicartplus' ), 'repeater', array(
				'button_label' => __( 'Добавить ролик', 'musicartplus' ),
				'sub_fields'   => array(
					array( 'key' => 'field_map_sub_video_title', 'name' => 'title', 'label' => __( 'Подпись', 'musicartplus' ), 'type' => 'text' ),
					array( 'key' => 'field_map_sub_video_url', 'name' => 'url', 'label' => __( 'Ссылка', 'musicartplus' ), 'type' => 'url' ),
					array( 'key' => 'field_map_sub_video_subtitle', 'name' => 'subtitle', 'label' => __( 'Вторая строка', 'musicartplus' ), 'type' => 'text' ),
					array( 'key' => 'field_map_sub_video_cover', 'name' => 'cover', 'label' => __( 'Обложка', 'musicartplus' ), 'type' => 'image', 'return_format' => 'id' ),
				),
			) ),

			map_acf_field( 'tab_cta', __( 'Призыв к записи', 'musicartplus' ), 'tab' ),
			map_acf_field( 'cta_title', __( 'Заголовок', 'musicartplus' ), 'text' ),
			map_acf_field( 'cta_text', __( 'Текст', 'musicartplus' ), 'textarea', array( 'rows' => 3 ) ),
		),
	) );
}
