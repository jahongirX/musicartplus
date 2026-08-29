# -*- coding: utf-8 -*-
"""Локализует шрифты Google Fonts для темы WordPress.

Скачивает только кириллицу и латиницу (греческий, вьетнамский и символы сайту
не нужны) и собирает assets/css/fonts.css со ссылками на локальные файлы.

Зачем: убирает обращения к fonts.googleapis.com и fonts.gstatic.com — это два
лишних DNS-резолва и два TLS-хендшейка перед первой отрисовкой текста.

Запуск:  python3 tools/build-theme-fonts.py
"""
import io, os, re, urllib.request

ROOT = os.path.dirname(os.path.dirname(os.path.abspath(__file__)))
THEME = os.path.join(ROOT, 'theme', 'musicartplus')
FONT_DIR = os.path.join(THEME, 'assets', 'fonts')
CSS_OUT = os.path.join(THEME, 'assets', 'css', 'fonts.css')

SRC = ('https://fonts.googleapis.com/css2'
       '?family=Ysabeau:ital,wght@0,300..800;1,300..600'
       '&family=Ysabeau+SC:wght@500;600;700&display=swap')

# Без User-Agent современного браузера Google отдаёт ttf вместо woff2.
UA = ('Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 '
      '(KHTML, like Gecko) Chrome/122.0 Safari/537.36')

KEEP = {'cyrillic', 'cyrillic-ext', 'latin', 'latin-ext'}


def main():
    if not os.path.isdir(FONT_DIR):
        os.makedirs(FONT_DIR)

    req = urllib.request.Request(SRC, headers={'User-Agent': UA})
    css = urllib.request.urlopen(req).read().decode('utf-8')

    blocks = re.findall(r'/\*\s*([a-z\-]+)\s*\*/\s*(@font-face\s*\{[^}]*\})', css)
    out, saved = [], 0

    for subset, block in blocks:
        if subset not in KEEP:
            continue

        url = re.search(r'url\((https://fonts\.gstatic\.com[^)]+)\)', block)
        if not url:
            continue
        url = url.group(1)

        family = re.search(r"font-family:\s*'([^']+)'", block).group(1)
        style = 'italic' if 'font-style: italic' in block else 'normal'
        name = '%s-%s-%s.woff2' % (family.replace(' ', ''), subset, style)
        path = os.path.join(FONT_DIR, name)

        if not os.path.exists(path):
            urllib.request.urlretrieve(url, path)
            saved += 1

        out.append('/* %s */\n%s' % (subset, block.replace(url, '../fonts/' + name)))

    io.open(CSS_OUT, 'w', encoding='utf-8').write(
        '/* Локальные шрифты Ysabeau. Сгенерировано tools/build-theme-fonts.py */\n'
        + '\n'.join(out) + '\n')

    print('подключений @font-face:', len(out), '| скачано файлов:', saved)


if __name__ == '__main__':
    main()
