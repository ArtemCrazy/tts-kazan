"""Плагины WordPress: скачать с wordpress.org, распаковать и включить.

Ставим только то, без чего не закрыть требования ТЗ. Кэш добавим на этапе
оптимизации: во время разработки он мешает видеть правки.

Запуск:  python tools/wp/install-plugins.py
"""

import os
import secrets
import sys
import urllib.request

sys.path.insert(0, os.path.dirname(os.path.abspath(__file__)))
from common import Remote, fetch, say, SITE_SUBDIR, SITE_URL  # noqa: E402

PLUGINS = [
    # Поля, блоки Gutenberg и страница настроек (п. 12.2 и 12.3 ТЗ).
    # Это ACF от самого WordPress: те же возможности, что в платной версии, лицензий нет.
    ('secure-custom-fields', 'поля, блоки и страница настроек'),
    # SEO-поля, карта сайта, canonical, крошки, разметка Schema.org (п. 14 ТЗ).
    ('seo-by-rank-math', 'SEO'),
    # Отправка заявок через SMTP (п. 11.2 ТЗ).
    ('wp-mail-smtp', 'отправка писем через SMTP'),
    # Ограничение попыток входа (п. 15.3 ТЗ).
    ('limit-login-attempts-reloaded', 'защита входа'),
    # Резервные копии базы и файлов (п. 15.3 ТЗ).
    ('updraftplus', 'резервные копии'),
]

SCRATCH = os.environ.get('TEMP', '.')
DOWNLOAD = 'https://downloads.wordpress.org/plugin/%s.latest-stable.zip'

RUNNER = '''<?php
// Одноразовый установщик плагинов: распаковать архивы и включить. Удаляется сразу.
header('Content-Type: text/plain');
if (($_GET['token'] ?? '') !== '%(token)s') { http_response_code(403); exit('нет токена'); }
require __DIR__ . '/wp-load.php';
require_once ABSPATH . 'wp-admin/includes/plugin.php';
$slugs = explode(',', '%(slugs)s');
$dir = WP_PLUGIN_DIR;
foreach ($slugs as $slug) {
    $zip = __DIR__ . '/plugin-' . $slug . '.zip';
    if (!is_dir($dir . '/' . $slug) && file_exists($zip)) {
        $z = new ZipArchive();
        if ($z->open($zip) === true) { $z->extractTo($dir); $z->close(); }
        else { echo "$slug: архив не открылся\n"; continue; }
    }
    // Список плагинов WordPress держит в памяти запроса: без сброса
    // кеша только что распакованный плагин в нём не появится.
    wp_clean_plugins_cache(false);
    $main = null;
    foreach (get_plugins() as $file => $data) {
        if (strpos($file, $slug . '/') === 0) { $main = $file; break; }
    }
    if (!$main) { echo "$slug: не нашёлся после распаковки\n"; continue; }
    if (is_plugin_active($main)) { echo "$slug: уже включён\n"; continue; }
    $err = activate_plugin($main);
    echo "$slug: " . (is_wp_error($err) ? 'ошибка — ' . $err->get_error_message() : 'включён')
        . ' (' . $data['Version'] . ")\n";
}
'''


def main():
    r = Remote()
    token = secrets.token_hex(8)
    runner = f'run-{token}.php'
    uploaded = []

    for slug, what in PLUGINS:
        local = os.path.join(SCRATCH, f'{slug}.zip')
        if not os.path.exists(local):
            say(f'качаю {slug} — {what}')
            urllib.request.urlretrieve(DOWNLOAD % slug, local)
        r.put(local, SITE_SUBDIR, f'plugin-{slug}.zip')
        uploaded.append(f'plugin-{slug}.zip')

    slugs = ','.join(slug for slug, _ in PLUGINS)
    r.put_text(RUNNER % {'token': token, 'slugs': slugs}, SITE_SUBDIR, runner)
    say('распаковываю и включаю на сервере...')
    say(fetch(f'{SITE_URL}/{runner}?token={token}').strip())

    for junk in uploaded + [runner]:
        try:
            r.remove(SITE_SUBDIR, junk)
        except FileNotFoundError:
            pass
    say('служебные файлы удалены')
    r.close()


if __name__ == '__main__':
    main()
