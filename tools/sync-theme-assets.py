# -*- coding: utf-8 -*-
"""Переносит картинки, видео и Swiper из статической вёрстки в тему.

CSS и JS НЕ трогаем: в теме у них есть свои дополнения (служебные классы
WordPress, отправка формы на REST-маршрут), и слепое копирование их затрёт.
Правки стилей и скриптов после переезда на WordPress вносятся в тему.

Запуск:  python3 tools/sync-theme-assets.py
"""
import filecmp, os, shutil

ROOT = os.path.dirname(os.path.dirname(os.path.abspath(__file__)))
SRC = os.path.join(ROOT, 'assets')
DST = os.path.join(ROOT, 'theme', 'musicartplus', 'assets')

# Что синхронизируем один в один
FOLDERS = ('img', 'video', 'vendor')


def main():
    copied = skipped = 0

    for folder in FOLDERS:
        src_dir = os.path.join(SRC, folder)

        if not os.path.isdir(src_dir):
            continue

        for base, _dirs, files in os.walk(src_dir):
            rel = os.path.relpath(base, SRC)
            out = os.path.join(DST, rel)

            if not os.path.isdir(out):
                os.makedirs(out)

            for name in files:
                if name == '.DS_Store':
                    continue

                a = os.path.join(base, name)
                b = os.path.join(out, name)

                if os.path.exists(b) and filecmp.cmp(a, b, shallow=False):
                    skipped += 1
                    continue

                shutil.copy2(a, b)
                copied += 1

    print('скопировано: %d, без изменений: %d' % (copied, skipped))
    print('CSS и JS не синхронизируются — правьте их в теме.')


if __name__ == '__main__':
    main()
