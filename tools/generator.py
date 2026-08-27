# -*- coding: utf-8 -*-
"""Сборщик статических страниц MusicArtPlus."""
import io, json, os

OUT = os.path.dirname(os.path.dirname(os.path.abspath(__file__)))  # папка site/
PHONE = '+7 903 102-51-11'
PHONE_HREF = '+79031025111'
MAIL = 'musicartplus@yandex.ru'
ADDR = 'Москва, ул. Улофа Пальме, д. 5 (м. Минская)'
TG = 'https://t.me/MusicArtPlus'
IG = 'https://www.instagram.com/music_art_plus'
RT = 'https://rutube.ru/channel/76411207'
FUND = 'https://forteforma.ru/'

# ---------------------------------------------------------------- иконки
I = {}
I['tg'] = '<svg viewBox="0 0 24 24" fill="currentColor" aria-hidden="true"><path d="M21.9 4.3 19 20.1c-.2 1-.8 1.2-1.6.8l-4.5-3.3-2.2 2.1c-.25.25-.45.45-.92.45l.33-4.66 8.3-7.5c.36-.32-.08-.5-.56-.18l-10.26 6.46-4.42-1.38c-.96-.3-.98-.96.2-1.42l17.28-6.66c.8-.3 1.5.18 1.24 1.5z"/></svg>'
I['ig'] = '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" aria-hidden="true"><rect x="3" y="3" width="18" height="18" rx="5.2"/><circle cx="12" cy="12" r="4"/><circle cx="17.3" cy="6.7" r="1.15" fill="currentColor" stroke="none"/></svg>'
I['rt'] = '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" aria-hidden="true"><rect x="2.4" y="4.6" width="19.2" height="14.8" rx="4.2"/><path d="M10.2 9.1 15.4 12l-5.2 2.9z" fill="currentColor" stroke="none"/></svg>'
I['ar'] = '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M5 12h14M13 6l6 6-6 6"/></svg>'
I['al'] = '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M19 12H5M11 18l-6-6 6-6"/></svg>'
I['close'] = '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" aria-hidden="true"><path d="M5 5l14 14M19 5L5 19"/></svg>'
I['plus'] = '<svg viewBox="0 0 24 24" aria-hidden="true"><path d="M12 5v14M5 12h14"/></svg>'
I['check'] = '<svg viewBox="0 0 24 24" aria-hidden="true"><path d="M4 12.6l5.2 5.2L20 6.6"/></svg>'
I['star'] = '<svg viewBox="0 0 24 24" aria-hidden="true"><path d="M12 2.6l2.92 6.02 6.58.92-4.78 4.6 1.16 6.5L12 17.5l-5.88 3.14 1.16-6.5-4.78-4.6 6.58-.92z"/></svg>'
I['play'] = '<svg viewBox="0 0 24 24" aria-hidden="true"><path d="M7 4.6v14.8L20.2 12z"/></svg>'
I['phone'] = '<svg viewBox="0 0 24 24" aria-hidden="true"><path d="M6.6 3h3l1.5 4-2 1.5a12.2 12.2 0 006.4 6.4l1.5-2 4 1.5v3a2 2 0 01-2.2 2A17.2 17.2 0 014.6 5.2 2 2 0 016.6 3z"/></svg>'
I['pin'] = '<svg viewBox="0 0 24 24" aria-hidden="true"><path d="M12 21.2s7-5.7 7-11.2a7 7 0 10-14 0c0 5.5 7 11.2 7 11.2z"/><circle cx="12" cy="10" r="2.6"/></svg>'
I['mail'] = '<svg viewBox="0 0 24 24" aria-hidden="true"><rect x="3" y="5" width="18" height="14" rx="3.2"/><path d="M4 7.4l8 5.4 8-5.4"/></svg>'
I['clock'] = '<svg viewBox="0 0 24 24" aria-hidden="true"><circle cx="12" cy="12" r="9"/><path d="M12 6.8v5.4l3.4 2"/></svg>'
# направления
I['piano'] = '<svg viewBox="0 0 24 24"><rect x="2.6" y="4.4" width="18.8" height="15.2" rx="2.6"/><path d="M9 4.4v9M15 4.4v9M2.6 13.4h18.8"/></svg>'
I['violin'] = '<svg viewBox="0 0 24 24"><path d="M14.6 3.6l5.8 5.8M17.1 6.1l-8.4 8.4"/><path d="M9 12.6c-1.5-.6-3.1.3-3.9 1.7-.5.9-1.6 1-2.2 1.8-.9 1.1-.6 2.9.7 3.7 1.3.8 3.1.4 3.8-1 .4-.8.6-1.9 1.5-2.4 1.4-.8 2.2-2.5 1.4-3.8"/></svg>'
I['trumpet'] = '<svg viewBox="0 0 24 24"><rect x="2.6" y="9.4" width="10.4" height="5.2" rx="1.6"/><path d="M13 9.8l6.6-3.4v11.2L13 14.2"/><path d="M6 9.4V7.2M9.4 9.4V7.2"/></svg>'
I['mic'] = '<svg viewBox="0 0 24 24"><rect x="9" y="2.8" width="6" height="11" rx="3"/><path d="M5.6 11.4a6.4 6.4 0 0012.8 0M12 17.8v3.4M8.6 21.2h6.8"/></svg>'
I['masks'] = '<svg viewBox="0 0 24 24"><path d="M3.6 5.2h9.2v5.6a4.6 4.6 0 01-9.2 0z"/><path d="M11.4 5.2h9v5.6a4.6 4.6 0 01-6 4.4"/><path d="M6.4 8h.01M10 8h.01"/></svg>'
I['palette'] = '<svg viewBox="0 0 24 24"><path d="M12 3a9 9 0 100 18c1.2 0 1.9-1 1.5-2-.4-1.2.4-2.2 1.7-2.2h1.6a4.2 4.2 0 004.2-4.2C21 7.3 16.8 3 12 3z"/><circle cx="7.6" cy="11.4" r="1"/><circle cx="10.6" cy="7.6" r="1"/><circle cx="15.2" cy="8.4" r="1"/></svg>'
I['note'] = '<svg viewBox="0 0 24 24"><circle cx="6.6" cy="17.4" r="2.9"/><circle cx="17.6" cy="15" r="2.9"/><path d="M9.5 17.4V6.2l11-2.2V15"/></svg>'
I['child'] = '<svg viewBox="0 0 24 24"><circle cx="12" cy="7.2" r="3.3"/><path d="M5 20.6c0-3.7 3.1-6.6 7-6.6s7 2.9 7 6.6"/><path d="M18.6 3.4l.7 1.6 1.7.2-1.3 1.2.4 1.7-1.5-.9-1.5.9.4-1.7-1.3-1.2 1.7-.2z"/></svg>'
I['users'] = '<svg viewBox="0 0 24 24"><circle cx="9" cy="8" r="3.3"/><path d="M2.6 20.4c0-3.4 2.9-6.2 6.4-6.2s6.4 2.8 6.4 6.2"/><path d="M16.2 5.2a3.3 3.3 0 010 5.9M17.6 14.6c2.3.7 3.8 2.7 3.8 5.1"/></svg>'
I['book'] = '<svg viewBox="0 0 24 24"><path d="M3.6 4.6h5.8a3.2 3.2 0 013.2 3.2v12a2.6 2.6 0 00-2.6-2.6H3.6z"/><path d="M20.4 4.6h-5.8a3.2 3.2 0 00-3.2 3.2v12a2.6 2.6 0 012.6-2.6h6.4z"/></svg>'
I['online'] = '<svg viewBox="0 0 24 24"><rect x="2.6" y="4.4" width="18.8" height="12.4" rx="2.6"/><path d="M8 20.6h8M12 16.8v3.8"/><path d="M10.4 8.6l4.2 2.4-4.2 2.4z"/></svg>'
# преимущества
I['cap'] = '<svg viewBox="0 0 24 24"><path d="M12 4.2 22 9l-10 4.8L2 9z"/><path d="M6.4 11.2v4.6c0 1.7 2.5 3 5.6 3s5.6-1.3 5.6-3v-4.6"/></svg>'
I['person'] = '<svg viewBox="0 0 24 24"><circle cx="12" cy="8" r="3.6"/><path d="M4.8 20.4c0-3.7 3.2-6.6 7.2-6.6s7.2 2.9 7.2 6.6"/></svg>'
I['chat'] = '<svg viewBox="0 0 24 24"><path d="M20.4 12.6c0 3.7-3.6 6.7-8 6.7-1 0-2-.15-2.9-.43L4 21l1.3-3.5c-1.2-1.2-1.9-2.8-1.9-4.6 0-3.7 3.6-6.7 8-6.7s9 3 9 6.4z"/></svg>'
I['spark'] = '<svg viewBox="0 0 24 24"><path d="M12 2.8l2 5.2 5.2 2-5.2 2-2 5.2-2-5.2-5.2-2 5.2-2z"/><path d="M18.6 15.4l.9 2.3 2.3.9-2.3.9-.9 2.3-.9-2.3-2.3-.9 2.3-.9z"/></svg>'
I['link'] = '<svg viewBox="0 0 24 24"><path d="M10.2 13.8a4 4 0 006 .4l2.4-2.4a4.2 4.2 0 00-6-6l-1.3 1.3"/><path d="M13.8 10.2a4 4 0 00-6-.4L5.4 12.2a4.2 4.2 0 006 6l1.3-1.3"/></svg>'
I['scale'] = '<svg viewBox="0 0 24 24"><path d="M12 3.6v16.8M5 20.4h14"/><path d="M4 9.4h6l-3-4.2zM14 9.4h6l-3-4.2z"/><path d="M4 9.4a3 3 0 006 0M14 9.4a3 3 0 006 0"/></svg>'
I['cal'] = '<svg viewBox="0 0 24 24"><rect x="3.2" y="5" width="17.6" height="15.4" rx="3"/><path d="M8 2.8v4.4M16 2.8v4.4M3.2 10.4h17.6"/><path d="M8 14.2h.01M12 14.2h.01M16 14.2h.01"/></svg>'
I['heart'] = '<svg viewBox="0 0 24 24"><path d="M12 20.4S3.6 15.6 3.6 9.8a4.6 4.6 0 018.4-2.6 4.6 4.6 0 018.4 2.6c0 5.8-8.4 10.6-8.4 10.6z"/></svg>'

I['pin2'] = '<svg viewBox="0 0 512 512" aria-hidden="true"><path d="M256 0C153.755 0 70.573 83.182 70.573 185.426c0 126.888 165.939 313.167 173.004 321.035 6.636 7.391 18.222 7.378 24.846 0 7.065-7.868 173.004-194.147 173.004-321.035C441.425 83.182 358.244 0 256 0m0 278.719c-51.442 0-93.292-41.851-93.292-93.293S204.559 92.134 256 92.134s93.291 41.851 93.291 93.293-41.85 93.292-93.291 93.292"/></svg>'

def ico(k, cls=''):
    s = I[k]
    if cls:
        s = s.replace('<svg ', '<svg class="' + cls + '" ', 1)
    return s

# ---------------------------------------------------------------- педагоги
def sc(*pairs):
    days = ['Пн','Вт','Ср','Чт','Пт','Сб','Вс']
    m = dict(pairs)
    return [{'day': d, 'time': m.get(d, '')} for d in days]

