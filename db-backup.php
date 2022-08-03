#!/usr/bin/env php

<?php

require __DIR__ . "/vendor/autoload.php";

use Spatie\DbDumper\Databases\MySql;
use Spatie\DbDumper\Compressors\GzipCompressor;
use Kunnu\Dropbox\Dropbox;
use Kunnu\Dropbox\DropboxApp;
use Kunnu\Dropbox\DropboxFile;

$dotenv = Dotenv\Dotenv::createImmutable(__DIR__);
$dotenv->load();

$host = $_ENV['DB_HOST'];
$port = $_ENV['DB_PORT'];
$db = $_ENV['DB_NAME'];
$username = $_ENV['DB_USERNAME'];
$password = $_ENV['DB_PASSWORD'];

$dump_file_path = __DIR__ . '/backups/' . $db . '.sql.gz';
$dropbox_client_id = $_ENV['DROPBOX_CLIENT_ID'];
$dropbox_client_secret = $_ENV['DROPBOX_CLIENT_SECRET'];
$dropbox_token = $_ENV['DROPBOX_CLIENT_TOKEN'];
$path = $_ENV['DROPBOX_PATH'];

$result = -1;

try {

    // Create dump
    MySql::create()
        ->setHost('127.0.0.1')
        ->setDbName('smxcs')
        ->setUserName('smxcs')
        ->setPassword('6eC7PIpa')
        ->useCompressor(new GzipCompressor)
        ->dumpToFile($dump_file_path);

    // Upload to Dropbox
    $app = new DropboxApp($dropbox_client_id, $dropbox_client_secret, $dropbox_token);
    $dropbox = new Dropbox($app);

    $dropboxFile = DropboxFile::createByPath($dump_file_path, DropboxFile::MODE_READ);

    $file = $dropbox->simpleUpload($dropboxFile, $path . '/' . date('Y-m-d') . '.sql.gz');

     $result = 0;

} catch (\Exception $ex) {
    echo "\n" . $ex->getMessage();
    $result = -1;
} finally {
    if (file_exists($dump_file_path)) {
        unlink($dump_file_path);
    }
    exit($result);
}
