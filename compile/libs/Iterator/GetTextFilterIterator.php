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
namespace S3Sync\Compile\Iterator;

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
class GetTextFilterIterator extends \RecursiveFilterIterator
{
    /**
     * +-- accept
     *
     * @access      public
     * @return bool
     */
    public function accept()
    {
        $iterator = $this->getInnerIterator();
        if ($iterator->isDir()) {
            return true;
        }

        if (1 === preg_match('/\.po$/', $iterator->current())) {
            return true;
        }

        return false;
    }
    /* ----------------------------------------- */
}
