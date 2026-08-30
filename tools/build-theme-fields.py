# -*- coding: utf-8 -*-
"""Достраивает группы полей ACF в theme/musicartplus/acf-json.

Скрипт ТОЛЬКО добавляет: поле, которое уже есть в файле, он не трогает, порядок
существующих не меняет. Поэтому его можно запускать повторно и после правок из
админки — свои изменения заказчика он не затрёт.

Ключи новых полей содержат имя группы (field_map_home_..., field_map_set_...):
одинаковое имя поля в разных группах допустимо, а вот одинаковый ключ ACF
ломает всё — он должен быть уникален на весь сайт.
"""
import io
import json
import os
import zlib

ROOT = os.path.dirname(os.path.dirname(os.path.abspath(__file__)))
JSON_DIR = os.path.join(ROOT, 'theme', 'musicartplus', 'acf-json')


def f(name, label, ftype='text', **extra):
    """Описание поля. Всё лишнее ACF допишет сам при первом сохранении."""
    d = {'name': name, 'label': label, 'type': ftype}
    d.update(extra)
    return d



def sub(prefix, name, label, ftype='text', **extra):
    """Подполе повторителя. Ключ обязан быть уникален на весь сайт, поэтому
    в нём и префикс группы, и метка sub — иначе поле с тем же именем в другой
    группе перетрёт это (ACF ищет определение по ключу)."""
    d = {
        'key': 'field_map_%s_sub_%s' % (prefix, name),
        'label': label, 'name': name, 'aria-label': '', 'type': ftype,
        'instructions': '', 'required': 0, 'conditional_logic': 0,
        'wrapper': {'width': '', 'class': '', 'id': ''},
    }
    d.update(extra)
    return d