TEACHERS = [
 dict(slug='ilyina', name='Ильина Марина Рэмовна', photo='ilyina.jpg',
      subject='Фортепиано', role='Преподаватель высшей квалификационной категории',
      short='Фортепиано для детей и подростков. 25+ лет практики, мягкий и внимательный подход.',
      bio='Преподаватель высшей квалификационной категории, работает с детьми от 5 лет. Ведёт учеников от первых нот до поступления в профильные учебные заведения.',
      facts=['Преподаватель высшей квалификационной категории','ДМШ им. П. И. Юргенсона','Специалист по детскому обучению','Более 25 лет педагогического опыта'],
      schedule=sc(('Пн','15:00 – 20:00'),('Ср','15:00 – 20:00'),('Пт','14:00 – 19:00'),('Сб','11:00 – 15:00'))),
 dict(slug='katyukova', name='Катюкова Наталья Михайловна', photo='katyukova.jpg',
      subject='Фортепиано · камерный ансамбль · вокальный коучинг', role='Профессор Высшей школы Джульярд (КНР)',
      short='Фортепиано, камерный ансамбль и вокальный коучинг. Носитель английского языка.',
      bio='Профессор, приглашённый коуч молодёжной оперной программы Большого театра. Занятия проходят в очно-заочном формате, в том числе на английском языке.',
      facts=['Профессор Высшей школы Джульярд (КНР)','Приглашённый коуч молодёжной оперной программы ГАБТ','Носитель английского языка','Формат обучения: очно-заочный'],
      schedule=sc(('Вт','16:00 – 20:00'),('Чт','16:00 – 20:00'),('Сб','12:00 – 17:00'))),
 dict(slug='belyakova', name='Белякова Анна Адольфовна', photo='belyakova.jpg',
      subject='Художественные дисциплины', role='Преподаватель и методист художественного отделения',
      short='Рисунок, живопись, композиция. Ведёт художественное отделение центра.',
      bio='Преподаватель и методист художественного отделения. Отмечена высокими государственными и профессиональными наградами.',
      facts=['Высокие государственные и профессиональные награды','Почётные грамоты Министерства культуры РФ','Грамоты Московской городской Думы','Методист художественного отделения'],
      schedule=sc(('Пн','12:00 – 18:00'),('Вт','12:00 – 18:00'),('Чт','14:00 – 19:00'),('Сб','10:00 – 14:00'))),
 dict(slug='speranskaya', name='Сперанская Светлана', photo='speranskaya.jpg',
      subject='Вокал · сценическая речь · актёрское мастерство', role='Актриса театра и кино, педагог по вокалу',
      short='Вокал, сценическая речь и актёрское мастерство. Преподаватель ГИТИСа и ВГИКа.',
      bio='Актриса театра и кино, певица, художественный руководитель и дирижёр хора «Force Мажор».',
      facts=['Актриса театра и кино','Педагог по вокалу, сценической речи и актёрскому мастерству','Художественный руководитель и дирижёр хора «Force Мажор»','Преподаватель ГИТИСа и ВГИКа'],
      schedule=sc(('Ср','15:00 – 20:00'),('Пт','15:00 – 20:00'),('Вс','11:00 – 16:00'))),
 dict(slug='kofanov', name='Кофанов Сергей', photo='kofanov.jpg',
      subject='Вокал', role='Певец, педагог по вокалу',
      short='Постановка голоса, дыхание и работа над репертуаром. Действующий артист сцены.',
      bio='Певец и педагог по вокалу. Совмещает преподавание с концертной деятельностью — поэтому на занятиях много живой практики и работы со сценой.',
      facts=['Певец, педагог по вокалу','Артист Центра исполнительских искусств (бывш. «Градский холл»)','Артист-вокалист Центрального оркестра ФСИН'],
      schedule=sc(('Вт','16:00 – 21:00'),('Чт','16:00 – 21:00'),('Сб','12:00 – 18:00'))),
 dict(slug='goncharov', name='Гончаров Серафим', photo='goncharov.jpg',
      subject='Скрипка · раннее музыкальное развитие', role='Выпускник Московской консерватории им. П. И. Чайковского',
      short='Скрипка и музыкальное развитие малышей от 3 лет. Автор книги «Горизонты детства».',
      bio='Преподаватель по классу скрипки, ведёт группы раннего музыкального развития для детей с 3 до 7 лет. Урок построен на играх — ребёнок не устаёт и ждёт следующего занятия.',
      facts=['Выпускник Московской консерватории им. П. И. Чайковского','Преподаватель по классу скрипки','Детское музыкальное развитие, группы с 3 до 7 лет','Автор книги «Горизонты детства»'],
      schedule=sc(('Пн','10:00 – 14:00'),('Вт','15:00 – 20:00'),('Чт','15:00 – 20:00'),('Сб','10:00 – 15:00'))),
 dict(slug='zhukovskaya', name='Жуковская Екатерина', photo='zhukovskaya.jpg',
      subject='Сольфеджио · теория и история музыки', role='Преподаватель ГМПИ им. М. М. Ипполитова-Иванова',
      short='Сольфеджио, теория и история музыки. Комплексные музыкальные занятия для детей.',
      bio='Высшее музыкально-педагогическое образование. Ведёт общеразвивающие комплексные музыкальные занятия с детьми.',
      facts=['Преподаватель ГМПИ им. М. М. Ипполитова-Иванова','Высшее музыкально-педагогическое образование','Педагог по сольфеджио и теории музыки','Комплексные музыкальные занятия с детьми'],
      schedule=sc(('Вт','14:00 – 19:00'),('Чт','14:00 – 19:00'),('Сб','11:00 – 16:00'))),
 dict(slug='tyuteykin', name='Тютейкин Сергей Александрович', photo='tyuteykin.jpg',
      subject='Труба · камерный ансамбль', role='Профессор Высшей школы Джульярд (КНР)',
      short='Труба, камерный ансамбль, работа над оркестровыми трудностями.',
      bio='Профессор международного уровня. Занятия проходят в очно-заочном формате, в том числе на английском языке.',
      facts=['Профессор Высшей школы Джульярд (КНР)','Носитель английского языка','Формат обучения: очно-заочный','Оркестровые трудности, камерный ансамбль'],
      schedule=sc(('Ср','16:00 – 20:00'),('Пт','16:00 – 20:00'))),
 dict(slug='sergeeva', name='Сергеева Полина', photo='sergeeva.jpg',
      subject='Фортепиано', role='Лауреат международных конкурсов',
      short='Фортепиано для начинающих и продолжающих. Студентка РАМ им. Гнесиных.',
      bio='Лауреат международных конкурсов, студентка Российской академии музыки имени Гнесиных.',
      facts=['Лауреат международных конкурсов','Студентка РАМ имени Гнесиных','Работа с начинающими и продолжающими учениками'],
      schedule=sc(('Пн','16:00 – 20:00'),('Ср','16:00 – 20:00'),('Вс','12:00 – 17:00'))),
]

GUESTS = [
 dict(slug='maklygin', name='Маклыгин Александр Львович', photo='maklygin.jpg',
      role='Доктор искусствоведения, профессор',
      facts=['Заслуженный работник Высшей школы России','Заслуженный деятель искусств Республики Татарстан и Республики Марий Эл','Председатель Общества теории музыки России','Профессор кафедры «Теория музыки» Московской консерватории им. П. И. Чайковского'],
      video='https://rutube.ru/play/embed/1522631533be108e914109d0a930b5a4/'),
 dict(slug='nosina', name='Носина Вера Борисовна', photo='nosina.jpg',
      role='Профессор РАМ им. Гнесиных',
      facts=['Профессор кафедры специального фортепиано Российской академии музыки им. Гнесиных','Заслуженный деятель искусств РФ','Заслуженный деятель культуры Киргизии'],
      video='https://rutube.ru/play/embed/969eddf5bbf5992d5650c366af9887d1/'),
 dict(slug='maslyuk', name='Маслюк Константин Александрович', photo='maslyuk.jpg',
      role='Профессор Московской консерватории',
      facts=['Лауреат международных конкурсов','Профессор','Заведующий кафедрой камерного ансамбля и квартета Московской консерватории им. П. И. Чайковского'],
      video='https://rutube.ru/play/embed/a2b7f8b7234a900399a5af701279aa3a/'),
]

REVIEWS = [
 dict(name='Валентина', role='мама ученика', text=[
  'Есть места, куда приходишь — и душа поёт. Школа MusicArtPlus — именно такое.',
  'Здесь работают педагоги космического уровня: не просто виртуозы своего дела, но люди с большим сердцем. Они не «ставят» ученика — они раскрывают его. Мой сын бежит на занятия с горящими глазами, а возвращается — с новой искрой во взгляде.',
  'Это настоящая школа мечты. Где каждый ребёнок находит свою мелодию и гармонию с собой, а каждый родитель обретает спокойствие: «Мой ребёнок в надёжных руках». Спасибо вам за ваш труд!'], long=True),
 dict(name='Мария', role='мама учеников', text=[
  'Марина Рэмовна — не просто учитель, она наставник. Она стала для моих детей тем самым значимым взрослым, которого так важно иметь подростку на сложном пути взросления.',
  'Через занятия музыкой детям не только открывался высокий мир культуры, они также учились ответственности, трудолюбию, умению рисковать и сопереживать. Очень ценно найти такого педагога.']),
 dict(name='Ирина Владимировна', role='мама ученицы', text=[
  'Хочется выразить слова огромной благодарности и признательности Беляковой Анне Адольфовне! Анна Адольфовна — удивительный педагог и профессионал своего дела.',
  'За время обучения показала себя как человек, умеющий найти подход к каждому ребёнку, развить его творческие способности и привить любовь к музыке.']),
 dict(name='Алёна', role='выпускница', text=[
  'Обучение у Марины Рэмовны Ильиной для меня было не просто освоением фортепиано. Она смогла развить во мне любовь к звуку, научила мыслить во время игры, добиваться поставленных задач и ярко проявлять себя во время выступлений.',
  'В классе всегда царила тёплая атмосфера, там хотелось придумывать что-то новое, творить. В итоге музыка стала неотъемлемой частью моей жизни.']),
 dict(name='Анна', role='мама ученика, 5 лет', text=[
  'Серафим занимается с сыном. Ребёнку пять лет, поэтому занятия не из простых: дети быстро теряют внимание. Серафим придумывает множество игр, ребёнок всегда с радостью ждёт урока.',
  'Восхищаюсь терпением Серафима: он готов множество раз повторить, вежливо поправить ошибку, поддержать при сложностях. Ребёнок радуется, что его успехи замечают.'], long=True),
 dict(name='Лилия Олеговна Ермакова', role='мама пианиста', text=[
  'Константин Александрович Маслюк — ВОСТОРГ! Человек, сочетающий в себе невероятную харизматичность и скромность, глубину мысли и простоту её изложения, доброжелательность и требовательность по отношению к ученику.',
  'Ум, интеллигентность, лёгкость, способность вдохновить ребёнка.']),
 dict(name='Яна', role='ученица', text=[
  'Марина Рэмовна — удивительно чуткий и профессиональный педагог. Она не просто работает над пианистической техникой, она работает личностно, не по шаблону, воспитывая в учениках всё самое светлое и важное.',
  'Огромный поклон и благодарность такому мастеру!']),
]

NEWS = [
 dict(slug='camp', d='12', m='августа', y='2026', tag='Жизнь центра', img='g15.jpg',
      title='Летний музыкальный лагерь MusicArtPlus',
      text='Две недели музыки, живописи и театра: ансамбли, пленэры, вечерние концерты и новые друзья.'),
 dict(slug='concert', d='25', m='мая', y='2026', tag='Концерты', img='g07.jpg',
      title='Отчётный концерт учеников центра',
      text='Наши ученики впервые вышли на большую сцену — от первых пьес до концертных программ.'),
 dict(slug='master', d='18', m='апреля', y='2026', tag='Мастер-классы', img='g11.jpg',
      title='Мастер-класс профессора А. Л. Маклыгина',
      text='Открытый мастер-класс по теории музыки и импровизации для учеников и родителей.'),
 dict(slug='open', d='06', m='апреля', y='2026', tag='Жизнь центра', img='g04.jpg',
      title='Открытый урок раннего музыкального развития',
      text='Показали родителям, как проходит занятие с малышами 3–7 лет: игры, ритмика, первые ноты.'),
 dict(slug='winners', d='22', m='марта', y='2026', tag='Достижения', img='g03.jpg',
      title='Наши ученики — лауреаты международного конкурса',
      text='Поздравляем ребят и педагогов с блестящим выступлением и заслуженными наградами.'),
 dict(slug='art', d='14', m='февраля', y='2026', tag='Жизнь центра', img='g02.jpg',
      title='Художественное отделение: открыт набор',
      text='Рисунок, живопись и композиция для детей от 5 лет под руководством А. А. Беляковой.'),
 dict(slug='bach', d='30', m='января', y='2026', tag='Мастер-классы', img='g09.jpg',
      title='Концерт-беседа В. Б. Носиной о музыке Баха',
      text='Профессор РАМ им. Гнесиных рассказала о символике баховских произведений.'),
 dict(slug='newyear', d='24', m='декабря', y='2025', tag='Концерты', img='g01.jpg',
      title='Новогодний концерт и творческая ёлка',
      text='Музыка, спектакль и выставка детских работ — праздник, который придумали сами ученики.'),
]

