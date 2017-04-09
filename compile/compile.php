#!/usr/bin/env php
<?php
/**
 * @category   Amazon AWS S3
 * @package    S3Sync
 * @subpackage S3SyncCompile
 * @author     akito<akito-artisan@five-foxes.com>
 * @author     suzunone<suzunone.eleven@gmail.com>
 * @copyright Project S3Sync For PHP
 * @license MIT
 * @version    GIT: $Id$
 * @link https://github.com/suzunone/S3Sync
 * @see https://github.com/suzunone/S3Sync
 * @since      Class available since Release 1.0.0
 */
umask(0);

ini_set('max_execution_time', 0);
ini_set('memory_limit', -1);

define('BASE_DIR', dirname(__DIR__));


if (!class_exists('\S3Sync\Autoloader', false)) {
    include BASE_DIR.'/src/libs/S3Sync/Autoloader.php';
}

$Autoloader = new \S3Sync\Autoloader;

$Autoloader->register();
$Autoloader->addNamespace('S3Sync\Compile\Compiler', __DIR__.'/libs/Compiler');
$Autoloader->addNamespace('S3Sync\Compile\Iterator', __DIR__.'/libs/Iterator');
$Autoloader->addNamespace('S3Sync\Compile\Exception', __DIR__.'/libs/Exception');



try {
    if (isset($argv[1]) && $argv[1] === 'create_phar') {
        $CreateMO = new \S3Sync\Compile\Compiler\CreateMO;
        $CreateMO->execute();

        $CreateGT = new \S3Sync\Compile\Compiler\CreateGT;
        $CreateGT->execute();

        $CreatePhar = new \S3Sync\Compile\Compiler\CreatePhar;
        $CreatePhar->execute();
    }

    $compile_path = BASE_DIR.'/bin/S3Sync.phar';
    if (is_file($compile_path)) {
        unlink($compile_path);
    }

    $install_path = BASE_DIR.'/S3Sync.php';
    if (is_file($install_path)) {
        unlink($install_path);
    }

    $windows_path = BASE_DIR.'/windows.zip';
    if (is_file($windows_path)) {
        unlink($windows_path);
    }

    $cmd = 'php -d phar.readonly=0 '.__FILE__.' create_phar';

    echo `$cmd`;
    chmod($compile_path, 0777);
    copy($compile_path, $install_path);

    $cmd = 'zip -r '.$windows_path.' '.BASE_DIR.'/bin';
    echo `$cmd`;
} catch (\S3Sync\Compile\Exception\Kill $e) {
} catch (exception $e) {
    var_dump($e);
}