# ------------------------------------------------------------------ настройки
SETTINGS = [
    ('Логотип', [
        f('logo_color', 'Логотип для светлой шапки', 'image', return_format='id',
          instructions='Цветной. Показывается на внутренних страницах и при прокрутке.'),
        f('logo_white', 'Логотип для тёмной шапки и подвала', 'image', return_format='id',
          instructions='Белый. Показывается поверх фотографии на первом экране.'),
        f('favicon', 'Значок вкладки', 'image', return_format='id',
          instructions='Квадратный PNG, лучше 512×512.'),
    ]),
    ('Контакты', [
        f('lessons_format', 'Формат занятий', 'text', default_value='Очно в центре и онлайн'),
        f('lessons_format_note', 'Уточнение к формату', 'text',
          default_value='Некоторые педагоги проводят занятия на дому'),
    ]),
    ('Соцсети', [
        f('rutube_label', 'Подпись ссылки на Rutube', 'text', default_value='Канал на Rutube'),
        f('fund_logo', 'Логотип фонда', 'image', return_format='id'),
        f('fund_prefix', 'Подпись перед названием фонда', 'text', default_value='При поддержке фонда'),
    ]),
    ('Подвал', [
        f('footer_menu_title', 'Заголовок колонки с разделами', 'text', default_value='Разделы'),
        f('footer_directions_title', 'Заголовок колонки с направлениями', 'text', default_value='Направления'),
        f('footer_directions_count', 'Сколько направлений показывать', 'number',
          default_value=5, min=1, max=12),
        f('footer_contacts_title', 'Заголовок колонки с контактами', 'text', default_value='Контакты'),
        f('footer_copyright', 'Строка копирайта', 'text', default_value='Все права защищены.',
          instructions='Год и название центра подставляются перед этой строкой.'),
    ]),
    ('Кнопки', [
        f('cta_label', 'Надпись на кнопке записи', 'text', default_value='Записаться на пробный урок'),
        f('cta_label_short', 'Короткая надпись (шапка)', 'text', default_value='Записаться'),
    ]),
    ('CRM «Мой класс»', [
        f('schedule_eyebrow', 'Расписание: надглавие', 'text', default_value='ЗАПИСЬ ОНЛАЙН'),
        f('schedule_title', 'Расписание: заголовок', 'text', default_value='Расписание занятий'),
        f('schedule_text', 'Расписание: текст', 'textarea', rows=3,
          default_value='Актуальные группы и свободное время — напрямую из системы «Мой класс».'),
    ]),
    ('Форма записи', [
        f('booking_title', 'Заголовок окна', 'text', default_value='Записаться на пробный урок'),
        f('booking_subtitle', 'Подзаголовок окна', 'text',
          default_value='Заполните два поля — остальное уточним по телефону.'),
        f('booking_aside_title', 'Заголовок левой колонки', 'text',
          default_value='Первый урок — чтобы просто попробовать'),
        f('booking_aside_text', 'Текст левой колонки', 'textarea', rows=3,
          default_value='Знакомство с педагогом и инструментом. Ни к чему не обязывает.'),
        f('booking_points', 'Что получит посетитель', 'textarea', rows=4,
          instructions='По одному пункту в строке.',
          default_value='Подберём педагога под возраст и характер\nПокажем центр и инструменты\nОтветим на вопросы родителей'),
        f('booking_submit_label', 'Надпись на кнопке отправки', 'text', default_value='Отправить заявку'),
        f('booking_consent_text', 'Текст согласия на обработку данных', 'textarea', rows=2,
          default_value='Я согласен(-на) на обработку персональных данных'),
        f('booking_phone_label', 'Подпись перед телефоном', 'text', default_value='Или позвоните'),
        f('booking_crm_note', 'Подпись перед ссылкой на расписание', 'text',
          default_value='Или выберите время сами в системе «Мой класс»'),
        f('booking_success_text', 'Сообщение после отправки', 'textarea', rows=2,
          default_value='Спасибо! Заявка принята — мы перезвоним в ближайшее рабочее время.'),
    ]),
    ('Иконки', [
        f('icon_set', 'Свои иконки', 'repeater', button_label='Добавить иконку',
          instructions='Загруженные сюда SVG появятся во всех списках выбора иконки — '
                       'в направлениях, особых программах и принципах методики. '
                       'Файл вставляется в страницу как разметка, поэтому иконка '
                       'подхватывает цвет темы.',
          sub_fields=[
              sub('set', 'icon_name', 'Название в списке'),
              sub('set', 'icon_file', 'Файл SVG', 'file',
                  return_format='id', mime_types='svg'),
          ]),
    ]),
    ('Служебные страницы', [
        f('hero_default_image', 'Фон первого экрана по умолчанию', 'image', return_format='id',
          instructions='Показывается на страницах без своей фотографии.'),
        f('err404_eyebrow', '404: надглавие', 'text', default_value='Ошибка 404'),
        f('err404_title', '404: заголовок', 'text', default_value='Такой страницы нет'),
        f('err404_text', '404: текст', 'textarea', rows=2,
          default_value='Возможно, она переехала. Вернитесь на главную или запишитесь на урок.'),
        f('err404_btn_home', '404: кнопка на главную', 'text', default_value='На главную'),
        f('err404_btn_cta', '404: кнопка записи', 'text', default_value='Записаться на урок'),
        f('search_empty', 'Поиск: когда ничего не нашлось', 'textarea', rows=2,
          default_value='Ничего не нашлось. Попробуйте другой запрос.'),
        f('archive_empty', 'Список новостей: когда пусто', 'textarea', rows=2,
          default_value='Здесь пока пусто.'),
    ]),
]