# ---------------------------------------------------------------- шаблоны
NAV = [('index.html','home','Главная'),('about.html','about','О нас'),
       ('directions.html','directions','Наши направления'),
       ('teachers.html','teachers','Педагоги'),('news.html','news','Новости')]

def head(title, desc, page, hero='light', swiper=False):
    return '''<!DOCTYPE html>
<html lang="ru" class="no-js">
<head>
<script>document.documentElement.className="js"</script>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>''' + title + '''</title>
<meta name="description" content="''' + desc + '''">
<meta name="theme-color" content="#F3B71E">
<link rel="icon" href="assets/img/ui/favicon.png" type="image/png">
<meta property="og:type" content="website">
<meta property="og:title" content="''' + title + '''">
<meta property="og:description" content="''' + desc + '''">
<meta property="og:image" content="assets/img/ui/logo-color.png">
<meta property="og:locale" content="ru_RU">
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Ysabeau:ital,wght@0,300..800;1,300..600&family=Ysabeau+SC:wght@500;600;700&display=swap" rel="stylesheet">
<link rel="stylesheet" href="assets/css/style.css">''' + ('''
<link rel="stylesheet" href="assets/vendor/swiper/swiper-bundle.min.css">''' if swiper else '') + '''
</head>
<body data-page="''' + page + '''" data-hero="''' + hero + '''">
<div class="preloader" aria-hidden="true">
  <img src="assets/img/ui/logo-color.png" alt="">
  <span class="preloader__bar"></span>
</div>
'''

def socials(cls=''):
    return ('<div class="socials ' + cls + '">'
      '<a href="' + TG + '" target="_blank" rel="noopener" aria-label="Telegram">' + I['tg'] + '</a>'
      '<a href="' + IG + '" target="_blank" rel="noopener" aria-label="Instagram">' + I['ig'] + '</a>'
      '<a href="' + RT + '" target="_blank" rel="noopener" aria-label="Rutube">' + I['rt'] + '</a>'
      '</div>')

def header(page):
    nav = ''.join('<a class="nav__link" data-nav="' + s + '" href="' + h + '">' + t + '</a>' for h, s, t in NAV)
    mnav = ''.join('<a data-nav="' + s + '" href="' + h + '">' + t + ico('ar') + '</a>' for h, s, t in NAV)
    return '''
<header class="header">
  <div class="container header__inner">
    <a class="logo" href="index.html" aria-label="MusicArtPlus — на главную">
      <img class="logo__dark" src="assets/img/ui/logo-color.png" alt="MusicArtPlus — центр искусств" width="176" height="103">
      <img class="logo__light" src="assets/img/ui/logo-white.png" alt="" aria-hidden="true" width="176" height="103">
    </a>
    <nav class="nav" aria-label="Основная навигация">''' + nav + '''</nav>
    <div class="header__side">
      <a class="header__phone" href="tel:''' + PHONE_HREF + '''">''' + PHONE + '''</a>
      ''' + socials() + '''
      <a class="btn btn--gold btn--sm" data-crm="true" href="#">Записаться</a>
      <button class="burger" type="button" aria-label="Открыть меню" aria-expanded="false" aria-controls="mobile-menu">
        <span></span><span></span><span></span>
      </button>
    </div>
  </div>
</header>

<div class="mobile-menu" id="mobile-menu">
  <nav class="mobile-menu__nav" aria-label="Мобильная навигация">''' + mnav + '''</nav>
  <div class="mobile-menu__foot">
    <a class="btn btn--gold btn--block" data-crm="true" href="#">Записаться на пробный урок</a>
    <a class="header__phone" href="tel:''' + PHONE_HREF + '''" style="font-size:22px;display:block">''' + PHONE + '''</a>
    <p class="muted" style="font-size:15px">''' + ADDR + '''</p>
    ''' + socials() + '''
  </div>
</div>
'''

FOOTER = '''
<footer class="footer">
  <div class="footer__deco" aria-hidden="true">
    <svg viewBox="0 0 300 300"><circle cx="60" cy="230" r="34"/><circle cx="200" cy="196" r="34"/><path d="M94 230V60l140-28v164"/><path d="M94 96l140-28"/></svg>
  </div>
  <div class="container">
    <div class="footer__grid">
      <div>
        <img class="footer__logo" src="assets/img/ui/logo-white.png" alt="MusicArtPlus" width="176" height="103">
        <p>Центр искусств для детей и взрослых в Москве. Музыка, живопись и сцена — в атмосфере, где хочется творить.</p>
        <a class="footer__fund" href="''' + FUND + '''" target="_blank" rel="noopener">
          <img src="assets/img/ui/forteforma.svg" alt="Фонд ФОРТЕФОРМА" width="45" height="34">
          <span>При поддержке фонда <b>ФОРТЕФОРМА</b></span>
        </a>
      </div>
      <div>
        <h4>Разделы</h4>
        <div class="footer__links">
          <a href="index.html">Главная</a>
          <a href="about.html">О нас</a>
          <a href="directions.html">Наши направления</a>
          <a href="teachers.html">Педагоги</a>
          <a href="news.html">Новости</a>
        </div>
      </div>
      <div>
        <h4>Направления</h4>
        <div class="footer__links">
          <a href="directions.html#dir-piano">Фортепиано</a>
          <a href="directions.html#dir-violin">Скрипка</a>
          <a href="directions.html#dir-vocal">Вокал и сцена</a>
          <a href="directions.html#dir-art">Изобразительное искусство</a>
          <a href="directions.html#dir-early">Раннее развитие 3–7 лет</a>
        </div>
      </div>
      <div>
        <h4>Контакты</h4>
        <div class="footer__contact">
          <span>''' + ADDR + '''<br><small style="opacity:.7">вход со стороны запасного входа</small></span>
          <a href="tel:''' + PHONE_HREF + '''">''' + PHONE + '''</a>
          <a href="mailto:''' + MAIL + '''">''' + MAIL + '''</a>
        </div>
        <div style="margin-top:18px">''' + socials('socials--footer') + '''</div>
      </div>
    </div>
    <div class="footer__bottom">
      <span>© <span data-year>2026</span> MusicArtPlus. Все права защищены.</span>
      <span><a href="#">Политика конфиденциальности</a> · <a href="#">Согласие на обработку данных</a></span>
    </div>
  </div>
</footer>

<a class="btn btn--gold fab" data-crm="true" href="#">Записаться на пробный урок</a>
'''

DIRECTION_OPTIONS = ['Фортепиано','Скрипка','Труба и духовые','Вокал и сценическая речь',
 'Актёрское мастерство','Изобразительное искусство','Сольфеджио и теория музыки',
 'Раннее музыкальное развитие (3–7 лет)','Подготовка к поступлению','Ещё не выбрали — подскажите']

MODALS = '''
<div class="modal" id="booking-modal" role="dialog" aria-modal="true" aria-labelledby="bk-title">
  <div class="modal__backdrop"></div>
  <div class="modal__box modal__box--form">
    <button class="modal__close" type="button" data-close aria-label="Закрыть">''' + I['close'] + '''</button>
    <div class="bk">
      <aside class="bk__aside">
        <img src="assets/img/ui/logo-color.png" alt="MusicArtPlus">
        <h4>Первый урок — чтобы просто попробовать</h4>
        <p>Знакомство с педагогом и инструментом. Ни к чему не обязывает.</p>
        <ul class="about-list">
          <li><span class="tick">''' + ico('check') + '''</span><span>Подберём педагога под возраст и характер</span></li>
          <li><span class="tick">''' + ico('check') + '''</span><span>Заниматься можно на инструментах центра</span></li>
          <li><span class="tick">''' + ico('check') + '''</span><span>Очно у метро Минская или онлайн</span></li>
        </ul>
        <div class="bk__phone">
          <span>Или позвоните</span>
          <a href="tel:''' + PHONE_HREF + '''">''' + PHONE + '''</a>
        </div>
      </aside>
      <div class="bk__main">
        <h3 class="bk__title" id="bk-title">Записаться на пробный урок</h3>
        <p class="bk__sub">Заполните два поля — остальное уточним по телефону.</p>

        <div class="bk__ctx" data-bk-ctx>
          <img alt="" data-bk-photo>
          <div><span data-bk-label>Педагог</span><b data-bk-name></b></div>
        </div>

        <form class="form" data-form id="form-booking" novalidate>
          <input type="hidden" name="teacher" data-bk-input>
          <div class="field">
            <label for="bk-name">Как вас зовут</label>
            <input id="bk-name" name="name" type="text" placeholder="Имя" required autocomplete="name">
            <span class="field__err">Пожалуйста, укажите имя</span>
          </div>
          <div class="field">
            <label for="bk-tel">Телефон</label>
            <input id="bk-tel" name="phone" type="tel" placeholder="+7 (___) ___-__-__" required autocomplete="tel">
            <span class="field__err">Укажите телефон полностью</span>
          </div>
          <div class="field">
            <label for="bk-dir">Направление</label>
            <select id="bk-dir" name="direction" data-bk-dir>''' + ''.join(
              '<option value="' + d + '">' + d + '</option>' for d in DIRECTION_OPTIONS) + '''</select>
          </div>
          <label class="check">
            <input type="checkbox" required>
            <span>Я согласен(-на) на обработку персональных данных и принимаю <a href="#">политику конфиденциальности</a></span>
          </label>
          <button class="btn btn--gold btn--block btn--lg" type="submit" data-label="Отправить заявку">Отправить заявку</button>
          <div class="form__ok">Спасибо! Заявка принята — мы перезвоним в ближайшее рабочее время.</div>
          <p class="form__note">Или выберите время сами в системе «Мой класс» —
            <a data-bk-crm href="#" target="_blank" rel="noopener" style="color:var(--gold-dark);font-weight:600">открыть расписание</a></p>
        </form>
      </div>
    </div>
  </div>
</div>

<div class="modal" id="teacher-modal" role="dialog" aria-modal="true" aria-label="Карточка педагога">
  <div class="modal__backdrop"></div>
  <div class="modal__box">
    <button class="modal__close" type="button" data-close aria-label="Закрыть">''' + I['close'] + '''</button>
    <div data-teacher-slot></div>
  </div>
</div>

<div class="modal" id="video-modal" role="dialog" aria-modal="true" aria-label="Видео">
  <div class="modal__backdrop"></div>
  <div class="modal__box modal__box--video">
    <button class="modal__close" type="button" data-close aria-label="Закрыть">''' + I['close'] + '''</button>
    <div class="modal__video"></div>
    <div class="v-foot" style="display:none"></div>
  </div>
</div>

<script src="assets/js/main.js" defer></script>
</body>
</html>
'''

def modals(swiper=False):
    lib = '<script src="assets/vendor/swiper/swiper-bundle.min.js" defer></script>\n' if swiper else ''
    return MODALS.replace('<script src="assets/js/main.js" defer></script>',
                          lib + '<script src="assets/js/main.js" defer></script>')

def page(fname, title, desc, slug, body, hero='light', swiper=False):
    html = (head(title, desc, slug, hero, swiper) + header(slug) +
            '<main>' + body + '</main>' + FOOTER + modals(swiper))
    io.open(os.path.join(OUT, fname), 'w', encoding='utf-8').write(html)
    return fname

# ---------------------------------------------------------------- компоненты
def esc(s):
    return (str(s).replace('&','&amp;').replace('<','&lt;').replace('>','&gt;')
            .replace('"','&quot;').replace("'",'&#39;'))

