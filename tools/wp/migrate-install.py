"""Шаг 2 переноса: разворачивание сайта на хостинге клиента.

Что делает:
  1. собирает архив темы (тема + assets, как deploy-theme.py) и заливает
     его по FTP вместе с одноразовым установщиком;
  2. установщик на сервере клиента скачивает ядро WordPress и плагины
     с wordpress.org, а базу и медиатеку — с тестового сервера
     (адреса берутся из .migrate-state.json, файл создаёт migrate-export.py);
  3. пишет wp-config.php с доступами клиента и новыми солями;
  4. импортирует базу и заменяет адрес тестового сайта на боевой,
     аккуратно с сериализованными строками;
  5. убирает за собой: архивы и сам установщик удаляются.

Индексацию скрипт НЕ открывает: сайт остаётся закрытым от поисковиков,
пока мы не проверим его на боевом адресе.

Доступы клиента — creds/card-199-hosting.env, в репозиторий не попадают.

Запуск:  python tools/wp/migrate-install.py
         python tools/wp/migrate-install.py --force   — переустановить поверх
"""

import io
import json
import os
import pathlib
import secrets
import sys
import time
import urllib.error
import urllib.request
import zipfile

sys.path.insert(0, os.path.dirname(os.path.abspath(__file__)))
from common import read_env, say  # noqa: E402

import importlib.util  # noqa: E402

_deploy_spec = importlib.util.spec_from_file_location(
    'deploytheme', os.path.join(os.path.dirname(__file__), 'deploy-theme.py'))
deploy = importlib.util.module_from_spec(_deploy_spec)
_deploy_spec.loader.exec_module(deploy)

ROOT = pathlib.Path(__file__).resolve().parents[2]
STATE = ROOT / 'tools' / 'wp' / '.migrate-state.json'

# Плагины ставим свежими с wordpress.org, а не переносим с теста: так на
# боевом сайте не окажется ни старых версий, ни ненужных плагинов.
PLUGINS = (
    'secure-custom-fields',
    'seo-by-rank-math',
    'updraftplus',
    'wp-mail-smtp',
    'limit-login-attempts-reloaded',
)