# ------------------------------------------------------------------ главная
HOME = [
    ('Первый экран', [
        f('hero_eyebrow_icon', 'Иконка надглавия', 'select', choices={}, allow_null=1,
          instructions='Список берётся из набора иконок темы.'),
        f('hero_btn_primary', 'Первая кнопка: текст', 'text', default_value='Записаться на пробный урок'),
        f('hero_btn_secondary', 'Вторая кнопка: текст', 'text', default_value='Наши направления'),
        f('hero_btn_secondary_url', 'Вторая кнопка: ссылка', 'text', default_value='#directions'),
    ]),
    ('О центре', [
        f('about_eyebrow', 'Надглавие', 'text', default_value='О центре'),
        f('about_point_icon', 'Иконка пунктов списка', 'select', choices={}, allow_null=1),
        f('about_btn_text', 'Кнопка: текст', 'text', default_value='Подробнее о центре'),
        f('about_link_text', 'Ссылка рядом: текст', 'text', default_value='Наши педагоги'),
    ]),
    ('Новости', [
        f('news_eyebrow', 'Надглавие', 'text', default_value='Новости'),
        f('news_title', 'Заголовок', 'text', default_value='Чем живёт центр'),
        f('news_text', 'Текст под заголовком', 'textarea', rows=2),
        f('news_count', 'Сколько новостей показывать', 'number', default_value=3, min=1, max=9),
        f('news_btn_text', 'Кнопка: текст', 'text', default_value='Все новости'),
    ]),
    ('Направления', [
        f('dirs_eyebrow', 'Надглавие', 'text', default_value='Наши направления'),
        f('dirs_title', 'Заголовок', 'text',
          instructions='Можно вставить %d — подставится число направлений.',
          default_value='%d путей к искусству'),
        f('dirs_text', 'Текст под заголовком', 'textarea', rows=2,
          default_value='Инструменты, вокал, сцена и живопись — можно выбрать одно направление или собрать своё сочетание.'),
        f('dirs_limit', 'Сколько направлений показывать', 'number', default_value=6, min=1, max=12),
        f('dirs_btn_text', 'Кнопка: текст', 'text', default_value='Посмотреть все направления'),
    ]),
    ('Педагоги', [
        f('teachers_eyebrow', 'Надглавие', 'text', default_value='Педагоги'),
        f('teachers_title', 'Заголовок', 'text', default_value='Люди, к которым хочется возвращаться'),
        f('teachers_text', 'Текст под заголовком', 'textarea', rows=2,
          default_value='Нажмите на фотографию, чтобы открыть биографию, расписание и записаться на урок.'),
        f('teachers_btn_text', 'Кнопка: текст', 'text', default_value='Все педагоги'),
    ]),
    ('Видео', [
        f('video_eyebrow', 'Надглавие', 'text', default_value='Видео'),
        f('video_title', 'Заголовок', 'text', default_value='Как у нас проходят занятия'),
        f('video_text', 'Текст под заголовком', 'textarea', rows=2),
    ]),
    ('Отзывы', [
        f('reviews_eyebrow', 'Надглавие', 'text', default_value='Отзывы'),
        f('reviews_title', 'Заголовок', 'text', default_value='Что говорят родители и ученики'),
        f('reviews_text', 'Текст под заголовком', 'textarea', rows=2),
    ]),
    ('Разделители', [
        f('brush_eyebrow', 'Полоса с кистью: надглавие', 'text'),
        f('brush_title', 'Полоса с кистью: заголовок', 'text'),
        f('brush_text', 'Полоса с кистью: текст', 'textarea', rows=2),
        f('brush_video', 'Полоса с кистью: видео', 'file', return_format='url', mime_types='mp4,webm'),
        f('brush_poster', 'Полоса с кистью: обложка видео', 'image', return_format='id'),
        f('notes_caption', 'Нотная полоса: подпись', 'text'),
    ]),
    ('Как нас найти', [
        f('contacts_eyebrow', 'Надглавие', 'text', default_value='Как нас найти'),
        f('contacts_title', 'Заголовок', 'text', default_value='Мы рядом с метро Минская'),
        f('contacts_text', 'Текст под заголовком', 'textarea', rows=2),
    ]),
    ('Призыв к записи', [
        f('cta_eyebrow', 'Надглавие', 'text', default_value='Запись'),
        f('cta_form_title', 'Заголовок карточки формы', 'text'),
        f('cta_form_text', 'Текст карточки формы', 'textarea', rows=2),
    ]),
]

