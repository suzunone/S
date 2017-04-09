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

use \Phar;

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
class CreatePhar
{
    protected $phar;
    /**
     * +-- コンストラクタ
     *
     * @access      public
     * @return void
     */
    public function __construct()
    {
        $this->phar = new Phar(BASE_DIR.'/bin/S3Sync.phar', 0);
        $this->phar->setSignatureAlgorithm(Phar::SHA256);
        $this->phar->setStub(file_get_contents(BASE_DIR.'/compile/src/stub.php'));
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
        $this->phar = new Phar(BASE_DIR.'/bin/S3Sync.phar', 0);
        $this->phar->setSignatureAlgorithm(Phar::SHA256);
        $this->phar->setStub(file_get_contents(BASE_DIR.'/compile/src/stub.php'));
        $this->addFileList(BASE_DIR.'/src');
        $this->phar->stopBuffering();

        copy(BASE_DIR.'/bin/S3Sync.phar', BASE_DIR.'/bin/S3Sync.none.phar');

        $this->phar->compressFiles(Phar::BZ2);

        throw new \S3Sync\Compile\Exception\Kill;
    }
    /* ----------------------------------------- */

    /**
     * +--
     *
     * @access      protected
     * @param  var_text $dir
     * @return array
     */
    protected function addFileList($dir)
    {
        $iterator = new \RecursiveDirectoryIterator($dir);
        $iterator = new \S3Sync\Compile\Iterator\CompileFilterIterator($iterator);
        $iterator = new \RecursiveIteratorIterator($iterator);
        foreach ($iterator as $fileinfo) {
            if ($fileinfo->isFile()) {
                $this->phar->addFile(
                    $fileinfo->getPathname(),
                    strtr(
                        $fileinfo->getPathname(),
                        array(BASE_DIR.'/src' => '')
                    )
                );
            }
        }
    }
    /* ----------------------------------------- */
}
