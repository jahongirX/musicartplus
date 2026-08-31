# -*- coding: utf-8 -*-
"""Выгружает встроенные иконки из generator.py в отдельные SVG-файлы.

Иконка в теме перестала быть пунктом списка: её загружают файлом. Чтобы при
переходе ничего не потерялось, готовый набор выкладывается файлами — те же
рисунки, которые были в коде.

В файл дописываются атрибуты обводки и заливки. Раньше их задавал CSS гнезда,
но чужой SVG с собственной заливкой от такого правила превратился бы
в контур, поэтому теперь каждый файл описывает себя сам.

Запуск:  python3 tools/build-icon-files.py
"""
import io, os, re

ROOT = os.path.dirname(os.path.dirname(os.path.abspath(__file__)))
DEST = os.path.join(ROOT, 'theme', 'musicartplus', 'assets', 'img', 'icons')

src = io.open(os.path.join(ROOT, 'tools', 'generator.py'), encoding='utf-8').read()

ns = {}
exec(compile('I = {}', 'icons', 'exec'), ns)
for line in src.splitlines():
    if line.startswith("I['"):
        exec(compile(line, 'generator.py', 'exec'), ns)
I = ns['I']

LINE = ('fill="none" stroke="currentColor" stroke-width="1.6" '
        'stroke-linecap="round" stroke-linejoin="round"')
TICK = ('fill="none" stroke="currentColor" stroke-width="2.6" '
        'stroke-linecap="round" stroke-linejoin="round"')
SOLID = 'fill="currentColor" stroke="none"'

# Заливка вместо обводки — у знаков, нарисованных силуэтом.
FILLED = {'star', 'play', 'phone', 'pin2', 'tg', 'vk'}
THICK = {'check'}

os.makedirs(DEST, exist_ok=True)
made = 0

for key in sorted(I):
    svg = I[key]

    # Свои атрибуты у иконки уже есть — не трогаем.
    if 'stroke="currentColor"' in svg or 'fill="currentColor"' in svg:
        out = svg
    else:
        attrs = SOLID if key in FILLED else (TICK if key in THICK else LINE)
        out = re.sub(r'<svg ', '<svg ' + attrs + ' ', svg, count=1)

    out = out.replace(' aria-hidden="true"', '')
    out = '<?xml version="1.0" encoding="UTF-8"?>\n' + out + '\n'

    io.open(os.path.join(DEST, key + '.svg'), 'w', encoding='utf-8').write(out)
    made += 1

print('иконок выгружено: %d -> %s' % (made, os.path.relpath(DEST, ROOT)))