# ------------------------------------------------------------------ страницы
PAGE_ABOUT = [
    ('Первый экран', [
        f('about_hero_title', 'Заголовок', 'text',
          instructions='Показывается вместо названия страницы. В меню и в хлебных крошках останется «О нас».',
          default_value='Центр искусств, где ребёнку хочется остаться'),
        f('about_hero_text', 'Текст под заголовком', 'textarea', rows=2,
          default_value='Музыка, живопись и сцена для детей от трёх лет. Мы соединяем академическую базу с живым, радостным обучением.'),
        f('about_hero_image', 'Фотография первого экрана', 'image', return_format='id'),
    ]),
    ('Кто мы', [
        f('intro_eyebrow', 'Надглавие', 'text', default_value='Кто мы'),
        f('intro_title', 'Заголовок', 'text', default_value='Центр, построенный вокруг'),
        f('intro_accent', 'Выделенное слово в заголовке', 'text',
          instructions='Дописывается к заголовку и подсвечивается золотым.',
          default_value='ученика'),
        f('intro_text', 'Текст', 'wysiwyg', media_upload=0, toolbar='basic'),
        f('intro_btn_text', 'Кнопка записи: текст', 'text', default_value='Записаться на пробный урок'),
        f('intro_link_text', 'Вторая кнопка: текст', 'text', default_value='Познакомиться с педагогами'),
        f('intro_gallery', 'Фотографии рядом с текстом', 'gallery', return_format='id',
          instructions='Четыре снимка смотрятся лучше всего.'),
    ]),
    ('Цифры', [
        f('about_facts', 'Цифры', 'repeater', layout='table', button_label='Добавить цифру',
          sub_fields=[
              sub('pgabout', 'num', 'Число'),
              sub('pgabout', 'label', 'Подпись'),
          ]),
    ]),
    ('Как всё устроено', [
        f('steps_eyebrow', 'Надглавие', 'text', default_value='Как всё устроено'),
        f('steps_title', 'Заголовок', 'text', default_value='Путь ученика — от первой заявки до сцены'),
        f('steps', 'Шаги', 'repeater', button_label='Добавить шаг',
          sub_fields=[
              sub('pgabout', 'step_label', 'Подпись слева', 'text'),
              sub('pgabout', 'step_title', 'Заголовок шага', 'text'),
              sub('pgabout', 'step_text', 'Описание', 'textarea', rows=3),
          ]),
        f('steps_quote', 'Цитата', 'textarea', rows=2),
        f('steps_quote_author', 'Кто сказал', 'text'),
        f('steps_card_image', 'Карточка: фотография', 'image', return_format='id'),
        f('steps_card_title', 'Карточка: заголовок', 'text',
          default_value='Пробный урок ни к чему не обязывает'),
        f('steps_card_text', 'Карточка: текст', 'textarea', rows=3),
        f('steps_card_btn', 'Карточка: кнопка', 'text', default_value='Записаться'),
    ]),
    ('Атмосфера', [
        f('mood_eyebrow', 'Надглавие', 'text', default_value='Атмосфера'),
        f('mood_title', 'Заголовок', 'text', default_value='Как проходят наши занятия'),
        f('mood_text', 'Текст под заголовком', 'textarea', rows=2,
          default_value='Светлые классы, два рояля, мольберты и много воздуха. И дети, которым здесь интересно.'),
        f('mood_gallery', 'Фотографии', 'gallery', return_format='id'),
    ]),
    ('Партнёр', [
        f('partner_eyebrow', 'Надглавие', 'text', default_value='Партнёр центра'),
        f('partner_title', 'Заголовок', 'text', default_value='При поддержке фонда ФОРТЕФОРМА'),
        f('partner_text', 'Текст', 'textarea', rows=4,
          default_value='Фонд поддерживает музыкальное образование и культурные проекты. Благодаря этому партнёрству в центре проходят мастер-классы приглашённых профессоров, концерты и творческие программы для детей.'),
        f('partner_btn_text', 'Кнопка: текст', 'text', default_value='Сайт фонда'),
        f('partner_note', 'Подпись под знаком фонда', 'text',
          default_value='Фонд поддержки и развития культурных и социальных проектов'),
    ]),
    ('Отзывы', [
        f('about_reviews_eyebrow', 'Надглавие', 'text', default_value='Отзывы'),
        f('about_reviews_title', 'Заголовок', 'text', default_value='Родители — о центре'),
        f('about_reviews_link', 'Надпись ссылки на все отзывы', 'text', default_value='Все отзывы'),
    ]),
    ('Призыв к записи', [
        f('about_cta_title', 'Заголовок', 'text'),
        f('about_cta_text', 'Текст', 'textarea', rows=2),
    ]),
]

