"""Заливка темы на боевой сайт (tts-engineering.kz, hoster.kz).

На тестовом сервере тема уходит по SFTP пофайлово (deploy-theme.py).
У клиента только FTP, и сотни мелких файлов по нему идут долго, поэтому
тему пакуем в архив, кладём одним файлом и распаковываем на сервере.

Состав архива тот же, что и на тесте: тема плюс общие assets.

Запуск:  python tools/wp/deploy-theme-prod.py
"""

import io
import os
import pathlib
import sys
import zipfile

sys.path.insert(0, os.path.dirname(os.path.abspath(__file__)))
from common import read_env, say  # noqa: E402

import importlib.util  # noqa: E402


def _load(name, filename):
    spec = importlib.util.spec_from_file_location(name, os.path.join(os.path.dirname(__file__), filename))
    module = importlib.util.module_from_spec(spec)
    spec.loader.exec_module(module)
    return module


deploy = _load('deploytheme', 'deploy-theme.py')
prod = _load('prodphp', 'prod-php.py')

UNPACK = r'''
$zip_path = ABSPATH . 'theme.zip';
if (!file_exists($zip_path)) { echo "архива нет\n"; return; }
$target = get_theme_root() . '/tts';
$zip = new ZipArchive();
if ($zip->open($zip_path) !== true) { echo "архив не открылся\n"; return; }
$count = 0;
for ($i = 0; $i < $zip->numFiles; $i++) {
    $name = $zip->getNameIndex($i);
    if (substr($name, -1) === '/') continue;
    @mkdir(dirname($target . '/' . $name), 0755, true);
    copy('zip://' . $zip_path . '#' . $name, $target . '/' . $name);
    $count++;
}
$zip->close();
unlink($zip_path);
echo 'файлов обновлено: ', $count, "\n";
echo 'активная тема: ', get_stylesheet(), "\n";
$theme = wp_get_theme('tts');
$errors = $theme->errors();
echo 'ошибки темы: ', ($errors ? implode('; ', $errors->get_error_messages()) : 'нет'), "\n";
'''


def main():
    import ftplib

    env = read_env('card-199-hosting.env')
    archive = pathlib.Path(os.environ.get('TEMP', '.')) / 'tts-theme-prod.zip'
    with zipfile.ZipFile(archive, 'w', zipfile.ZIP_DEFLATED) as zf:
        for local, rel in deploy.files_to_send():
            zf.write(local, rel)
    say('архив темы: %.1f МБ' % (archive.stat().st_size / 1048576))

    ftp = ftplib.FTP()
    ftp.connect(env['FTP_HOST'], 21, timeout=60)
    ftp.login(env['FTP_USER'], env['FTP_PASSWORD'])
    with io.open(archive, 'rb') as fh:
        ftp.storbinary('STOR theme.zip', fh, blocksize=1 << 16)
    ftp.quit()
    say('архив залит, распаковываю')
    say(prod.run(UNPACK).strip())


if __name__ == '__main__':
    main()
