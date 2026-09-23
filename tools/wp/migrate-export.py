"""Шаг 1 переноса: выгрузка с тестового сервера.

Делает на тестовом сервере два файла с одноразовыми именами и отдаёт
их адреса: дамп базы и архив медиатеки. Забирать их будет сервер клиента
(шаг 2, migrate-install.py), поэтому файлы лежат в папке сайта и доступны
по ссылке — имена случайные, а после переноса скрипт их удаляет.

Перед выгрузкой чистит тестовые заявки: персональные данные на новый
сервер не переносим.

Запуск:  python tools/wp/migrate-export.py           — выгрузить
         python tools/wp/migrate-export.py --clean   — удалить выгруженное
"""

import io
import json
import os
import pathlib
import secrets
import sys

sys.path.insert(0, os.path.dirname(os.path.abspath(__file__)))
from common import Remote, say, SITE_SUBDIR, SITE_URL  # noqa: E402

import importlib.util  # noqa: E402

_spec = importlib.util.spec_from_file_location('runphp', os.path.join(os.path.dirname(__file__), 'run-php.py'))
runphp = importlib.util.module_from_spec(_spec)
_spec.loader.exec_module(runphp)

STATE = pathlib.Path(__file__).resolve().parents[2] / 'tools' / 'wp' / '.migrate-state.json'

EXPORT = r'''
$token = '%(token)s';
$base  = ABSPATH;

// Тестовые заявки на боевой сайт не едут.
$leads = get_posts(array('post_type' => 'lead', 'post_status' => 'any', 'numberposts' => -1));
foreach ($leads as $lead) wp_delete_post($lead->ID, true);
echo 'удалено тестовых заявок: ', count($leads), "\n";

// Дамп базы: mysqldump, если он есть, иначе выгрузка средствами PHP.
// nginx тестового сервера не отдаёт .sql (405), поэтому расширение .txt
$sql = $base . 'dump-' . $token . '.txt';
$cmd = sprintf('mysqldump --no-tablespaces --default-character-set=utf8mb4 -h%%s -u%%s -p%%s %%s > %%s 2>/dev/null',
    escapeshellarg(DB_HOST), escapeshellarg(DB_USER), escapeshellarg(DB_PASSWORD),
    escapeshellarg(DB_NAME), escapeshellarg($sql));
$out = function_exists('shell_exec') ? (string) shell_exec($cmd) : 'нет shell_exec';
if (!file_exists($sql) || filesize($sql) < 10000) {
    echo 'mysqldump не сработал (', trim($out), '), выгружаю через PHP', "\n";
    global $wpdb;
    $fh = fopen($sql, 'w');
    fwrite($fh, "SET NAMES utf8mb4;\nSET FOREIGN_KEY_CHECKS=0;\n");
    foreach ($wpdb->get_col('SHOW TABLES') as $table) {
        $create = $wpdb->get_row('SHOW CREATE TABLE `' . $table . '`', ARRAY_N);
        fwrite($fh, "\nDROP TABLE IF EXISTS `$table`;\n" . $create[1] . ";\n");
        $offset = 0;
        while (true) {
            $rows = $wpdb->get_results("SELECT * FROM `$table` LIMIT 200 OFFSET $offset", ARRAY_A);
            if (!$rows) break;
            foreach ($rows as $row) {
                $values = array();
                foreach ($row as $value) {
                    $values[] = is_null($value) ? 'NULL' : "'" . esc_sql($value) . "'";
                }
                fwrite($fh, "INSERT INTO `$table` VALUES (" . implode(',', $values) . ");\n");
            }
            $offset += 200;
        }
    }
    fclose($fh);
}
echo 'дамп: ', round(filesize($sql) / 1048576, 1), " МБ\n";

// Архив медиатеки.
$zip_path = $base . 'uploads-' . $token . '.zip';
$zip = new ZipArchive();
$zip->open($zip_path, ZipArchive::CREATE | ZipArchive::OVERWRITE);
$uploads = $base . 'wp-content/uploads';
$files = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($uploads, FilesystemIterator::SKIP_DOTS));
$count = 0;
foreach ($files as $file) {
    $zip->addFile($file->getPathname(), substr($file->getPathname(), strlen($uploads) + 1));
    $count++;
}
$zip->close();
echo 'медиатека: ', $count, ' файлов, ', round(filesize($zip_path) / 1048576, 1), " МБ\n";

echo 'SQL_URL=', home_url('/dump-' . $token . '.txt'), "\n";
echo 'ZIP_URL=', home_url('/uploads-' . $token . '.zip'), "\n";
echo 'WP_VERSION=', get_bloginfo('version'), "\n";
echo 'PREFIX=', $GLOBALS['wpdb']->prefix, "\n";
echo 'SITEURL=', get_option('siteurl'), "\n";
'''

CLEAN = r'''
$token = '%(token)s';
foreach (array('dump-' . $token . '.txt', 'uploads-' . $token . '.zip') as $name) {
    $path = ABSPATH . $name;
    echo $name, ': ', (file_exists($path) && unlink($path)) ? 'удалён' : 'уже нет', "\n";
}
'''


def state():
    return json.loads(STATE.read_text(encoding='utf-8')) if STATE.exists() else {}


def main():
    if '--clean' in sys.argv:
        token = state().get('token')
        if not token:
            sys.exit('нечего чистить: выгрузки не было')
        say(runphp.run(CLEAN % {'token': token}).strip())
        STATE.unlink(missing_ok=True)
        return

    token = secrets.token_hex(8)
    report = runphp.run(EXPORT % {'token': token})
    say(report.strip())
    data = dict(
        line.split('=', 1) for line in report.splitlines() if '=' in line and line.split('=', 1)[0].isupper()
    )
    data['token'] = token
    STATE.write_text(json.dumps(data, ensure_ascii=False, indent=1), encoding='utf-8')
    say('\nсохранено в %s' % STATE.name)


if __name__ == '__main__':
    main()