PAGE_DIRS = [
    ('Первый экран', [
        f('dirs_hero_title', 'Заголовок', 'text', default_value='Восемь путей к искусству'),
        f('dirs_hero_text', 'Текст под заголовком', 'textarea', rows=3,
          default_value='Инструменты, вокал, сцена и живопись. Можно выбрать одно направление '
                        'или собрать своё сочетание — программа подстроится под ребёнка.'),
        f('dirs_hero_image', 'Фон первого экрана', 'image', return_format='id'),
    ]),
    ('Секция направлений', [
        # Без значений по умолчанию: в макете над плитками заголовка нет,
        # первый экран уже назвал раздел. Заполните, если он всё-таки нужен.
        f('dirs_eyebrow', 'Надглавие', 'text'),
        f('dirs_title', 'Заголовок', 'text'),
        f('dirs_text', 'Текст под заголовком', 'textarea', rows=2),
    ]),
    ('Особые программы', [
        f('special_eyebrow', 'Надглавие', 'text', default_value='Особые программы'),
        f('special_title', 'Заголовок', 'text', default_value='Не только уроки по расписанию'),
        f('special_text', 'Текст под заголовком', 'textarea', rows=2),
        f('special_items', 'Программы', 'repeater', button_label='Добавить программу',
          instructions='Секция не выводится, пока список пуст.',
          sub_fields=[
              sub('pgdirs', 'special_icon', 'Иконка', 'select', choices={}, allow_null=1),
              sub('pgdirs', 'special_title', 'Заголовок'),
              sub('pgdirs', 'special_text', 'Описание', 'textarea', rows=3),
          ]),
    ]),
    ('Преимущества', [
        f('adv_eyebrow', 'Надглавие', 'text', default_value='Наши преимущества'),
        f('adv_title', 'Заголовок', 'text', default_value='Авторская методика центра'),
        f('adv_text', 'Текст под заголовком', 'textarea', rows=2,
          default_value='Мы объединили лучшие традиции музыкального образования '
                        'с современными подходами — и описали это восемью принципами.'),
        f('adv_items', 'Принципы', 'repeater', button_label='Добавить принцип',
          instructions='Нумерация проставляется сама. Секция не выводится, пока список пуст.',
          sub_fields=[
              sub('pgdirs', 'adv_icon', 'Иконка', 'select', choices={}, allow_null=1),
              sub('pgdirs', 'adv_title', 'Заголовок'),
              sub('pgdirs', 'adv_text', 'Описание', 'textarea', rows=2),
          ]),
    ]),
    ('Вопросы и ответы', [
        f('faq_eyebrow', 'Надглавие', 'text', default_value='Вопросы и ответы'),
        f('faq_title', 'Заголовок', 'text', default_value='Часто задаваемые вопросы'),
        f('faq_items', 'Вопросы', 'repeater', button_label='Добавить вопрос',
          instructions='Секция не выводится, пока список пуст.',
          sub_fields=[
              sub('pgdirs', 'faq_q', 'Вопрос'),
              sub('pgdirs', 'faq_a', 'Ответ', 'textarea', rows=4),
          ]),
    ]),
    ('Призыв к записи', [
        f('dirs_cta_eyebrow', 'Надглавие', 'text', default_value='Не знаете, что выбрать?'),
        f('dirs_cta_title', 'Заголовок', 'text',
          default_value='Поможем подобрать направление и педагога'),
        f('dirs_cta_text', 'Текст', 'textarea', rows=3,
          default_value='Расскажите о ребёнке: возраст, характер, что ему нравится. Мы предложим '
                        'подходящее направление и педагога, а первый урок покажет, попали ли мы в точку.'),
        f('dirs_cta_points', 'Короткие пункты', 'textarea', rows=4,
          instructions='По одному пункту в строке. Выводятся галочками под текстом.',
          default_value='Пробное занятие ни к чему не обязывает\n'
                        'Можно начать без своего инструмента\n'
                        'Есть очные и онлайн-форматы'),
        f('dirs_form_title', 'Форма: заголовок', 'text', default_value='Подобрать направление'),
        f('dirs_form_text', 'Форма: подзаголовок', 'text',
          default_value='Оставьте контакты — перезвоним и всё обсудим.'),
    ]),
]