def jattr(v):
    return json.dumps(v, ensure_ascii=False).replace("'", '&#39;')

def teacher_card(t, delay=0, reveal=True):
    return ('<article class="teacher' + (' reveal' if reveal else '') + '" data-delay="' + str(delay) + '"'
      ' data-teacher="' + t['slug'] + '"'
      ' data-name="' + esc(t['name']) + '"'
      ' data-role="' + esc(t['role']) + '"'
      ' data-subject="' + esc(t['subject']) + '"'
      ' data-photo="assets/img/teachers/' + t['photo'] + '"'
      ' data-bio="' + esc(t['bio']) + '"'
      " data-facts='" + jattr(t['facts']) + "'"
      " data-schedule='" + jattr(t['schedule']) + "'>"
      '<button class="teacher__ava" type="button" data-teacher-open aria-label="Подробнее: ' + esc(t['name']) + '">'
        '<img src="assets/img/teachers/' + t['photo'] + '" alt="' + esc(t['name']) + '" loading="lazy" width="330" height="330">'
      '</button>'
      '<h3 class="teacher__name">' + t['name'] + '</h3>'
      '<div class="teacher__role">' + t['subject'] + '</div>'
      '<p class="teacher__desc">' + t['short'] + '</p>'
      '<div class="teacher__actions">'
        '<a class="btn btn--gold btn--sm" data-crm="' + t['slug'] + '" href="#">Записаться</a>'
        '<button class="btn btn--ghost btn--sm" type="button" data-teacher-open>Подробнее</button>'
      '</div>'
      '</article>')

def guest_card(g, delay=0, reveal=True):
    facts = ''.join('<li>' + f + '</li>' for f in g['facts'])
    return ('<article class="teacher teacher--guest' + (' reveal' if reveal else '') + '" data-delay="' + str(delay) + '">'
      '<div class="teacher__ava"><img src="assets/img/guests/' + g['photo'] + '" alt="' + esc(g['name']) + '" loading="lazy" width="330" height="330"></div>'
      '<h3 class="teacher__name">' + g['name'] + '</h3>'
      '<div class="teacher__role">' + g['role'] + '</div>'
      '<ul class="tm__list" style="text-align:left;margin-top:14px">' + facts + '</ul>'
      '</article>')

def review_card(r, delay=0, reveal=True):
    cls = 'review' + (' reveal' if reveal else '') + (' review--clamped' if r.get('long') else '')
    body = ''.join('<p>' + p + '</p>' for p in r['text'])
    more = '<button class="review__more" type="button">Читать полностью</button>' if r.get('long') else ''
    return ('<article class="' + cls + '" data-delay="' + str(delay) + '">'
      '<span class="review__quote" aria-hidden="true">&ldquo;</span>'
      '<div class="review__stars" aria-label="Оценка 5 из 5">' + ico('star') * 5 + '</div>'
      '<div class="review__text">' + body + '</div>' + more +
      '<div class="review__author"><span class="review__ava">' + r['name'][0] + '</span>'
      '<div><b>' + r['name'] + '</b><span>' + r['role'] + '</span></div></div>'
      '</article>')

def news_card(n, delay=0, reveal=True):
    return ('<a class="card' + (' reveal' if reveal else '') + '" data-delay="' + str(delay) + '" data-news-item data-tag="' + n['tag'] + '" href="news-' + n['slug'] + '.html">'
      '<div class="card__media">'
        '<div class="card__date"><b>' + n['d'] + '</b><span>' + n['m'][:3] + '</span></div>'
        '<img src="assets/img/gallery/' + n['img'] + '" alt="' + esc(n['title']) + '" loading="lazy" width="640" height="440">'
      '</div>'
      '<div class="card__body">'
        '<span class="chip chip--outline">' + n['tag'] + '</span>'
        '<h3 class="card__title">' + n['title'] + '</h3>'
        '<p class="card__text">' + n['text'] + '</p>'
        '<div class="card__foot"><span class="link-arrow">Читать' + ico('ar') + '</span></div>'
      '</div></a>')

def slider(cards, name, preset='cards', grid=''):
    slides = ''.join('<div class="swiper-slide">' + c + '</div>' for c in cards)
    cls = 'slider' + ((' ' + grid) if grid else '')
    return ('<div class="' + cls + '" data-swiper="' + name + '" data-swiper-preset="' + preset + '">'
      '<div class="swiper"><div class="swiper-wrapper">' + slides + '</div></div>'
      '<div class="swiper-pagination"></div>'
      '</div>')

def slider_nav(name):
    return ('<div class="slider__nav" data-swiper-nav="' + name + '">'
      '<button class="c-arrow" type="button" data-c-prev aria-label="Назад">' + I['al'] + '</button>'
      '<button class="c-arrow" type="button" data-c-next aria-label="Вперёд">' + I['ar'] + '</button>'
      '</div>')

INTERLUDE_NOTES = '''
<div class="interlude">
  <div class="interlude__line draw draw--slow" aria-hidden="true">
    <svg viewBox="0 0 1000 140" preserveAspectRatio="xMidYMid meet">
      <path d="M8 84C120 40 214 118 322 84c108-34 190 34 300 6 92-24 190 30 370-24"/>
      <path d="M322 84V30"/>
      <ellipse cx="311" cy="86" rx="13" ry="9.5" transform="rotate(-18 311 86)"/>
      <path d="M322 30c14 4 26 10 32 20"/>
      <path d="M622 90V34"/>
      <ellipse cx="611" cy="92" rx="13" ry="9.5" transform="rotate(-18 611 92)"/>
      <path d="M686 74V22"/>
      <ellipse cx="675" cy="76" rx="13" ry="9.5" transform="rotate(-18 675 76)"/>
      <path d="M622 34h64"/>
      <path d="M866 62V16c14 4 26 12 30 24"/>
      <ellipse cx="855" cy="64" rx="13" ry="9.5" transform="rotate(-18 855 64)"/>
    </svg>
  </div>
  <p class="interlude__caption">каждый урок начинается с первой ноты</p>
</div>
'''

INTERLUDE_BRUSH = '''
<div class="interlude interlude--cream">
  <div class="container interlude__narrow">
    <div class="reveal" style="display:grid;grid-template-columns:minmax(0,1fr) minmax(0,1fr);gap:clamp(20px,4vw,50px);align-items:center">
      <div style="border-radius:var(--r-lg);overflow:hidden;box-shadow:var(--sh-2);background:#fff">
        <video data-autoloop muted loop playsinline preload="none" src="assets/video/brush.mp4"
               poster="assets/video/brush-poster.jpg" width="640" height="640"
               aria-label="Детская рука выводит кистью надпись «Музыка Арт Плюс»"></video>
      </div>
      <div>
        <span class="eyebrow">Наш почерк</span>
        <h3 class="h3">Творчество начинается с первого движения руки</h3>
        <p class="muted" style="margin-top:14px">Кисть, смычок или клавиша — мы верим, что первое прикосновение к искусству должно быть радостным. С него начинается всё остальное.</p>
      </div>
    </div>
  </div>
</div>
'''

def form_block(title, subtitle, btn='Записаться на пробный урок', ident='form-main'):
    return '''
<div class="form-card reveal reveal--right">
  <h3 class="h3">''' + title + '''</h3>
  <p class="muted" style="margin-top:10px;font-size:16px">''' + subtitle + '''</p>
  <form class="form" data-form id="''' + ident + '''" novalidate style="margin-top:24px">
    <div class="field">
      <label for="''' + ident + '''-name">Как вас зовут</label>
      <input id="''' + ident + '''-name" name="name" type="text" placeholder="Имя" required autocomplete="name">
      <span class="field__err">Пожалуйста, укажите имя</span>
    </div>
    <div class="field">
      <label for="''' + ident + '''-tel">Телефон</label>
      <input id="''' + ident + '''-tel" name="phone" type="tel" placeholder="+7 (___) ___-__-__" required autocomplete="tel">
      <span class="field__err">Укажите телефон полностью</span>
    </div>
    <label class="check">
      <input type="checkbox" required>
      <span>Я согласен(-на) на обработку персональных данных и принимаю <a href="#">политику конфиденциальности</a></span>
    </label>
    <button class="btn btn--gold btn--block btn--lg" type="submit" data-label="''' + btn + '''">''' + btn + '''</button>
    <div class="form__ok">Спасибо! Заявка принята — мы перезвоним в ближайшее рабочее время.</div>
    <p class="form__note">Или запишитесь сами в системе «Мой класс» — <a data-crm="true" href="#" style="color:var(--gold-dark);font-weight:600">открыть расписание</a></p>
  </form>
</div>'''

MAP_BLOCK = '''
<section class="section section--cream section--map" id="contacts">
  <span class="deco deco--tr draw" aria-hidden="true">
    <svg viewBox="0 0 200 200"><path d="M120 12c-30 22-44 52-26 76 18 24 58 18 62-8 4-26-24-40-46-26-22 14-20 50 4 66"/><circle cx="60" cy="150" r="16"/><path d="M76 150V54"/></svg>
  </span>
  <div class="container">
    <div class="sec-head sec-head--center">
      <div class="sec-head__text">
        <span class="eyebrow">Как нас найти</span>
        <h2 class="h2">Мы рядом с метро&nbsp;Минская</h2>
        <p class="sec-head__desc">Центр находится на закрытой территории — перед первым визитом позвоните, и мы встретим вас у входа.</p>
      </div>
    </div>
    <div class="contact-info contact-info--row">
      <div class="ci reveal"><span class="ci__ico">''' + I['pin'] + '''</span>
        <div><b>Адрес</b><span>''' + ADDR + '''</span>
        <small>Вход не через главный, а через запасной вход</small></div></div>
      <div class="ci reveal" data-delay="1"><span class="ci__ico">''' + I['phone'] + '''</span>
        <div><b>Телефон</b><a href="tel:''' + PHONE_HREF + '''">''' + PHONE + '''</a>
        <small>Пн–Вс, 10:00–20:00</small></div></div>
      <div class="ci reveal" data-delay="2"><span class="ci__ico">''' + I['mail'] + '''</span>
        <div><b>Почта</b><a href="mailto:''' + MAIL + '''">''' + MAIL + '''</a></div></div>
      <div class="ci reveal" data-delay="3"><span class="ci__ico">''' + I['clock'] + '''</span>
        <div><b>Занятия</b><span>Очно в центре и онлайн</span>
        <small>Некоторые педагоги проводят занятия на дому</small></div></div>
    </div>
  </div>
  <div class="map-wrap map-wrap--full reveal">
    <iframe src="https://yandex.ru/map-widget/v1/?text=%D0%9C%D0%BE%D1%81%D0%BA%D0%B2%D0%B0%2C%20%D1%83%D0%BB%D0%B8%D1%86%D0%B0%20%D0%A3%D0%BB%D0%BE%D1%84%D0%B0%20%D0%9F%D0%B0%D0%BB%D1%8C%D0%BC%D0%B5%2C%205&amp;z=17"
            title="MusicArtPlus на карте: ул. Улофа Пальме, д. 5" loading="lazy" allowfullscreen></iframe>
  </div>
</section>'''

def cta_band(title, text, primary='Записаться на пробный урок'):
    return '''
<section class="section section--tight">
  <div class="container">
    <div class="cta-band reveal reveal--scale">
      <span class="cta-band__glow" aria-hidden="true"></span>
      <div>
        <h2 class="h2">''' + title + '''</h2>
        <p>''' + text + '''</p>
      </div>
      <div class="cta-band__actions">
        <a class="btn btn--gold btn--lg" data-crm="true" href="#">''' + primary + '''</a>
        <a class="btn btn--light btn--lg" href="tel:''' + PHONE_HREF + '''">''' + PHONE + '''</a>
      </div>
    </div>
  </div>
</section>'''

def gallery_block(imgs, cls='gallery'):
    figs = ''.join('<figure><img src="assets/img/gallery/' + g + '" alt="Занятия в MusicArtPlus" loading="lazy"></figure>' for g in imgs)
    return '<div class="' + cls + '">' + figs + '</div>'

