# -*- coding: utf-8 -*-
"""Выгружает контент из generator.py в JSON для первичного наполнения WordPress.

Тема читает этот файл в inc/seed.php и создаёт страницы, педагогов,
направления, отзывы и новости — вместе с картинками из assets/img.

Запуск:  python3 tools/build-theme-seed.py
"""
import io, json, os, shutil, tempfile

ROOT = os.path.dirname(os.path.dirname(os.path.abspath(__file__)))
GEN = os.path.join(ROOT, 'tools', 'generator.py')
DEST = os.path.join(ROOT, 'theme', 'musicartplus', 'seed', 'content.json')

src = io.open(GEN, encoding='utf-8').read()


def load_namespace():
    """Выполняет generator.py целиком в песочнице и забирает готовые данные.

    Сборщик по пути пишет HTML — уводим его во временную папку, чтобы не
    трогать рабочие файлы сайта.
    """
    sandbox = tempfile.mkdtemp(prefix='map-seed-')
    os.makedirs(os.path.join(sandbox, 'tools'))

    ns = {'__file__': os.path.join(sandbox, 'tools', 'generator.py'),
          '__name__': 'generator_data'}
    try:
        exec(compile(src, 'generator.py', 'exec'), ns)
    finally:
        shutil.rmtree(sandbox, ignore_errors=True)

    return ns


ns = load_namespace()

teachers = []
for t in ns['TEACHERS']:
    teachers.append({
        'slug': t['slug'],
        'name': t['name'],
        'photo': 'assets/img/teachers/' + t['photo'],
        'subject': t['subject'],
        'role': t['role'],
        'short': t['short'],
        'bio': t['bio'],
        'facts': t['facts'],
        # sc() в генераторе уже отдаёт [{'day': ..., 'time': ...}] —
        # распаковывать это как пары нельзя, получатся ключи вместо значений.
        'schedule': t['schedule'],
    })

guests = []
for g in ns['GUESTS']:
    guests.append({
        'slug': g['slug'],
        'name': g['name'],
        'photo': 'assets/img/guests/' + g['photo'],
        'role': g['role'],
        'facts': g['facts'],
        'video': g.get('video', ''),
    })

reviews = []
for r in ns['REVIEWS']:
    reviews.append({
        'name': r['name'],
        'role': r['role'],
        'text': r['text'],
    })

directions = []
for aid, icon, title, age, text, fmt, dur in ns['DIRECTIONS']:
    directions.append({
        'slug': aid.replace('dir-', ''),
        'icon': icon,
        'title': title,
        'age': age,
        'text': text,
        'format': fmt,
        'duration': dur,
    })

MONTHS = {'января': 1, 'февраля': 2, 'марта': 3, 'апреля': 4, 'мая': 5, 'июня': 6,
          'июля': 7, 'августа': 8, 'сентября': 9, 'октября': 10, 'ноября': 11, 'декабря': 12}

news = []
for n in ns['NEWS']:
    news.append({
        'slug': n['slug'],
        'title': n['title'],
        'excerpt': n['text'],
        'tag': n['tag'],
        'image': 'assets/img/gallery/' + n['img'],
        'date': '%s-%02d-%02d 12:00:00' % (n['y'], MONTHS[n['m']], int(n['d'])),
        'body': ns['FULL'].get(n['slug'], []),
        'photos': ['assets/img/gallery/' + img for img, _ in n.get('photos', [])],
        'video': ({
            'url':      n['video']['src'],
            'kind':     n['video']['typ'],
            'page':     n['video'].get('page', ''),
            'host':     n['video'].get('host', ''),
            'poster':   n['video']['poster'],
            'title':    n['video']['title'],
            'subtitle': n['video']['sub'],
            'vertical': bool(n['video'].get('vertical')),
        } if n.get('video') else None),
    })