PAGE_TEACHERS = [
    ('Первый экран', [
        f('teachers_hero_title', 'Заголовок', 'text',
          default_value='Педагоги, которым доверяют детей'),
        f('teachers_hero_text', 'Текст под заголовком', 'textarea', rows=3,
          default_value='Преподаватели Московской консерватории, РАМ им. Гнесиных, ГИТИСа и ВГИКа. '
                        'Нажмите на фотографию — откроется биография, расписание и запись на урок.'),
        f('teachers_hero_image', 'Фон первого экрана', 'image', return_format='id'),
    ]),
    ('Основной состав', [
        f('teachers_eyebrow', 'Надглавие', 'text', default_value='Основной состав'),
        f('teachers_title', 'Заголовок', 'text', default_value='Наши преподаватели'),
        f('teachers_text', 'Текст под заголовком', 'textarea', rows=2,
          default_value='Кнопка «Записаться» ведёт в систему «Мой класс» — там видно свободное время педагога.'),
    ]),
    ('Приглашённые мастера', [
        f('guests_eyebrow', 'Надглавие', 'text', default_value='Приглашённые мастера'),
        f('guests_title', 'Заголовок', 'text', default_value='Мастера, которые приходят'),
        f('guests_text', 'Текст под заголовком', 'textarea', rows=2,
          default_value='Профессора и заслуженные деятели искусств проводят у нас мастер-классы и творческие встречи.'),
    ]),
    ('Фотогалерея', [
        f('shots_eyebrow', 'Надглавие', 'text', default_value='Фотогалерея'),
        f('shots_title', 'Заголовок', 'text', default_value='Фотографии с уроков'),
        f('shots_text', 'Текст под заголовком', 'textarea', rows=2),
        f('shots_gallery', 'Фотографии', 'gallery', return_format='id',
          instructions='Секция не выводится, пока галерея пуста. '
                       'На широком экране это кладка, на телефоне — лента.'),
    ]),
    ('Видео', [
        f('teachers_video_eyebrow', 'Надглавие', 'text', default_value='Видео'),
        f('teachers_video_title', 'Заголовок', 'text',
          default_value='Приглашённые педагоги — в записи'),
        f('teachers_video_text', 'Текст под заголовком', 'textarea', rows=2,
          default_value='Видео открывается прямо на сайте, без перехода на Rutube.'),
    ]),
    ('Отзывы', [
        f('teachers_reviews_eyebrow', 'Надглавие', 'text', default_value='Отзывы'),
        f('teachers_reviews_title', 'Заголовок', 'text',
          default_value='Что говорят о наших педагогах'),
    ]),
    ('Призыв к записи', [
        f('teachers_cta_eyebrow', 'Надглавие', 'text', default_value='Обратная связь'),
        f('teachers_cta_title', 'Заголовок', 'text',
          default_value='Не выбрали педагога? Поможем'),
        f('teachers_cta_text', 'Текст', 'textarea', rows=3,
          default_value='Оставьте имя и телефон — мы перезвоним, расспросим о ребёнке и предложим '
                        'педагога, который подойдёт по возрасту, характеру и целям.'),
        f('teachers_phone_label', 'Подпись над телефоном', 'text', default_value='Позвонить сейчас'),
        f('teachers_form_title', 'Форма: заголовок', 'text', default_value='Подобрать педагога'),
        f('teachers_form_text', 'Форма: подзаголовок', 'text',
          default_value='Два поля — и мы свяжемся с вами в ближайшее рабочее время.'),
    ]),
]

PAGE_GUEST = [
    ('Видео', [
        f('guest_video', 'Ссылка на ролик', 'url',
          instructions='Ссылка вида https://rutube.ru/play/embed/… — ролик открывается '
                       'прямо на сайте, в секции «Видео» на странице педагогов.'),
    ]),
]

PAGE_NEWS = [
    ('Первый экран', [
        f('news_hero_title', 'Заголовок', 'text', default_value='Чем живёт центр искусств'),
        f('news_hero_text', 'Текст под заголовком', 'textarea', rows=2),
        f('news_hero_image', 'Фон первого экрана', 'image', return_format='id'),
    ]),
    ('Похожие новости', [
        f('news_related_eyebrow', 'Надглавие', 'text', default_value='Читайте также'),
        f('news_related_title', 'Заголовок', 'text', default_value='Другие новости центра'),
        f('news_related_count', 'Сколько показывать', 'number', default_value=3, min=1, max=6),
    ]),
    ('Фильтр по рубрикам', [
        f('news_filter_all', 'Надпись кнопки «все»', 'text', default_value='Все новости',
          instructions='Кнопки рубрик появляются сами, если у новостей на странице '
                       'больше одной рубрики.'),
    ]),
    ('Призыв к записи', [
        f('news_cta_title', 'Заголовок', 'text'),
        f('news_cta_text', 'Текст', 'textarea', rows=2),
    ]),
]

