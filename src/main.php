<?php
/**
 * @category   Amazon AWS S3
 * @package    S3Sync
 * @subpackage S3Sync For PHP
 * @author     akito<akito-artisan@five-foxes.com>
 * @author     suzunone<suzunone.eleven@gmail.com>
 * @copyright Project S3Sync For PHP
 * @license    MIT
 * @version    GIT: $Id$
 * @link https://github.com/suzunone/S3Sync
 * @see https://github.com/suzunone/S3Sync
 * @since      Class available since Release 1.0.0
 */
namespace {
    umask(0);
    ini_set('max_execution_time', 0);
    ini_set('memory_limit', -1);
    if (!defined('S3_SYNC_INSTALL_DIR')) {
        define('S3_SYNC_INSTALL_DIR', __FILE__);
    }

    if (!defined('S3_SYNC_VERSION')) {
        define('S3_SYNC_VERSION', 'cli');
    }

    $is_debug = true;

    if (!class_exists('\S3Sync\Autoloader', false)) {
        include 'libs/S3Sync/Autoloader.php';
    }



    if (!ini_get('date.timezone')) {
        $TZ = @date_default_timezone_get();
        date_default_timezone_set($TZ ? $TZ : 'Europe/London');
    }

    // get-textが有効かどうかで処理を分ける
    $is_get_text = false;
    if (!function_exists('\textdomain')) {
        include __DIR__.DIRECTORY_SEPARATOR.'get_text.php';
        define('S3_SYNC_IS_GET_TEXT', false);
    } else {
        // domain
        $domain = 'messages';

        // LANG
        $locale = trim(`echo \$LANG`);
        if (empty($locale)) {
            $locale = 'ja_JP.UTF-8';
        }

        setlocale(LC_ALL, $locale);

        list($lang, $code_set) = explode('.', $locale);
        textdomain($domain);
        bind_textdomain_codeset($domain, 'UTF-8');

        if (S3_SYNC_VERSION === 'phar') {
            define('S3_SYNC_BINDTEXTDOMAIN', ('phar://S3Sync.phar/lang/'));
        } else {
            define('S3_SYNC_BINDTEXTDOMAIN',  (__DIR__.DIRECTORY_SEPARATOR.'lang').DIRECTORY_SEPARATOR);
        }

        $is_bindtextdomain = bindtextdomain($domain, S3_SYNC_BINDTEXTDOMAIN);

        $gettext_data = [];
        if ($is_bindtextdomain) {
            define('S3_SYNC_IS_GET_TEXT', true);
        } else {
            define('S3_SYNC_IS_GET_TEXT', false);
            if (is_file(S3_SYNC_BINDTEXTDOMAIN.$lang.DIRECTORY_SEPARATOR.'LC_MESSAGES'.DIRECTORY_SEPARATOR."{$domain}.po.php")) {
                $gettext_data = include S3_SYNC_BINDTEXTDOMAIN.$lang.DIRECTORY_SEPARATOR.'LC_MESSAGES'.DIRECTORY_SEPARATOR."{$domain}.po.php";
            }
        }

    }

    function __($message) {
        global $gettext_data;
        if (S3_SYNC_IS_GET_TEXT) {
            return _($message);
        } else {
            return isset($gettext_data[$message]) ? $gettext_data[$message] : $message;
        }
    }
}
namespace S3Sync\Main{

    $Autoloader = new \S3Sync\Autoloader;

    $Autoloader->register();

    if (S3_SYNC_VERSION === 'phar') {
        $Autoloader->addNamespace('S3Sync\Driver', 'phar://S3Sync.phar/libs/S3Sync/Driver');
        $Autoloader->addNamespace('S3Sync', 'phar://S3Sync.phar/libs/S3Sync');
    } else {
        $Autoloader->addNamespace('S3Sync\Driver', __DIR__.'/libs/S3Sync/Driver');
        $Autoloader->addNamespace('S3Sync', __DIR__.'/libs/S3Sync');
    }



    try {

        $setting_files = [
            '~/.aws_s3sync_php/setting.php',
            __DIR__.DIRECTORY_SEPARATOR.'setting.php',
            '/etc/aws_s3sync_php/setting.php',
            '/etc/aws_s3sync_php.php',
        ];

        if (DIRECTORY_SEPARATOR === '\\') {
            mb_internal_encoding('utf8');
            mb_http_output('sjis-win');
            mb_http_input('sjis-win');

            $setting_files = [
                'C:\windows\aws_s3sync_php.php',
                'C:\Windows\aws_s3sync_php.php',
            ];
        }

        $option = null;
        foreach ($setting_files as $setting_file) {
            if (is_file($setting_file)) {
                $option = include($setting_file);
            }
        }

        if ($option) {
            $S3Sync = \S3Sync\S3Sync::factory($option);
        } else {
            $S3Sync = \S3Sync\S3Sync::factory();
        }


        $S3Sync->execute();
    } catch (\exception $e) {
        $S3Sync->ncecho($e->getMessage()."\n");
    }
}