# ================================================================ ГЛАВНАЯ
DECO_NOTE = ('<span class="deco deco--bl draw" aria-hidden="true">'
  '<svg viewBox="0 0 200 200"><circle cx="42" cy="150" r="15"/><circle cx="120" cy="132" r="15"/>'
  '<path d="M57 150V44l78-16v104"/><path d="M57 76l78-16"/></svg></span>')
DECO_STAR = ('<span class="deco deco--tl deco--gold draw" aria-hidden="true">'
  '<svg viewBox="0 0 200 200"><path d="M100 20l16 46 46 16-46 16-16 46-16-46-46-16 46-16z"/>'
  '<path d="M158 118l7 20 20 7-20 7-7 20-7-20-20-7 20-7z"/></svg></span>')
DECO_BRUSH = ('<span class="deco deco--tr draw" aria-hidden="true">'
  '<svg viewBox="0 0 220 140"><path d="M8 96C60 46 108 118 152 74c26-26 44-8 60 2"/>'
  '<path d="M28 118c40-30 78 22 116-10"/></svg></span>')

HERO_SLIDES = [('g07.jpg','Урок фортепиано в MusicArtPlus'),
               ('g11.jpg','Занятие по скрипке с ребёнком'),
               ('g02.jpg','Урок живописи у мольберта'),
               ('g15.jpg','Открытый урок: труба')]

slides = ''
for i, (img, alt) in enumerate(HERO_SLIDES):
    slides += ('<div class="hero__slide' + (' is-active' if i == 0 else '') + '">'
               '<img src="assets/img/gallery/' + img + '" alt="' + alt + '" '
               + ('fetchpriority="high"' if i == 0 else 'loading="lazy"') + ' width="1400" height="900"></div>')

FACTS = [('12','педагогов и приглашённых артистов'),('8','направлений обучения'),
         ('3+','возраст первых занятий, лет'),('∞','взрослые любого возраста и подготовки')]
facts_html = ''.join('<div class="hero-fact"><div class="hero-fact__num">' + n + '</div>'
                     '<div class="hero-fact__label">' + l + '</div></div>' for n, l in FACTS)

DIRS_HOME = [
 ('piano','Фортепиано','5+ лет'), ('violin','Скрипка','5+ лет'),
 ('trumpet','Труба и духовые','8+ лет'), ('mic','Вокал и сцен. речь','6+ лет'),
 ('masks','Актёрское мастерство','6+ лет'), ('palette','Изобразительное искусство','5+ лет'),
 ('note','Сольфеджио и теория','6+ лет'), ('child','Раннее развитие','3–7 лет'),
]
dirs_html = ''
for i, (icn, t, age) in enumerate(DIRS_HOME):
    dirs_html += ('<a class="dir-mini reveal" data-delay="' + str(i % 4) + '" href="directions.html">'
      '<span class="dir-mini__ico">' + I[icn] + '</span>'
      '<span class="dir-mini__body"><b>' + t + '</b><span>' + age + '</span></span></a>')

VIDEOS_HOME = [
 ('https://rutube.ru/play/embed/1522631533be108e914109d0a930b5a4/','iframe','assets/img/guests/maklygin.jpg',
  'Мастер-класс А. Л. Маклыгина','Открытое занятие в центре'),
 ('https://rutube.ru/play/embed/969eddf5bbf5992d5650c366af9887d1/','iframe','assets/img/guests/nosina.jpg',
  'Творческая встреча с В. Б. Носиной','Профессор РАМ им. Гнесиных'),
 ('https://rutube.ru/play/embed/a2b7f8b7234a900399a5af701279aa3a/','iframe','assets/img/guests/maslyuk.jpg',
  'Встреча с К. А. Маслюком','Профессор Московской консерватории'),
]
vid_html = ''
for i, (src, typ, poster, t, sub) in enumerate(VIDEOS_HOME):
    vid_html += ('<button class="video-card reveal" data-delay="' + str(i) + '" type="button" '
      'data-video="' + src + '" data-video-type="' + typ + '"'
      + (' data-video-page="' + src.replace('/play/embed/', '/video/') + '"' if typ == 'iframe' else '')
      + ' aria-label="Смотреть: ' + t + '">'
      '<img src="' + poster + '" alt="" loading="lazy" width="800" height="500">'
      '<span class="video-card__play">' + I['play'] + '</span>'
      '<span class="video-card__cap"><b>' + t + '</b><span>' + sub + '</span></span></button>')

INDEX = '''
<section class="hero">
  <div class="hero__media">''' + slides + '''</div>
  <div class="container hero__inner">
    <div class="hero__content">
      <span class="hero__eyebrow">''' + I['pin2'] + '''<span class="he-wide">Центр искусств · </span>Москва, м.&nbsp;Минская</span>
      <h1 class="hero__title" data-reveal-title>Место, где искра творчества зажигает звёзды</h1>
      <p class="hero__sub">Музыка, живопись и сцена для детей от 3 лет и взрослых. Сильная академическая база — в тёплой, живой атмосфере.</p>
      <div class="hero__actions">
        <a class="btn btn--gold btn--lg" data-crm="true" href="#">Записаться на пробный урок</a>
        <a class="btn btn--light btn--lg" href="#directions">Наши направления</a>
      </div>
    </div>
    <div class="hero__bottom">
      <div class="hero__facts">''' + facts_html + '''</div>
      <div class="hero__dots" role="tablist" aria-label="Слайды"></div>
    </div>
  </div>
</section>

<!-- ===== Блок 2. О нас ===== -->
<section class="section" id="about">
  <span class="deco deco--tr draw" aria-hidden="true">
    <svg viewBox="0 0 200 200"><path d="M120 12c-30 22-44 52-26 76 18 24 58 18 62-8 4-26-24-40-46-26-22 14-20 50 4 66"/><circle cx="60" cy="150" r="16"/><path d="M76 150V54"/></svg>
  </span>
  <div class="container">
    <div class="about-split">
      <div class="about-visual reveal reveal--left">
        <span class="about-visual__ringbox" aria-hidden="true"><span class="about-visual__ring"></span></span>
        <div class="about-visual__circle">
          <img src="assets/img/gallery/g15.jpg" alt="Занятие в центре искусств MusicArtPlus" loading="lazy" width="700" height="700">
        </div>
        <div class="about-visual__badge">
          <img src="assets/img/ui/logo-color.png" alt="">
          <div><b>MusicArtPlus</b><span>центр искусств</span></div>
        </div>
      </div>
      <div class="reveal reveal--right">
        <span class="eyebrow">О центре искусств</span>
        <h2 class="h2">Мы учим не играть «правильно», а учим <span class="accent">слышать и чувствовать Музыку</span></h2>
        <p class="lead" style="margin-top:20px">MusicArtPlus — центр искусств в Москве, где дети от трёх лет и взрослые знакомятся с музыкой, живописью и сценой. Мы соединяем крепкую академическую базу с современными методиками и авторскими программами для каждого ученика.</p>
        <ul class="about-list">
          <li><span class="tick">''' + ico('check') + '''</span><span>Уникальная авторская методика: традиции музыкального образования и современные подходы</span></li>
          <li><span class="tick">''' + ico('check') + '''</span><span>Педагоги Московской консерватории, РАМ им. Гнесиных, ГИТИСа и ВГИКа</span></li>
          <li><span class="tick">''' + ico('check') + '''</span><span>Индивидуальная программа под характер, возраст и цели ребёнка</span></li>
          <li><span class="tick">''' + ico('check') + '''</span><span>Проект поддержан фондом
            <a class="ff-inline" href="''' + FUND + '''" target="_blank" rel="noopener"><img src="assets/img/ui/forteforma.svg" alt="" width="45" height="34">ФОРТЕФОРМА</a></span></li>
        </ul>
        <div class="flex-center mt-l" style="justify-content:flex-start">
          <a class="btn btn--dark" href="about.html">Подробнее о центре</a>
          <a class="link-arrow" href="teachers.html">Наши педагоги''' + ico('ar') + '''</a>
        </div>
      </div>
    </div>
  </div>
</section>

<!-- ===== Блок 3. Новости ===== -->
<section class="section section--cream" id="news">
  ''' + DECO_NOTE + '''
  <div class="container">
    <div class="sec-head">
      <div class="sec-head__text">
        <span class="eyebrow">Новости</span>
        <h2 class="h2">Чем живёт центр искусств</h2>
        <p class="sec-head__desc">Концерты, мастер-классы, выставки и маленькие победы наших учеников.</p>
      </div>
      ''' + slider_nav('news') + '''
    </div>
    ''' + slider([news_card(n, reveal=False) for n in NEWS[:6]], 'news') + '''
    <div class="flex-center mt-l"><a class="btn btn--ghost" href="news.html">Все новости''' + ico('ar', 'btn__ico') + '''</a></div>
  </div>
</section>

''' + INTERLUDE_NOTES + '''

<!-- ===== Блок 4. Наши направления ===== -->
<section class="section" id="directions">
  ''' + DECO_STAR + '''
  <div class="container">
    <div class="sec-head sec-head--center">
      <div class="sec-head__text">
        <span class="eyebrow">Наши направления</span>
        <h2 class="h2">Восемь путей к искусству</h2>
        <p class="sec-head__desc">Инструменты, вокал, сцена и живопись — можно выбрать одно направление или собрать своё сочетание.</p>
      </div>
    </div>
    <div class="dir-mini-grid">''' + dirs_html + '''</div>
    <div class="flex-center mt-l"><a class="btn btn--gold" href="directions.html">Посмотреть все направления''' + ico('ar', 'btn__ico') + '''</a></div>
  </div>
</section>

<!-- ===== Блок 5. Педагоги ===== -->
<section class="section section--cream" id="teachers">
  <div class="container">
    <div class="sec-head">
      <div class="sec-head__text">
        <span class="eyebrow">Педагоги</span>
        <h2 class="h2">Люди, к которым хочется возвращаться</h2>
        <p class="sec-head__desc">Нажмите на фотографию, чтобы открыть биографию, расписание и записаться на урок.</p>
      </div>
      ''' + slider_nav('teachers-home') + '''
    </div>
    ''' + slider([teacher_card(t, reveal=False) for t in TEACHERS], 'teachers-home') + '''
    <div class="flex-center mt-l"><a class="btn btn--ghost" href="teachers.html">Все педагоги''' + ico('ar', 'btn__ico') + '''</a></div>
  </div>
</section>

<!-- ===== Блок 6. Видео ===== -->
<section class="section" id="video">
  ''' + DECO_BRUSH + '''
  <div class="container">
    <div class="sec-head">
      <div class="sec-head__text">
        <span class="eyebrow">Видео</span>
        <h2 class="h2">Посмотрите, как мы работаем</h2>
        <p class="sec-head__desc">Съёмки занятий и мероприятий. Видео открывается прямо на сайте.</p>
      </div>
      <a class="link-arrow" href="''' + RT + '''" target="_blank" rel="noopener">Канал на Rutube''' + ico('ar') + '''</a>
    </div>
    <div class="grid g-3">''' + vid_html + '''</div>
  </div>
</section>

''' + INTERLUDE_BRUSH + '''

<!-- ===== Блок 7. Отзывы ===== -->
<section class="section" id="reviews">
  ''' + DECO_STAR + '''
  <div class="container">
    <div class="sec-head">
      <div class="sec-head__text">
        <span class="eyebrow">Отзывы</span>
        <h2 class="h2">Что говорят родители и ученики</h2>
      </div>
      ''' + slider_nav('reviews-home') + '''
    </div>
    ''' + slider([review_card(r, reveal=False) for r in REVIEWS], 'reviews-home') + '''
  </div>
</section>

''' + MAP_BLOCK + '''

<!-- ===== Блок 9. Связаться с нами ===== -->
<section class="section" id="booking">
  ''' + DECO_NOTE + '''
  <div class="container">
    <div class="contact-grid">
      <div class="reveal reveal--left">
        <span class="eyebrow">Связаться с нами</span>
        <h2 class="h2">Первый урок — чтобы просто попробовать</h2>
        <p class="lead" style="margin-top:18px">Оставьте имя и телефон: мы перезвоним, расспросим о ребёнке и подберём педагога и удобное время. Пробное занятие ни к чему не обязывает.</p>
        <div class="quote" style="margin-top:28px">
          <span class="quote__mark" aria-hidden="true">&ldquo;</span>
          <p>Мой сын бежит на занятия с горящими глазами, а возвращается — с новой искрой во взгляде.</p>
          <footer>Валентина, мама ученика</footer>
        </div>
      </div>
      ''' + form_block('Записаться на пробный урок',
                       'Заполните два поля — остальное мы уточним по телефону.',
                       'Записаться на пробный урок', 'form-home') + '''
    </div>
  </div>
</section>
'''