# группа -> (файл, префикс ключей, описание вкладок)
PLAN = [
    ('group_map_settings',   'set',     SETTINGS),
    ('group_map_front',      'home',    HOME),
    ('group_map_page_about', 'pgabout', PAGE_ABOUT),
    ('group_map_page_dirs',  'pgdirs',  PAGE_DIRS),
    ('group_map_page_teach', 'pgteach', PAGE_TEACHERS),
    ('group_map_page_news',  'pgnews',  PAGE_NEWS),
    ('group_map_guest',      'guest',   PAGE_GUEST),
]

# группы, которых ещё нет — их надо создать целиком
NEW_GROUPS = {
    'group_map_page_about': ('Страница «О нас»', [[{
        'param': 'page_template', 'operator': '==', 'value': 'page-about.php'}]]),
    'group_map_page_dirs': ('Страница «Наши направления»', [[{
        'param': 'page_template', 'operator': '==', 'value': 'page-directions.php'}]]),
    'group_map_page_teach': ('Страница «Педагоги»', [[{
        'param': 'page_template', 'operator': '==', 'value': 'page-teachers.php'}]]),
    'group_map_page_news': ('Страница новостей', [[{
        'param': 'page_type', 'operator': '==', 'value': 'posts_page'}]]),
}


def blank_group(key, title, location):
    return {
        'key': key, 'title': title, 'fields': [], 'location': location,
        'menu_order': 0, 'position': 'normal', 'style': 'default',
        'label_placement': 'top', 'instruction_placement': 'label',
        'hide_on_screen': '', 'active': True, 'description': '',
        'show_in_rest': 0,
    }


def full_field(spec, prefix):
    d = {
        'key': 'field_map_%s_%s' % (prefix, spec['name']),
        'label': spec['label'],
        'name': spec['name'],
        'aria-label': '',
        'type': spec['type'],
        'instructions': spec.get('instructions', ''),
        'required': 0,
        'conditional_logic': 0,
        'wrapper': {'width': '', 'class': '', 'id': ''},
    }
    for k, v in spec.items():
        if k not in ('name', 'label', 'type', 'instructions'):
            d[k] = v
    return d


def tab_field(label, prefix):
    # hash() в Python рандомизируется от запуска к запуску — ключ обязан быть
    # устойчивым, иначе повторный прогон заведёт вкладки заново.
    stamp = format(zlib.crc32(label.encode('utf-8')) & 0xFFFFF, 'x')
    return {
        'key': 'field_map_%s_tab_%s' % (prefix, stamp),
        'label': label, 'name': '', 'aria-label': '', 'type': 'tab',
        'instructions': '', 'required': 0, 'conditional_logic': 0,
        'wrapper': {'width': '', 'class': '', 'id': ''},
        'placement': 'top', 'endpoint': 0, 'selected': 0,
    }


added_total = 0

for key, prefix, plan in PLAN:
    path = os.path.join(JSON_DIR, key + '.json')

    if os.path.exists(path):
        group = json.load(io.open(path, encoding='utf-8'))
    else:
        title, location = NEW_GROUPS[key]
        group = blank_group(key, title, location)

    fields = group['fields']
    have = set(x.get('name') for x in fields if x.get('name'))
    added = []

    for tab_label, specs in plan:
        # ищем вкладку; нет — заводим в конце
        idx = None
        for i, x in enumerate(fields):
            if x['type'] == 'tab' and x['label'] == tab_label:
                idx = i
                break

        if idx is None:
            fields.append(tab_field(tab_label, prefix))
            idx = len(fields) - 1

        # конец блока вкладки — следующая вкладка или конец списка
        end = len(fields)
        for i in range(idx + 1, len(fields)):
            if fields[i]['type'] == 'tab':
                end = i
                break

        insert_at = end
        for spec in specs:
            if spec['name'] in have:
                continue
            fields.insert(insert_at, full_field(spec, prefix))
            insert_at += 1
            have.add(spec['name'])
            added.append('%s/%s' % (tab_label, spec['name']))

    io.open(path, 'w', encoding='utf-8').write(
        json.dumps(group, ensure_ascii=False, indent=4) + '\n')

    added_total += len(added)
    state = 'создана' if key in NEW_GROUPS and not added else ''
    print('%-24s полей всего %-3d  добавлено %-3d %s' % (
        key, len(fields), len(added), state))
    for a in added:
        print('      + %s' % a)

