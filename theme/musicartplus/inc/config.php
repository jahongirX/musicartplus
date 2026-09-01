<?php
/**
 * Константы и значения по умолчанию.
 *
 * Всё, что заказчик может поменять, читается из настроек ACF; значения ниже —
 * запасной вариант на случай, если поле ещё не заполнено.
 *
 * @package MusicArtPlus
 */

defined( 'ABSPATH' ) || exit;

/**
 * Реквизиты центра по умолчанию.
 *
 * @return array<string,string>
 */
function map_defaults() {
	return array(
		'phone'        => '+7 903 102-51-11',
		'phone_href'   => '+79031025111',
		'email'        => 'musicartplus@yandex.ru',
		// Куда уходит письмо о новой заявке, пока в настройках не задан
		// другой адрес.
		'notify_email' => 'musicartplus@yandex.ru',
		'address'      => 'Москва, ул. Улофа Пальме, д. 5 (м. Минская)',
		'address_note' => 'вход со стороны запасного входа',
		'telegram'     => 'https://t.me/MusicArtPlus',
		'instagram'    => 'https://www.instagram.com/music_art_plus',
		'rutube'       => 'https://rutube.ru/channel/76411207',
		'fund_url'     => 'https://forteforma.ru/',
		'fund_name'    => 'ФОРТЕФОРМА',
	);
}

/**
 * Направления в форме записи.
 *
 * Используются, пока в настройках не задан свой список.
 *
 * @return string[]
 */
function map_default_directions() {
	return array(
		'Фортепиано',
		'Скрипка',
		'Труба и духовые',
		'Вокал и сценическая речь',
		'Актёрское мастерство',
		'Изобразительное искусство',
		'Сольфеджио и теория музыки',
		'Раннее музыкальное развитие (3–7 лет)',
		'Подготовка к поступлению',
		'Ещё не выбрали — подскажите',
	);
}

/**
 * Идентификатор виджета расписания «Мой класс».
 *
 * @return string
 */
function map_widget_key() {
	$key = map_opt( 'crm_widget_key' );

	return $key ? $key : '01pmDhdEhbM62kYtpQgSetFqsRKoHvtpnQf2';
}

/**
 * Числовой ID контейнера виджета «Мой класс».
 *
 * @return string
 */
function map_widget_id() {
	$id = map_opt( 'crm_widget_id' );

	return $id ? $id : '139193';
}
