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
define('S3_SYNC_INSTALL_DIR', __FILE__);
define('S3_SYNC_VERSION', 'phar');
Phar::mapPhar('S3Sync.phar');

$aws_phar = sys_get_temp_dir().DIRECTORY_SEPARATOR.'S3SyncAWS.phar';
if (!is_file($aws_phar)) {
    umask(0);
    file_put_contents($aws_phar, file_get_contents('https://github.com/aws/aws-sdk-php/releases/download/2.8.7/aws.phar'));
}

include $aws_phar;
include 'phar://S3Sync.phar/main.php';

__HALT_COMPILER(); ?>