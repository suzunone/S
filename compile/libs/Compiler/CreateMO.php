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
namespace S3Sync\Compile\Compiler;

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
class CreateMO
{
    /**
     * +-- コンストラクタ
     *
     * @access      public
     * @return void
     */
    public function __construct()
    {
    }
    /* ----------------------------------------- */

    /**
     * +--
     *
     * @access      public
     * @return void
     */
    public function execute()
    {
        foreach ($this->getCommandList(BASE_DIR.'/src') as $cmd) {
            `$cmd`;
        }
    }
    /* ----------------------------------------- */

    /**
     * +--
     *
     * @access      protected
     * @param  var_text $dir
     * @return array
     */
    protected function getCommandList($dir)
    {
        $iterator = new \RecursiveDirectoryIterator($dir);
        $iterator = new \S3Sync\Compile\Iterator\GetTextFilterIterator($iterator);
        $iterator = new \RecursiveIteratorIterator($iterator);
        foreach ($iterator as $fileinfo) {
            if ($fileinfo->isFile() && mb_ereg('.po$', $fileinfo->getPathname())) {
                $lang = substr($fileinfo->getPathname(), 0, -3);
                unlink("{$lang}.mo");
                $cmd = "msgfmt  -o  {$lang}.mo {$lang}.po";
                yield $cmd;
            }
        }
    }
    /* ----------------------------------------- */
}
