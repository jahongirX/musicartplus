# -*- coding: utf-8 -*-
"""Переносит словарь иконок из generator.py в inc/icons.php темы — один в один.

Запуск:  python3 tools/build-theme-icons.py
"""
import io, os

ROOT = os.path.dirname(os.path.dirname(os.path.abspath(__file__)))
src = io.open(os.path.join(ROOT, 'tools', 'generator.py'), encoding='utf-8').read()

# Часть иконок объявлена не в шапке, а по ходу файла (I['vk'], I['copy']),
# поэтому собираем все строки вида I['ключ'] = '...' по всему исходнику.
ns = {}
exec(compile('I = {}', 'icons', 'exec'), ns)
for line in src.splitlines():
    if line.startswith("I['"):
        exec(compile(line, 'generator.py', 'exec'), ns)
I = ns['I']


def php_str(s):
    return "'" + s.replace('\\', '\\\\').replace("'", "\\'") + "'"


out = ["<?php",
       "/**",
       " * Иконки темы.",
       " *",
       " * Файл сгенерирован из tools/generator.py — правьте генератор и пересоберите:",
       " * python3 tools/build-theme-icons.py",
       " *",
       " * @package MusicArtPlus",
       " */",
       "",
       "defined( 'ABSPATH' ) || exit;",
       "",
       "/**",
       " * Возвращает разметку SVG-иконки.",
       " *",
       " * @param string $name  Ключ иконки.",
       " * @param string $class Дополнительный CSS-класс.",
       " * @return string",
       " */",
       "function map_icon( $name, $class = '' ) {",
       "\tstatic $icons = null;",
       "",
       "\tif ( null === $icons ) {",
       "\t\t$icons = array("]

for k in sorted(I):
    out.append("\t\t\t%-12s => %s," % ("'" + k + "'", php_str(I[k])))

out += ["\t\t);",
        "\t}",
        "",
        "\tif ( ! isset( $icons[ $name ] ) ) {",
        "\t\treturn '';",
        "\t}",
        "",
        "\t$svg = $icons[ $name ];",
        "",
        "\tif ( '' !== $class ) {",
        "\t\t$svg = preg_replace( '/<svg /', '<svg class=\"' . esc_attr( $class ) . '\" ', $svg, 1 );",
        "\t}",
        "",
        "\treturn $svg;",
        "}",
        "",
        "/**",
        " * Печатает SVG-иконку.",
        " *",
        " * @param string $name  Ключ иконки.",
        " * @param string $class Дополнительный CSS-класс.",
        " * @return void",
        " */",
        "function map_the_icon( $name, $class = '' ) {",
        "\t// Статичная разметка SVG из кода темы, экранирование не требуется.",
        "\techo map_icon( $name, $class ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped",
        "}",
        ""]

dest = os.path.join(ROOT, 'theme', 'musicartplus', 'inc', 'icons.php')
io.open(dest, 'w', encoding='utf-8').write('\n'.join(out))
print('иконок перенесено:', len(I), '->', os.path.relpath(dest, ROOT))