page('index.html',
     'MusicArtPlus — центр искусств для детей в Москве | музыка, живопись, сцена',
     'Центр искусств MusicArtPlus: фортепиано, скрипка, вокал, актёрское мастерство и живопись для детей от 3 лет. Москва, м. Минская. Запись на пробный урок.',
     'home', INDEX, hero='dark', swiper=True)
print('index.html готов')

# ================================================================ О НАС
def page_hero(crumb, title, text, img, dark=True):
    bg = ('<div class="page-hero__bg"><img src="assets/img/gallery/' + img +
          '" alt="" loading="eager"></div>') if img else ''
    cls = 'page-hero page-hero--photo' if img else 'page-hero'
    return ('<section class="' + cls + '">' + bg +
      '<div class="container"><div class="page-hero__inner">'
      '<nav class="crumbs" aria-label="Хлебные крошки"><a href="index.html">Главная</a>' + ico('ar') +
      '<span>' + crumb + '</span></nav>'
      '<h1>' + title + '</h1><p>' + text + '</p>'
      '</div></div></section>')

STATS = [('11','педагогов и приглашённых артистов'),('8','направлений обучения'),
         ('3','профессора ведущих вузов страны'),('3+','возраст первых занятий, лет')]
stats_html = ''.join('<div class="stat reveal" data-delay="' + str(i) + '"><b>' + n + '</b>'
                     '<span class="stat__l">' + l + '</span></div>' for i, (n, l) in enumerate(STATS))

STEPS = [
 ('Шаг 1','Знакомство и пробный урок','Вы оставляете заявку, мы созваниваемся и подбираем педагога. Первый урок — знакомство: ребёнок пробует инструмент, педагог смотрит на его данные и характер.'),
 ('Шаг 2','Индивидуальная программа','После пробного занятия педагог предлагает программу: репертуар, темп, цели на ближайшие месяцы. Программа авторская — она пишется под конкретного ученика.'),
 ('Шаг 3','Регулярные занятия','Занятия проходят в центре или онлайн. Родители получают обратную связь: что получается, над чем работаем, что делать дома.'),
 ('Шаг 4','Сцена и результат','Концерты, конкурсы, отчётные выступления и выставки. Сцена — часть обучения: она учит собранности и приносит радость.'),
]
steps_html = ''.join('<div class="tl__item reveal" data-delay="' + str(i) + '">'
  '<div class="tl__year">' + y + '</div><h4>' + t + '</h4><p>' + d + '</p></div>'
  for i, (y, t, d) in enumerate(STEPS))

ABOUT = page_hero('О нас', 'Центр искусств, где ребёнку хочется остаться',
  'Музыка, живопись и сцена для детей от трёх лет. Мы соединяем академическую базу с живым, радостным обучением.',
  'g07.jpg') + '''

<section class="section">
  <div class="container">
    <div class="about-split">
      <div class="reveal reveal--left">
        <span class="eyebrow">Кто мы</span>
        <h2 class="h2">Центр, построенный вокруг <span class="accent">ученика</span></h2>
        <p class="lead" style="margin-top:20px">MusicArtPlus — центр искусств в Москве, недалеко от станции метро «Минская». У нас занимаются дошкольники, школьники и взрослые: кто-то приходит за профессией, кто-то — за радостью и уверенностью в себе.</p>
        <p style="margin-top:16px">Мы верим, что музыка и искусство не должны быть испытанием. Поэтому в основе нашей работы — авторская методика, которая объединяет лучшие традиции музыкального образования с современными подходами: крепкая академическая база, живой диалог с учеником и обязательная практика на сцене.</p>
        <p style="margin-top:16px">С нашими учениками работают преподаватели Московской консерватории, РАМ им. Гнесиных, ГИТИСа и ВГИКа, а также приглашённые профессора и заслуженные деятели искусств.</p>
        <div class="flex-center mt-l" style="justify-content:flex-start">
          <a class="btn btn--gold" data-crm="true" href="#">Записаться на пробный урок</a>
          <a class="btn btn--ghost" href="teachers.html">Познакомиться с педагогами</a>
        </div>
      </div>
      <div class="reveal reveal--right">
        ''' + gallery_block(['g11.jpg','g02.jpg','g15.jpg','g04.jpg'], 'gallery-strip') + '''
      </div>
    </div>
  </div>
</section>

<section class="section section--cream section--tight">
  <div class="container">
    <div class="stats">''' + stats_html + '''</div>
  </div>
</section>

<section class="section">
  <div class="container">
    <div class="sec-head sec-head--center">
      <div class="sec-head__text">
        <span class="eyebrow">Как всё устроено</span>
        <h2 class="h2">Путь ученика — от первой заявки до сцены</h2>
      </div>
    </div>
    <div class="grid g-2" style="align-items:start">
      <div class="tl">''' + steps_html + '''</div>
      <div class="reveal reveal--right">
        <div class="quote">
          <span class="quote__mark" aria-hidden="true">&ldquo;</span>
          <p>Здесь тебя слышат. Здесь верят в твой потенциал, даже если ты ещё сам в него не веришь.</p>
          <footer>Валентина, мама ученика</footer>
        </div>
        <div class="card" style="margin-top:22px">
          <div class="card__media" style="aspect-ratio:4/3">
            <img src="assets/img/gallery/g07.jpg" alt="Урок фортепиано" loading="lazy">
          </div>
          <div class="card__body">
            <h3 class="card__title">Пробный урок ни к чему не обязывает</h3>
            <p class="card__text">Это спокойное знакомство: ребёнок пробует, родители задают вопросы, педагог рассказывает, что и как будет дальше.</p>
            <div class="card__foot"><a class="btn btn--gold btn--sm" data-crm="true" href="#">Записаться</a></div>
          </div>
        </div>
      </div>
    </div>
  </div>
</section>

''' + INTERLUDE_NOTES + '''

<section class="section section--cream">
  <div class="container">
    <div class="sec-head sec-head--center">
      <div class="sec-head__text">
        <span class="eyebrow">Атмосфера</span>
        <h2 class="h2">Как проходят наши занятия</h2>
        <p class="sec-head__desc">Светлые классы, два рояля, мольберты и много воздуха. И дети, которым здесь интересно.</p>
      </div>
    </div>
    <div class="reveal">''' + gallery_block(['g07.jpg','g11.jpg','g02.jpg','g15.jpg','g04.jpg','g03.jpg','g09.jpg','g01.jpg','g12.jpg','g14.jpg','g17.jpg','g19.jpg']) + '''</div>
  </div>
</section>

<section class="section section--ink">
  <div class="container">
    <div class="about-split">
      <div class="reveal reveal--left">
        <span class="eyebrow" style="color:var(--gold)">Партнёр центра</span>
        <h2 class="h2">При поддержке фонда ФОРТЕФОРМА</h2>
        <p style="margin-top:18px;font-size:18px;line-height:1.65">Фонд поддерживает музыкальное образование и культурные проекты. Благодаря этому партнёрству в центре проходят мастер-классы приглашённых профессоров, концерты и творческие программы для детей.</p>
        <div class="flex-center mt-l" style="justify-content:flex-start">
          <a class="btn btn--gold" href="''' + FUND + '''" target="_blank" rel="noopener">Сайт фонда''' + ico('ar', 'btn__ico') + '''</a>
        </div>
      </div>
      <div class="reveal reveal--right ff-mark">
        <img src="assets/img/ui/forteforma.svg" alt="Фонд ФОРТЕФОРМА" loading="lazy">
        <b>ФОРТЕФОРМА</b>
        <span>Фонд поддержки и развития культурных и социальных проектов</span>
      </div>
    </div>
  </div>
</section>

<section class="section">
  <div class="container">
    <div class="sec-head">
      <div class="sec-head__text">
        <span class="eyebrow">Отзывы</span>
        <h2 class="h2">Родители — о центре</h2>
      </div>
      <a class="link-arrow" href="teachers.html">Все отзывы''' + ico('ar') + '''</a>
    </div>
    <div class="grid g-3">''' + ''.join(review_card(r, i) for i, r in enumerate(REVIEWS[:3])) + '''</div>
  </div>
</section>

''' + cta_band('Приходите знакомиться',
   'Мы подберём педагога, расскажем о программе и покажем центр. Пробное занятие — лучший способ понять, подходим ли мы друг другу.')

page('about.html', 'О нас — центр искусств MusicArtPlus в Москве',
     'О центре искусств MusicArtPlus: авторская методика, педагоги ведущих вузов, обучение музыке, живописи и актёрскому мастерству с 3 лет.',
     'about', ABOUT, hero='dark')
print('about.html готов')

# ================================================================ НАПРАВЛЕНИЯ
DIRECTIONS = [
 ('dir-piano','piano','Фортепиано','5+ лет','Классическая фортепианная школа: постановка руки, звук, работа над репертуаром. От первых пьес до конкурсных программ и поступления.','Индивидуально','45–60 мин'),
 ('dir-violin','violin','Скрипка','5+ лет','Постановка рук и смычка, интонация, первые ансамбли. Занятия строятся на игре — малышам не бывает скучно.','Индивидуально','30–45 мин'),
 ('dir-trumpet','trumpet','Труба и духовые','8+ лет','Дыхание, звукоизвлечение, оркестровые трудности и камерный ансамбль с педагогом международного уровня.','Индивидуально','45–60 мин'),
 ('dir-vocal','mic','Вокал и сценическая речь','6+ лет','Постановка голоса, дыхание, дикция и работа с микрофоном. Академический и эстрадный репертуар.','Индивидуально / группа','45 мин'),
 ('dir-acting','masks','Актёрское мастерство','6+ лет','Раскрепощение, внимание, сценическое движение и работа с текстом. Помогает и на сцене, и в жизни.','Группа','60 мин'),
 ('dir-art','palette','Изобразительное искусство','5+ лет','Рисунок, живопись, композиция и работа с разными материалами на художественном отделении центра.','Группа','60–90 мин'),
 ('dir-theory','note','Сольфеджио и теория музыки','6+ лет','Слух, ритм, музыкальная грамота и история музыки. Тот фундамент, без которого инструмент остаётся набором нот.','Группа / индивидуально','45 мин'),
 ('dir-early','child','Раннее музыкальное развитие','3–7 лет','Ритмика, слушание, игры со звуком и знакомство с инструментами. Мягкий вход в музыку для самых маленьких.','Мини-группа','30–40 мин'),
]
dirs_page = ''
for i, (aid, icn, t, age, txt, fmt, dur) in enumerate(DIRECTIONS):
    dirs_page += ('<article class="dir-tile reveal" id="' + aid + '" data-delay="' + str(i % 4) + '">'
      '<span class="dir-tile__ico">' + I[icn] + '</span>'
      '<span class="chip">' + age + '</span>'
      '<h3>' + t + '</h3><p>' + txt + '</p>'
      '<div class="dir-tile__meta"><span>Формат: <b>' + fmt + '</b></span><span>Урок: <b>' + dur + '</b></span></div>'
      '<div style="margin-top:16px"><a class="btn btn--gold btn--sm" data-crm="true"'
      ' data-crm-subject="' + esc(t) + '" href="#">Записаться</a></div>'
      '</article>')