INSTALLER = r'''<?php
// Одноразовый установщик. Удаляется в конце работы.
set_time_limit(0);
ignore_user_abort(true);
header('Content-Type: text/plain; charset=utf-8');
if (($_GET['token'] ?? '') !== '%(token)s') { http_response_code(403); exit('нет токена'); }
$cfg = %(config)s;
$root = __DIR__;
$force = !empty($_GET['force']);

function step($text) { echo $text, "\n"; flush(); }

/** Скачивание файла с проверкой размера. */
function grab($url, $dest) {
    // Тестовый сервер (Beget) без служебной печеньки отдаёт страницу-заглушку,
    // поэтому запрашиваем с ней — на wordpress.org она просто игнорируется.
    $context = stream_context_create(array('http' => array(
        'header' => "Cookie: beget=begetok
User-Agent: tts-migrate
",
        'timeout' => 300, 'follow_location' => 1)));
    $in = fopen($url, 'rb', false, $context);
    if (!$in) { step('  не открылся адрес ' . $url); return false; }
    $out = fopen($dest, 'wb');
    stream_copy_to_stream($in, $out);
    fclose($in); fclose($out);
    step('  скачано ' . basename($dest) . ': ' . round(filesize($dest) / 1048576, 1) . ' МБ');
    return filesize($dest) > 1024;
}

/** Распаковка архива, с возможностью срезать верхнюю папку. */
function tts_unpack($file, $target, $strip = '') {
    $zip = new ZipArchive();
    if ($zip->open($file) !== true) { step('  архив не открылся: ' . basename($file)); return 0; }
    $done = 0;
    for ($i = 0; $i < $zip->numFiles; $i++) {
        $name = $zip->getNameIndex($i);
        $rel = $strip && strpos($name, $strip) === 0 ? substr($name, strlen($strip)) : $name;
        if ($rel === '' || substr($rel, -1) === '/') { if ($rel) @mkdir($target . '/' . $rel, 0755, true); continue; }
        @mkdir(dirname($target . '/' . $rel), 0755, true);
        copy('zip://' . $file . '#' . $name, $target . '/' . $rel);
        $done++;
    }
    $zip->close();
    return $done;
}

// ---------------------------------------------------------------- ядро
if ($force || !file_exists($root . '/wp-load.php')) {
    step('Ставлю WordPress ' . $cfg['wp_version']);
    $core = $root . '/core.zip';
    if (!grab('https://ru.wordpress.org/wordpress-' . $cfg['wp_version'] . '-ru_RU.zip', $core)) {
        grab('https://wordpress.org/wordpress-' . $cfg['wp_version'] . '.zip', $core);
    }
    step('  файлов распаковано: ' . tts_unpack($core, $root, 'wordpress/'));
    @unlink($core);
} else {
    step('WordPress уже стоит, ядро не трогаю');
}

// ---------------------------------------------------------------- wp-config
$config_path = $root . '/wp-config.php';
if ($force || !file_exists($config_path)) {
    step('Пишу wp-config.php');
    $salts = @file_get_contents('https://api.wordpress.org/secret-key/1.1/salt/');
    if (!$salts || strpos($salts, 'AUTH_KEY') === false) {
        $salts = '';
        foreach (array('AUTH_KEY','SECURE_AUTH_KEY','LOGGED_IN_KEY','NONCE_KEY',
                       'AUTH_SALT','SECURE_AUTH_SALT','LOGGED_IN_SALT','NONCE_SALT') as $key) {
            $salts .= "define('$key', '" . bin2hex(random_bytes(32)) . "');\n";
        }
    }
    $config = "<?php\n"
        . "define('DB_NAME', '" . $cfg['db_name'] . "');\n"
        . "define('DB_USER', '" . $cfg['db_user'] . "');\n"
        . "define('DB_PASSWORD', '" . $cfg['db_password'] . "');\n"
        . "define('DB_HOST', '" . $cfg['db_host'] . "');\n"
        . "define('DB_CHARSET', 'utf8mb4');\n"
        . "define('DB_COLLATE', '');\n"
        . $salts
        . "\$table_prefix = '" . $cfg['prefix'] . "';\n"
        . "define('WP_DEBUG', false);\n"
        . "define('DISALLOW_FILE_EDIT', true);\n"   // правка файлов из админки запрещена
        . "define('WP_AUTO_UPDATE_CORE', 'minor');\n"
        . "define('WP_MEMORY_LIMIT', '256M');\n"
        . "if (!defined('ABSPATH')) define('ABSPATH', __DIR__ . '/');\n"
        . "require_once ABSPATH . 'wp-settings.php';\n";
    file_put_contents($config_path, $config);
    step('  готово');
}

// ---------------------------------------------------------------- база
// Импорт идёт ДО загрузки WordPress: при пустой базе wp-load уводит на
// мастер установки, и дальше скрипт уже не выполняется.
$mysqli = new mysqli($cfg['db_host'], $cfg['db_user'], $cfg['db_password'], $cfg['db_name']);
if ($mysqli->connect_error) { step('база недоступна: ' . $mysqli->connect_error); exit; }
$mysqli->set_charset('utf8mb4');
$existing = $mysqli->query('SHOW TABLES')->num_rows;
if ($force || !$existing) {
    step('Переношу базу');
    $sql = $root . '/dump.sql';
    if (!file_exists($sql) && !grab($cfg['sql_url'], $sql)) { step('  дамп не скачался — останавливаюсь'); exit; }
    $fh = fopen($sql, 'r');
    $query = '';
    $count = 0;
    $failed = 0;
    $mysqli->query('SET FOREIGN_KEY_CHECKS=0');
    while (($line = fgets($fh)) !== false) {
        if ($line === '' || substr($line, 0, 2) === '--' || substr($line, 0, 2) === '/*') continue;
        $query .= $line;
        if (substr(rtrim($line), -1) === ';') {
            if (!$mysqli->query($query)) { $failed++; if ($failed < 4) step('  ошибка: ' . substr($mysqli->error, 0, 120)); }
            $query = '';
            $count++;
        }
    }
    fclose($fh);
    @unlink($sql);
    step('  выполнено запросов: ' . $count . ', с ошибкой: ' . $failed);
    step('  таблиц в базе: ' . $mysqli->query('SHOW TABLES')->num_rows);
} else {
    step('В базе уже есть таблицы (' . $existing . '), импорт пропущен');
}

require_once $root . '/wp-load.php';
global $wpdb;

// ---------------------------------------------------------------- медиатека
$uploads = $root . '/wp-content/uploads';
@mkdir($uploads, 0755, true);
if ($force || count(glob($uploads . '/*')) === 0) {
    step('Переношу медиатеку');
    $zip = $root . '/uploads.zip';
    if (grab($cfg['zip_url'], $zip)) step('  файлов: ' . tts_unpack($zip, $uploads));
    @unlink($zip);
}

// ---------------------------------------------------------------- тема
$theme_zip = $root . '/theme.zip';
if (file_exists($theme_zip)) {
    step('Ставлю тему');
    @mkdir($root . '/wp-content/themes/tts', 0755, true);
    step('  файлов: ' . tts_unpack($theme_zip, $root . '/wp-content/themes/tts'));
    @unlink($theme_zip);
}

// ---------------------------------------------------------------- плагины
step('Ставлю плагины');
foreach ($cfg['plugins'] as $slug) {
    $dir = $root . '/wp-content/plugins/' . $slug;
    if (!$force && is_dir($dir)) { step('  ' . $slug . ' — уже есть'); continue; }
    $file = $root . '/' . $slug . '.zip';
    if (grab('https://downloads.wordpress.org/plugin/' . $slug . '.latest-stable.zip', $file)) {
        tts_unpack($file, $root . '/wp-content/plugins');
        step('  ' . $slug . ' — поставлен');
    }
    @unlink($file);
}

// ---------------------------------------------------------------- адреса
step('Меняю адрес сайта на ' . $cfg['new_url']);

/** Замена с учётом сериализованных строк: длина в них должна совпасть. */
function replace_deep($value, $from, $to) {
    if (is_string($value)) {
        $un = @unserialize($value);
        if ($un !== false || $value === 'b:0;') {
            return serialize(replace_deep($un, $from, $to));
        }
        return str_replace($from, $to, $value);
    }
    if (is_array($value)) {
        $out = array();
        foreach ($value as $k => $v) $out[replace_deep($k, $from, $to)] = replace_deep($v, $from, $to);
        return $out;
    }
    if (is_object($value)) {
        foreach (get_object_vars($value) as $k => $v) $value->$k = replace_deep($v, $from, $to);
        return $value;
    }
    return $value;
}

$old = rtrim($cfg['old_url'], '/');
$new = rtrim($cfg['new_url'], '/');
$replaced = 0;
$targets = array(
    array($wpdb->options, 'option_id', 'option_value'),
    array($wpdb->posts, 'ID', 'post_content'),
    array($wpdb->posts, 'ID', 'guid'),
    array($wpdb->postmeta, 'meta_id', 'meta_value'),
    array($wpdb->termmeta, 'meta_id', 'meta_value'),
    array($wpdb->usermeta, 'umeta_id', 'meta_value'),
);
foreach ($targets as $t) {
    list($table, $key, $column) = $t;
    $rows = $wpdb->get_results($wpdb->prepare(
        "SELECT `$key` AS id, `$column` AS value FROM `$table` WHERE `$column` LIKE %%s", '%%' . $wpdb->esc_like($old) . '%%'));
    foreach ($rows as $row) {
        $value = replace_deep($row->value, $old, $new);
        if ($value !== $row->value) {
            $wpdb->update($table, array($column => $value), array($key => $row->id));
            $replaced++;
        }
    }
}
step('  строк обновлено: ' . $replaced);
update_option('siteurl', $new);
update_option('home', $new);
update_option('blog_public', 0);   // индексацию откроем вручную после проверки

// Постоянные ссылки и .htaccess
require_once ABSPATH . 'wp-admin/includes/misc.php';
require_once ABSPATH . 'wp-admin/includes/file.php';
update_option('permalink_structure', '/%%postname%%/');
$GLOBALS['wp_rewrite']->init();
$GLOBALS['wp_rewrite']->flush_rules(true);
step('  постоянные ссылки: ' . get_option('permalink_structure'));

// Тема и плагины
switch_theme('tts');
require_once ABSPATH . 'wp-admin/includes/plugin.php';
foreach ($cfg['plugins'] as $slug) {
    foreach (array_keys(get_plugins('/' . $slug)) as $file) {
        $path = $slug . '/' . $file;
        if (!is_plugin_active($path)) activate_plugin($path);
    }
}
step('активная тема: ' . get_stylesheet());
step('активные плагины: ' . implode(', ', array_map(fn($p) => dirname($p), (array) get_option('active_plugins'))));
step('siteurl: ' . get_option('siteurl'));
step('страниц: ' . wp_count_posts('page')->publish . ', оборудования: ' . wp_count_posts('equipment')->publish);

@unlink(__FILE__);
step('установщик удалён');
step('ГОТОВО');
'''