print('\nвсего добавлено полей: %d' % added_total)

# --------------------------------------------- вкладки в остальных группах
# Поля тут уже есть — вкладки только раскладывают их по смыслу. Скрипт вставит
# вкладку перед указанным полем, если её ещё нет.
TABS_BEFORE = [
    ('group_map_teacher', 'teach', [
        ('Основное', 'subject'),
        ('Подробно', 'bio'),
        ('Расписание', 'schedule'),
    ]),
    ('group_map_direction', 'dir', [
        ('Основное', 'dir_icon'),
        ('На главной', 'dir_featured'),
    ]),
    ('group_map_guest', 'guest', [
        ('Основное', 'guest_role'),
    ]),
    ('group_map_news', 'news', [
        ('Текст', 'news_badge'),
        ('Галерея', 'news_gallery'),
        ('Видео', 'news_video_url'),
    ]),
]

for key, prefix, tabs in TABS_BEFORE:
    path = os.path.join(JSON_DIR, key + '.json')

    if not os.path.exists(path):
        continue

    group = json.load(io.open(path, encoding='utf-8'))
    fields = group['fields']
    have = set(x['label'] for x in fields if x['type'] == 'tab')
    added = 0

    for label, before in tabs:
        if label in have:
            continue

        idx = None
        for i, x in enumerate(fields):
            if x.get('name') == before:
                idx = i
                break

        if idx is None:
            continue

        fields.insert(idx, tab_field(label, prefix))
        added += 1

    if added:
        io.open(path, 'w', encoding='utf-8').write(
            json.dumps(group, ensure_ascii=False, indent=4) + '\n')

    print('%-24s вкладок добавлено %d' % (key, added))

# --------------------------------------------- порядок вкладок
# Новые вкладки скрипт дописывает в конец. Здесь задаём, в каком порядке они
# должны идти — по порядку блоков на самой странице, чтобы редактор искал
# настройку там же, где видит блок.
TAB_ORDER = {
    'group_map_settings': [
        'Логотип', 'Контакты', 'Соцсети', 'Подвал', 'Кнопки',
        'CRM «Мой класс»', 'Форма записи', 'Иконки', 'Служебные страницы',
    ],
    'group_map_page_about': [
        'Первый экран', 'Кто мы', 'Цифры', 'Как всё устроено', 'Атмосфера',
        'Партнёр', 'Отзывы', 'Призыв к записи',
    ],
    'group_map_front': [
        'Первый экран', 'О центре', 'Новости', 'Направления', 'Педагоги',
        'Видео', 'Разделители', 'Отзывы', 'Как нас найти', 'Призыв к записи',
    ],
    'group_map_page_dirs': [
        'Первый экран', 'Секция направлений', 'Особые программы', 'Преимущества',
        'Вопросы и ответы', 'Призыв к записи',
    ],
    'group_map_page_teach': [
        'Первый экран', 'Основной состав', 'Приглашённые мастера', 'Фотогалерея',
        'Видео', 'Отзывы', 'Призыв к записи',
    ],
    'group_map_guest': [
        'Основное', 'Видео',
    ],
}

for key, order in TAB_ORDER.items():
    path = os.path.join(JSON_DIR, key + '.json')

    if not os.path.exists(path):
        continue

    group = json.load(io.open(path, encoding='utf-8'))
    fields = group['fields']

    # разрезаем список на блоки «вкладка + её поля»
    head, blocks, current = [], [], None

    for x in fields:
        if x['type'] == 'tab':
            current = [x['label'], [x]]
            blocks.append(current)
        elif current is None:
            head.append(x)
        else:
            current[1].append(x)

    known = [b for b in blocks if b[0] in order]
    rest = [b for b in blocks if b[0] not in order]
    known.sort(key=lambda b: order.index(b[0]))

    new_fields = head[:]
    for b in known + rest:
        new_fields.extend(b[1])

    if [x['key'] for x in new_fields] != [x['key'] for x in fields]:
        group['fields'] = new_fields
        io.open(path, 'w', encoding='utf-8').write(
            json.dumps(group, ensure_ascii=False, indent=4) + '\n')
        print('%-24s вкладки переставлены' % key)
    else:
        print('%-24s порядок вкладок уже верный' % key)