SPECIAL = [
 ('users','Камерный ансамбль','Игра в дуэте и в ансамбле — умение слушать партнёра, держать темп и делить сцену. Ведут педагоги Московской консерватории.'),
 ('book','Подготовка к поступлению','Техника, репертуар, сольфеджио и теория — по требованиям конкретного учебного заведения. Опыт подготовки в ведущие музыкальные вузы.'),
 ('online','Онлайн-занятия','Все направления доступны онлайн. Часть педагогов работает в смешанном формате, а двое ведут занятия на английском языке.'),
]
special_html = ''.join('<article class="dir-tile reveal" data-delay="' + str(i) + '">'
  '<span class="dir-tile__ico">' + I[k] + '</span><h3>' + t + '</h3><p>' + d + '</p></article>'
  for i, (k, t, d) in enumerate(SPECIAL))

ADVANTAGES = [
 ('cap','Академическая база','Крепкая академическая база с интеграцией популярных жанров.'),
 ('person','Индивидуальный подход','Индивидуальные авторские программы для каждого ученика.'),
 ('chat','Принцип дискуссионности','Радость «поиска вслух» — совместное открытие музыки.'),
 ('spark','Соответствие времени','Современные методы для современных учеников.'),
 ('link','Теория и практика','Знания сразу применяются в живой музыке.'),
 ('scale','Баланс руководства','Мягкое направление с развитием самостоятельности.'),
 ('cal','Систематичность','Постоянство требований и регулярность занятий.'),
 ('heart','Гармония чувств','Эмоции и сознательное восприятие музыки.'),
]
adv_html = ''.join('<article class="adv reveal" data-delay="' + str(i % 4) + '" style="position:relative">'
  '<span class="adv__num" aria-hidden="true">' + str(i + 1) + '</span>'
  '<span class="adv__ico">' + I[k] + '</span>'
  '<div><h4>' + t + '</h4><p>' + d + '</p></div></article>'
  for i, (k, t, d) in enumerate(ADVANTAGES))

FAQ = [
 ('С какого возраста можно начинать обучение?','Мы принимаем детей от 3 лет на индивидуальное обучение и программы раннего музыкального развития. Индивидуальные занятия на инструментах рекомендуется начинать с 5–6 лет, когда ребёнок готов к более серьёзному обучению.'),
 ('Нужно ли покупать инструмент сразу?','Нет, не обязательно. На первых занятиях можно заниматься на инструментах центра. После пробного периода мы поможем подобрать подходящий инструмент с учётом бюджета и потребностей.'),
 ('Можно ли заниматься онлайн?','Да, мы предлагаем онлайн-занятия по всем направлениям. Некоторые преподаватели работают в смешанном формате.'),
 ('Как проходит подготовка к поступлению?','Наши преподаватели имеют опыт подготовки к поступлению в ведущие музыкальные учебные заведения. Программа включает работу над техникой, репертуаром, сольфеджио и теорией музыки в соответствии с требованиями конкретного вуза.'),
 ('Какова стоимость обучения?','Стоимость зависит от преподавателя, формата занятий и продолжительности урока. Мы предлагаем гибкую систему оплаты и разные варианты абонементов. Точную стоимость можно узнать при записи на пробный урок.'),
 ('Где проходят занятия?','Основная площадка находится по адресу: Москва, ул. Улофа Пальме, д. 5 (м. Минская). Это закрытая территория, поэтому необходим предварительный звонок. Также некоторые преподаватели могут проводить занятия на дому.'),
]
faq_html = ''.join('<div class="faq__item reveal" data-delay="' + str(i % 4) + '">'
  '<button class="faq__q" type="button" aria-expanded="false"><span>' + q + '</span>'
  '<span class="faq__ico">' + I['plus'] + '</span></button>'
  '<div class="faq__a"><div>' + a + '</div></div></div>' for i, (q, a) in enumerate(FAQ))

DIRPAGE = page_hero('Наши направления', 'Восемь путей к искусству',
  'Инструменты, вокал, сцена и живопись. Можно выбрать одно направление или собрать своё сочетание — программа подстроится под ребёнка.',
  'g02.jpg') + '''

<section class="section">
  <div class="container">
    <div class="grid g-4">''' + dirs_page + '''</div>
  </div>
</section>

<section class="section section--cream">
  <div class="container">
    <div class="sec-head sec-head--center">
      <div class="sec-head__text">
        <span class="eyebrow">Особые программы</span>
        <h2 class="h2">Не только уроки по расписанию</h2>
      </div>
    </div>
    <div class="grid g-3">''' + special_html + '''</div>
  </div>
</section>

''' + INTERLUDE_NOTES + '''

<section class="section">
  <div class="container">
    <div class="sec-head sec-head--center">
      <div class="sec-head__text">
        <span class="eyebrow">Наши преимущества</span>
        <h2 class="h2">Авторская методика центра</h2>
        <p class="sec-head__desc">Мы объединили лучшие традиции музыкального образования с современными подходами — и описали это восемью принципами.</p>
      </div>
    </div>
    <div class="grid g-4">''' + adv_html + '''</div>
  </div>
</section>

<section class="section section--cream">
  <div class="container container--narrow">
    <div class="sec-head sec-head--center">
      <div class="sec-head__text">
        <span class="eyebrow">Вопросы и ответы</span>
        <h2 class="h2">Часто задаваемые вопросы</h2>
      </div>
    </div>
    <div class="faq">''' + faq_html + '''</div>
  </div>
</section>

<section class="section">
  <div class="container">
    <div class="contact-grid">
      <div class="reveal reveal--left">
        <span class="eyebrow">Не знаете, что выбрать?</span>
        <h2 class="h2">Поможем подобрать направление и педагога</h2>
        <p class="lead" style="margin-top:18px">Расскажите о ребёнке: возраст, характер, что ему нравится. Мы предложим подходящее направление и педагога, а первый урок покажет, попали ли мы в точку.</p>
        <ul class="about-list">
          <li><span class="tick">''' + ico('check') + '''</span><span>Пробное занятие ни к чему не обязывает</span></li>
          <li><span class="tick">''' + ico('check') + '''</span><span>Можно начать без своего инструмента</span></li>
          <li><span class="tick">''' + ico('check') + '''</span><span>Есть очные и онлайн-форматы</span></li>
        </ul>
      </div>
      ''' + form_block('Подобрать направление',
                       'Оставьте контакты — перезвоним и всё обсудим.',
                       'Отправить заявку', 'form-dir') + '''
    </div>
  </div>
</section>
'''

page('directions.html', 'Наши направления — музыка, вокал, сцена и живопись | MusicArtPlus',
     'Направления обучения в MusicArtPlus: фортепиано, скрипка, труба, вокал, актёрское мастерство, живопись, сольфеджио и раннее музыкальное развитие с 3 лет.',
     'directions', DIRPAGE, hero='dark')
print('directions.html готов')

# ================================================================ ПЕДАГОГИ
guest_videos = ''.join(
  '<button class="video-card reveal" data-delay="' + str(i) + '" type="button" '
  'data-video="' + g['video'] + '" data-video-type="iframe"'
  ' data-video-page="' + g['video'].replace('/play/embed/', '/video/') + '"'
  ' aria-label="Смотреть видео: ' + esc(g['name']) + '">'
  '<img src="assets/img/guests/' + g['photo'] + '" alt="" loading="lazy">'
  '<span class="video-card__play">' + I['play'] + '</span>'
  '<span class="video-card__cap"><b>' + g['name'].split()[0] + ' ' + g['name'].split()[1][0] + '. ' + g['name'].split()[2][0] + '.</b>'
  '<span>' + g['role'] + '</span></span></button>' for i, g in enumerate(GUESTS))

TEACHPAGE = page_hero('Педагоги', 'Педагоги, которым доверяют детей',
  'Преподаватели Московской консерватории, РАМ им. Гнесиных, ГИТИСа и ВГИКа. Нажмите на фотографию — откроется биография, расписание и запись на урок.',
  'g11.jpg') + '''

<section class="section">
  <div class="container">
    <div class="sec-head">
      <div class="sec-head__text">
        <span class="eyebrow">Основной состав</span>
        <h2 class="h2">Наши преподаватели</h2>
        <p class="sec-head__desc">Кнопка «Записаться» ведёт в систему «Мой класс» — там видно свободное время педагога.</p>
      </div>
    </div>
    ''' + slider([teacher_card(t, reveal=False) for t in TEACHERS], 'teachers-all',
                    preset='gridMobile', grid='slider--grid slider--grid-3') + '''
  </div>
</section>

<section class="section section--cream">
  <div class="container">
    <div class="sec-head sec-head--center">
      <div class="sec-head__text">
        <span class="eyebrow">Приглашённые артисты и педагоги</span>
        <h2 class="h2">Мастера, которые приходят к нам на мастер-классы</h2>
        <p class="sec-head__desc">Профессора ведущих музыкальных вузов страны проводят открытые занятия и творческие встречи для наших учеников.</p>
      </div>
    </div>
    ''' + slider([guest_card(g, reveal=False) for g in GUESTS], 'guests',
                    preset='gridMobile', grid='slider--grid slider--grid-3') + '''
  </div>
</section>

<section class="section">
  <div class="container">
    <div class="sec-head sec-head--center">
      <div class="sec-head__text">
        <span class="eyebrow">Фотогалерея</span>
        <h2 class="h2">Фотографии с уроков</h2>
      </div>
    </div>
    <div class="reveal">''' + gallery_block(['g11.jpg','g07.jpg','g02.jpg','g15.jpg','g03.jpg','g04.jpg','g09.jpg','g12.jpg','g01.jpg','g14.jpg','g17.jpg','g19.jpg','g05.jpg','g08.jpg','g13.jpg','g18.jpg']) + '''</div>
  </div>
</section>

<section class="section section--cream">
  <div class="container">
    <div class="sec-head">
      <div class="sec-head__text">
        <span class="eyebrow">Видео</span>
        <h2 class="h2">Приглашённые педагоги — в записи</h2>
        <p class="sec-head__desc">Видео открывается прямо на сайте, без перехода на Rutube.</p>
      </div>
      <a class="link-arrow" href="''' + RT + '''" target="_blank" rel="noopener">Канал на Rutube''' + ico('ar') + '''</a>
    </div>
    <div class="grid g-3">''' + guest_videos + '''</div>
  </div>
</section>

<section class="section">
  <div class="container">
    <div class="sec-head">
      <div class="sec-head__text">
        <span class="eyebrow">Отзывы</span>
        <h2 class="h2">Что говорят о наших педагогах</h2>
      </div>
      ''' + slider_nav('reviews-teachers') + '''
    </div>
    ''' + slider([review_card(r, reveal=False) for r in REVIEWS], 'reviews-teachers') + '''
  </div>
</section>

<section class="section section--cream">
  <div class="container">
    <div class="contact-grid">
      <div class="reveal reveal--left">
        <span class="eyebrow">Обратная связь</span>
        <h2 class="h2">Не выбрали педагога? Поможем</h2>
        <p class="lead" style="margin-top:18px">Оставьте имя и телефон — мы перезвоним, расспросим о ребёнке и предложим педагога, который подойдёт по возрасту, характеру и целям.</p>
        <div class="ci" style="margin-top:26px"><span class="ci__ico">''' + I['phone'] + '''</span>
          <div><b>Позвонить сейчас</b><a href="tel:''' + PHONE_HREF + '''">''' + PHONE + '''</a></div></div>
      </div>
      ''' + form_block('Подобрать педагога',
                       'Два поля — и мы свяжемся с вами в ближайшее рабочее время.',
                       'Отправить заявку', 'form-teach') + '''
    </div>
  </div>
</section>
'''

page('teachers.html', 'Педагоги центра искусств MusicArtPlus — Москва',
     'Преподаватели MusicArtPlus: фортепиано, скрипка, труба, вокал, живопись и сольфеджио. Биографии, расписание и запись на урок онлайн.',
     'teachers', TEACHPAGE, hero='dark', swiper=True)
print('teachers.html готов')