def zip_theme(dest):
    """Тема вместе с assets — тем же составом, что уходит на тестовый сервер."""
    with zipfile.ZipFile(dest, 'w', zipfile.ZIP_DEFLATED) as zf:
        for local, rel in deploy.files_to_send():
            zf.write(local, rel)
    return dest.stat().st_size


def main():
    if not STATE.exists():
        sys.exit('нет .migrate-state.json — сначала запустите migrate-export.py')
    state = json.loads(STATE.read_text(encoding='utf-8'))
    env = read_env('card-199-hosting.env')
    force = '--force' in sys.argv

    scratch = pathlib.Path(os.environ.get('TEMP', '.')) / 'tts-theme.zip'
    say('собираю архив темы: %.1f МБ' % (zip_theme(scratch) / 1048576))

    token = secrets.token_hex(8)
    config = {
        'wp_version': state['WP_VERSION'],
        'prefix': state['PREFIX'],
        'old_url': state['SITEURL'],
        'new_url': 'https://' + env['SITE_DOMAIN'],
        'sql_url': state['SQL_URL'],
        'zip_url': state['ZIP_URL'],
        'db_name': env['DB_NAME'],
        'db_user': env['DB_USER'],
        'db_password': env['DB_PASSWORD'],
        'db_host': env.get('DB_HOST', 'localhost'),
        'plugins': list(PLUGINS),
    }
    installer = INSTALLER % {
        'token': token,
        'config': 'json_decode(<<<\'JSON\'\n%s\nJSON, true)' % json.dumps(config, ensure_ascii=False),
    }

    import ftplib
    name = 'install-%s.php' % token
    ftp = ftplib.FTP()
    ftp.connect(env['FTP_HOST'], 21, timeout=60)
    ftp.login(env['FTP_USER'], env['FTP_PASSWORD'])
    say('заливаю тему и установщик по FTP')
    with io.open(scratch, 'rb') as fh:
        ftp.storbinary('STOR theme.zip', fh, blocksize=1 << 16)
    ftp.storbinary('STOR ' + name, io.BytesIO(installer.encode('utf-8')))

    url = 'https://%s/%s?token=%s%s' % (env['SITE_DOMAIN'], name, token, '&force=1' if force else '')
    say('запускаю установку, это займёт несколько минут')
    start = time.time()
    try:
        with urllib.request.urlopen(url, timeout=900) as response:
            for line in io.TextIOWrapper(response, encoding='utf-8', errors='replace'):
                say('  ' + line.rstrip())
    except urllib.error.HTTPError as error:
        say('установщик ответил кодом %s:' % error.code)
        say(error.read().decode('utf-8', 'replace')[:2000])
    except Exception as error:
        say('установщик прервался: %s %s' % (type(error).__name__, str(error)[:200]))
    say('время: %d с' % (time.time() - start))

    # Подчищаем за собой на случай, если установщик не дошёл до конца.
    for junk in (name, 'theme.zip', 'core.zip', 'uploads.zip', 'dump.sql'):
        try:
            ftp.delete(junk)
            say('удалён остаток: ' + junk)
        except ftplib.error_perm:
            pass
    ftp.quit()


if __name__ == '__main__':
    main()
