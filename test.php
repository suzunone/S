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
define('S3_SYNC_VERSION', 'cli');

include __DIR__.'/bin/aws.php';
include __DIR__.'/src/main.php';