# ================================================================ НОВОСТИ
FULL = {
 'camp': ['Две недели летнего лагеря пролетели незаметно. Каждый день начинался с общей разминки и ритмики, а дальше ребята расходились по мастерским: кто-то занимался ансамблем, кто-то уходил на пленэр с мольбертами.',
          'Вечерами мы устраивали маленькие концерты — играли то, что успели выучить за день. Без оценок и волнения: важнее было попробовать себя на сцене и поддержать друг друга.',
          'Спасибо родителям за доверие, а педагогам — за две недели, в которые уместилось столько музыки.'],
 'concert': ['Отчётный концерт — главное событие учебного года. В программе прозвучали произведения от первых пьес начинающих до серьёзных концертных номеров старших учеников.',
             'Для многих ребят это был первый выход на большую сцену. Волнение, поклон, аплодисменты — тот самый опыт, ради которого мы и занимаемся.',
             'Поздравляем всех участников и благодарим педагогов за подготовку.'],
 'master': ['Александр Львович Маклыгин — доктор искусствоведения, профессор кафедры «Теория музыки» Московской консерватории им. П. И. Чайковского, председатель Общества теории музыки России.',
            'На открытом мастер-классе речь шла о том, как слышать музыкальную форму и не бояться импровизации. Занятие было построено как диалог: ученики играли, задавали вопросы и спорили.',
            'Запись мастер-класса доступна в разделе «Педагоги».'],
 'open': ['Мы пригласили родителей на открытый урок раннего музыкального развития, чтобы показать, как устроено занятие с малышами 3–7 лет.',
          'Ритмические игры, знакомство с инструментами, первые ноты и много движения — урок построен так, чтобы ребёнок не уставал и уходил с желанием вернуться.',
          'Группы раннего развития ведёт Серафим Гончаров, автор книги «Горизонты детства».'],
 'winners': ['Наши ученики выступили на международном конкурсе и вернулись с наградами. Мы гордимся не только дипломами, но и тем, как ребята держались на сцене.',
             'Конкурс — это всегда работа: месяцы подготовки, разбор записей, борьба с волнением. Спасибо педагогам, которые прошли этот путь вместе с учениками.'],
 'art': ['Художественное отделение центра объявляет набор. Занятия ведёт Анна Адольфовна Белякова — преподаватель и методист отделения, отмеченная почётными грамотами Министерства культуры РФ.',
         'В программе — рисунок, живопись, композиция и работа с разными материалами. Занятия проходят в мини-группах, инструменты и материалы есть в центре.'],
 'bach': ['Вера Борисовна Носина — профессор кафедры специального фортепиано РАМ им. Гнесиных, заслуженный деятель искусств РФ.',
          'На концерте-беседе она рассказала о символике музыки Баха: как за нотами прячутся образы и смыслы, и почему это меняет исполнение.',
          'Встреча была открыта для учеников и их родителей.'],
 'newyear': ['Новогодний вечер мы придумывали вместе с учениками: музыкальные номера, небольшой спектакль и выставка работ художественного отделения.',
             'Получился настоящий семейный праздник — с чаем, подарками и живой музыкой.'],
}

def news_url(n):
    return 'news-' + n['slug'] + '.html'

def news_article(n, delay=0, featured=False):
    media_ratio = '16/9' if featured else '16/11'
    return ('<a class="card reveal" href="' + news_url(n) + '" data-delay="' + str(delay) + '"'
      ' data-news-item data-tag="' + n['tag'] + '">'
      '<div class="card__media" style="aspect-ratio:' + media_ratio + '">'
        '<div class="card__date"><b>' + n['d'] + '</b><span>' + n['m'][:3] + '</span></div>'
        '<img src="assets/img/gallery/' + n['img'] + '" alt="' + esc(n['title']) + '" loading="lazy">'
      '</div>'
      '<div class="card__body">'
        '<span class="chip chip--outline">' + n['tag'] + '</span>'
        '<h3 class="card__title">' + n['title'] + '</h3>'
        '<p class="card__text">' + n['text'] + '</p>'
        '<div class="card__foot"><span class="link-arrow">Читать' + ico('ar') + '</span></div>'
      '</div></a>')

def news_mini(n, delay=0):
    return ('<a class="news-mini reveal" href="' + news_url(n) + '" data-delay="' + str(delay) + '"'
      ' data-news-item data-tag="' + n['tag'] + '">'
      '<div class="news-mini__img"><img src="assets/img/gallery/' + n['img'] + '" alt="" loading="lazy"></div>'
      '<div class="news-mini__body"><time>' + n['d'] + ' ' + n['m'] + ' ' + n['y'] + '</time>'
      '<b>' + n['title'] + '</b>'
      '<span class="link-arrow" style="font-size:14.5px;margin-top:4px">Читать' + ico('ar') + '</span></div></a>')

TAGS = ['Жизнь центра', 'Концерты', 'Мастер-классы', 'Достижения']
filters = '<button class="is-active" data-filter="all">Все новости</button>' + \
          ''.join('<button data-filter="' + t + '">' + t + '</button>' for t in TAGS)

NEWSPAGE = page_hero('Новости', 'Чем живёт центр искусств',
  'Концерты, мастер-классы, выставки и маленькие победы наших учеников. Здесь мы рассказываем о том, что происходит в MusicArtPlus.',
  'g01.jpg') + '''

<section class="section">
  <div class="container">
    <div class="filter-bar">''' + filters + '''</div>
    <div class="news-featured">
      ''' + news_article(NEWS[0], 0, featured=True) + '''
      <div class="news-side">''' + ''.join(news_mini(n, i + 1) for i, n in enumerate(NEWS[1:4])) + '''</div>
    </div>
    <div class="grid g-3" style="margin-top:clamp(20px,2.6vw,36px)">
      ''' + ''.join(news_article(n, i % 3) for i, n in enumerate(NEWS[4:])) + '''
    </div>
  </div>
</section>

<section class="section section--cream section--tight">
  <div class="container">
    <div class="cta-band reveal reveal--scale">
      <span class="cta-band__glow" aria-hidden="true"></span>
      <div>
        <h2 class="h2">Не пропускайте новое</h2>
        <p>Анонсы концертов, мастер-классов и наборов в группы мы публикуем в Telegram-канале центра.</p>
      </div>
      <div class="cta-band__actions">
        <a class="btn btn--gold btn--lg" href="''' + TG + '''" target="_blank" rel="noopener">Telegram-канал''' + ico('ar', 'btn__ico') + '''</a>
        <a class="btn btn--light btn--lg" href="''' + RT + '''" target="_blank" rel="noopener">Rutube</a>
      </div>
    </div>
  </div>
</section>

'''

page('news.html', 'Новости центра искусств MusicArtPlus — концерты и мастер-классы',
     'Новости MusicArtPlus: концерты, мастер-классы приглашённых профессоров, выставки, летний лагерь и достижения учеников.',
     'news', NEWSPAGE, hero='dark')
print('news.html готов')

# ================================================================ СТРАНИЦЫ НОВОСТЕЙ
I['copy'] = '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><rect x="9" y="9" width="12" height="12" rx="2.6"/><path d="M6 15H4.6A1.6 1.6 0 013 13.4V4.6A1.6 1.6 0 014.6 3h8.8A1.6 1.6 0 0115 4.6V6"/></svg>'
I['vk'] = '<svg viewBox="0 0 24 24" fill="currentColor"><path d="M12.8 16.6c-5.2 0-8.4-3.7-8.5-9.8h2.7c.1 4.5 2.1 6.4 3.6 6.8V6.8h2.5v3.9c1.5-.2 3.1-1.9 3.6-3.9h2.5c-.4 2.4-2 4.1-3.2 4.8 1.2.6 3 2.1 3.7 4.9h-2.7c-.5-1.7-2-3-3.9-3.2v3.3z"/></svg>'

def _nav_card(item, direction):
    if not item:
        return '<div class="prevnext__stub"></div>'
    arrow = I['al'] if direction == 'prev' else I['ar']
    label = 'Предыдущая' if direction == 'prev' else 'Следующая'
    cls = 'prevnext--prev' if direction == 'prev' else 'prevnext--next'
    return ('<div class="' + cls + '"><a href="' + news_url(item) + '">' + arrow +
            '<span><span class="prevnext__dir">' + label + '</span><b>' + item['title'] + '</b></span></a></div>')

def news_page(n, prev_n, next_n):
    body_html = ''.join('<p>' + p + '</p>' for p in FULL.get(n['slug'], [n['text']]))
    others = [x for x in NEWS if x['slug'] != n['slug']][:3]

    body = (
      '<article class="section article">'
        '<div class="container container--narrow">'
          '<nav class="crumbs" aria-label="Хлебные крошки">'
            '<a href="index.html">Главная</a>' + ico('ar') +
            '<a href="news.html">Новости</a>' + ico('ar') +
            '<span>' + n['tag'] + '</span>'
          '</nav>'
          '<span class="chip">' + n['tag'] + '</span>'
          '<h1>' + n['title'] + '</h1>'
          '<div class="article__meta">'
            '<time datetime="' + n['y'] + '">' + n['d'] + ' ' + n['m'] + ' ' + n['y'] + ' г.</time>'
          '</div>'
          '<figure class="article__cover reveal">'
            '<img src="assets/img/gallery/' + n['img'] + '" alt="' + esc(n['title']) + '" fetchpriority="high">'
          '</figure>'
          '<div class="article__body">' + body_html + '</div>'
          '<div class="article__foot">'
            '<a class="link-arrow link-arrow--back" href="news.html">' + I['al'] + 'Все новости</a>'
            '<div class="share">'
              '<span>Поделиться</span>'
              '<a data-share="tg" href="#" target="_blank" rel="noopener" aria-label="Поделиться в Telegram">' + I['tg'] + '</a>'
              '<a data-share="vk" href="#" target="_blank" rel="noopener" aria-label="Поделиться во ВКонтакте">' + I['vk'] + '</a>'
              '<button data-share="copy" type="button" aria-label="Скопировать ссылку">' + I['copy'] + '</button>'
            '</div>'
          '</div>'
          '<div class="prevnext">' + _nav_card(prev_n, 'prev') + _nav_card(next_n, 'next') + '</div>'
        '</div>'
      '</article>'
      '<section class="section section--cream">'
        '<div class="container">'
          '<div class="sec-head">'
            '<div class="sec-head__text">'
              '<span class="eyebrow">Читайте также</span>'
              '<h2 class="h2">Другие новости центра</h2>'
            '</div>'
            '<a class="link-arrow" href="news.html">Все новости' + ico('ar') + '</a>'
          '</div>'
          '<div class="grid g-3">' + ''.join(news_card(o, i) for i, o in enumerate(others)) + '</div>'
        '</div>'
      '</section>'
      + cta_band('Хотите так же?',
                 'Приходите на пробный урок — покажем центр, познакомим с педагогом и расскажем о программе.'))

    page(news_url(n), n['title'] + ' — новости MusicArtPlus', n['text'], 'news', body)

for _i, _n in enumerate(NEWS):
    news_page(_n,
              NEWS[_i - 1] if _i > 0 else None,
              NEWS[_i + 1] if _i + 1 < len(NEWS) else None)
print('страниц новостей:', len(NEWS))

# ================================================================
# ВРЕМЕННО: на главной отключены переходы на другие страницы —
# заказчику отправляется на согласование дизайн только главной.
# Чтобы вернуть ссылки, поставьте DEMO_HOME_ONLY = False и пересоберите.
# ================================================================
DEMO_HOME_ONLY = True

if DEMO_HOME_ONLY:
    import re as _re
    _path = os.path.join(OUT, 'index.html')
    _html = io.open(_path, encoding='utf-8').read()

    def _mute(m):
        href = m.group(1)
        if href.startswith('index.html'):     # ссылка на саму себя — оставляем
            return m.group(0)
        return ' data-demo'

    _html, _n = _re.subn(r' href="([a-z0-9-]+\.html(?:#[^"]*)?)"', _mute, _html)
    io.open(_path, 'w', encoding='utf-8').write(_html)
    print('главная: отключено переходов на другие страницы —', _n)