# ------------------------------------------------------ страница «О нас»
about = {
    'about_hero_title': 'Центр искусств, где ребёнку хочется остаться',
    'about_hero_text': ('Музыка, живопись и сцена для детей от трёх лет. '
                        'Мы соединяем академическую базу с живым, радостным обучением.'),
    'about_hero_image': 'assets/img/gallery/g07.jpg',

    'intro_eyebrow': 'Кто мы',
    'intro_title': 'Центр, построенный вокруг',
    'intro_accent': 'ученика',
    'intro_text': (
        '<p>MusicArtPlus — центр искусств в Москве, недалеко от станции метро «Минская». '
        'У нас занимаются дошкольники, школьники и взрослые: кто-то приходит за профессией, '
        'кто-то — за радостью и уверенностью в себе.</p>'
        '<p>Мы верим, что музыка и искусство не должны быть испытанием. Поэтому в основе '
        'нашей работы — авторская методика, которая объединяет лучшие традиции музыкального '
        'образования с современными подходами: крепкая академическая база, живой диалог '
        'с учеником и обязательная практика на сцене.</p>'
        '<p>С нашими учениками работают преподаватели Московской консерватории, '
        'РАМ им. Гнесиных, ГИТИСа и ВГИКа, а также приглашённые профессора и заслуженные '
        'деятели искусств.</p>'
    ),
    'intro_btn_text': 'Записаться на пробный урок',
    'intro_link_text': 'Познакомиться с педагогами',
    'intro_gallery': ['assets/img/gallery/' + x for x in ('g11.jpg', 'g02.jpg', 'g15.jpg', 'g04.jpg')],

    'about_facts': [{'num': n, 'label': l} for n, l in ns['STATS']],

    'steps_eyebrow': 'Как всё устроено',
    'steps_title': 'Путь ученика — от первой заявки до сцены',
    'steps': [{'step_label': a, 'step_title': b, 'step_text': c} for a, b, c in ns['STEPS']],
    'steps_quote': ('Здесь тебя слышат. Здесь верят в твой потенциал, '
                    'даже если ты ещё сам в него не веришь.'),
    'steps_quote_author': 'Валентина, мама ученика',
    'steps_card_image': 'assets/img/gallery/g07.jpg',
    'steps_card_title': 'Пробный урок ни к чему не обязывает',
    'steps_card_text': ('Это спокойное знакомство: ребёнок пробует, родители задают вопросы, '
                        'педагог рассказывает, что и как будет дальше.'),
    'steps_card_btn': 'Записаться',

    'mood_eyebrow': 'Атмосфера',
    'mood_title': 'Как проходят наши занятия',
    'mood_text': 'Светлые классы, два рояля, мольберты и много воздуха. И дети, которым здесь интересно.',
    'mood_gallery': ['assets/img/gallery/' + x for x in
                     ('g07.jpg', 'g11.jpg', 'g02.jpg', 'g15.jpg', 'g04.jpg', 'g03.jpg',
                      'g09.jpg', 'g01.jpg', 'g12.jpg', 'g14.jpg', 'g17.jpg', 'g19.jpg')],

    'partner_eyebrow': 'Партнёр центра',
    'partner_title': 'При поддержке фонда ФОРТЕФОРМА',
    'partner_text': ('Фонд поддерживает музыкальное образование и культурные проекты. '
                     'Благодаря этому партнёрству в центре проходят мастер-классы приглашённых '
                     'профессоров, концерты и творческие программы для детей.'),
    'partner_btn_text': 'Сайт фонда',
    'partner_note': 'Фонд поддержки и развития культурных и социальных проектов',

    'about_cta_title': 'Приходите знакомиться',
    'about_cta_text': 'Покажем центр, познакомим с педагогом и подберём программу под ребёнка.',
}

front = {
    'hero_eyebrow': 'Москва · м. Минская',
    'hero_title': 'Центр, построенный на любви к музыке и искусству',
    'hero_text': ('Фортепиано, скрипка, труба, вокал, сцена и живопись — для детей с 3 лет '
                  'и для взрослых. Педагоги Московской консерватории, РАМ им. Гнесиных, ГИТИСа и ВГИКа.'),
    'hero_slides': ['assets/img/gallery/' + img for img, _ in ns['HERO_SLIDES']],
    'hero_facts': [{'num': n, 'label': l} for n, l in ns['FACTS']],
    'videos': [{'title': t, 'url': src_, 'cover': poster, 'subtitle': sub}
               for src_, _typ, poster, t, sub in ns['VIDEOS_HOME']],
    'about_title': 'Мы учим не играть «правильно», а учим слышать и чувствовать Музыку',
    'about_text': (
        'MusicArtPlus — центр искусств в Москве, где дети от трёх лет и взрослые '
        'знакомятся с музыкой, живописью и сценой. Мы соединяем крепкую академическую '
        'базу с современными методиками и авторскими программами для каждого ученика.'),
    'about_image': 'assets/img/gallery/g15.jpg',
    'about_points': [
        'Уникальная авторская методика: традиции музыкального образования и современные подходы',
        'Педагоги Московской консерватории, РАМ им. Гнесиных, ГИТИСа и ВГИКа',
        'Индивидуальная программа под характер, возраст и цели ребёнка',
        'Проект поддержан фондом ФОРТЕФОРМА',
    ],
    'cta_title': 'Первый урок — чтобы просто попробовать',
    'cta_text': ('Оставьте имя и телефон: мы перезвоним, расспросим о ребёнке и подберём '
                 'педагога и удобное время. Пробное занятие ни к чему не обязывает.'),
}

options = {
    'phone': ns['PHONE'],
    'phone_href': ns['PHONE_HREF'],
    'email': ns['MAIL'],
    'address': ns['ADDR'],
    'address_note': 'вход со стороны запасного входа',
    'telegram': ns['TG'],
    'instagram': ns['IG'],
    'rutube': ns['RT'],
    'fund_url': ns['FUND'],
    'fund_name': 'ФОРТЕФОРМА',
    'directions_list': '\n'.join(ns['DIRECTION_OPTIONS'])
    if 'DIRECTION_OPTIONS' in ns else '',
}

data = {
    'generated': True,
    'options': options,
    'front': front,
    'teachers': teachers,
    'guests': guests,
    'reviews': reviews,
    'directions': directions,
    'news': news,
    'about': about,
    'tags': ns.get('TAGS', []),
}

if not os.path.isdir(os.path.dirname(DEST)):
    os.makedirs(os.path.dirname(DEST))

io.open(DEST, 'w', encoding='utf-8').write(
    json.dumps(data, ensure_ascii=False, indent=1))

print('педагогов %d, мастеров %d, отзывов %d, направлений %d, новостей %d -> %s' % (
    len(teachers), len(guests), len(reviews), len(directions), len(news),
    os.path.relpath(DEST, ROOT)))
