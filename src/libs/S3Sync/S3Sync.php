<?php
namespace S3Sync;
/**
 *
 *
 * PHP versions 5
 *
 *
 * @category   Amazon AWS S3
 * @package    S3Sync
 * @subpackage S3Sync
 * @author     suzunone <suzunone.eleven@gmail.com>
 * @copyright  S3Sync For PHP
 * @license    WTFPL
 * @version    Release: @package_version@
 * @link       https://github.com/suzunone/S3Sync
 * @see        https://github.com/suzunone/S3Sync
 * @since      Class available since Release 1.0.0
 */





use \Aws\Common\Aws;
use \Aws\Common\Enum\Region;
use \Aws\S3\Enum\CannedAcl;
use \Aws\S3\Exception\S3Exception;
use \Guzzle\Http\EntityBody;




/**
 *
 *
 * @category   Amazon AWS S3
 * @package    S3Sync
 * @subpackage S3Sync
 * @author     suzunone <suzunone.eleven@gmail.com>
 * @copyright  S3Sync For PHP
 * @license    WTFPL
 * @version    Release: @package_version@
 * @link       https://github.com/suzunone/S3Sync
 * @see        https://github.com/suzunone/S3Sync
 * @since      Class available since Release 1.0.0
 */
class S3Sync
{
    /**
     * AWS Iam ACCESS_KEY
     *
     * @var         string
     */
    const ACCESS_KEY = '';
    /* ----------------------------------------- */

    /**
     * AWS Iam SECRET_KEY
     *
     * @var         string
     */
    const SECRET_KEY = '';
    /* ----------------------------------------- */

    const VERSION = '1.0';

    protected $command_options;

    /**
     * +-- コンストラクタ
     *
     * @access      private
     * @param       array $command_options
     * @return      void
     */
    private function __construct(array $command_options)
    {
        $this->command_options = $command_options;
    }
    /* ----------------------------------------- */

    /**
     * +-- ファクトリー
     *
     * @access      public
     * @param       array $command_options
     * @static
     * @return      void
     */
    public static function factory(array $command_options = NULL)
    {
        static $S3Sync;
        global $argv;
        if ($S3Sync) {
            return $S3Sync;
        }
        if (empty($command_options)) {
            $S3Sync = new S3Sync(
                array('s3' => [
                    'access_key' => self::ACCESS_KEY,
                    'secret_key' => self::SECRET_KEY,
                ])
            );
        } else {
            $S3Sync = new S3Sync(
                array('s3' => [
                    'access_key' => $command_options['access_key'],
                    'secret_key' => $command_options['secret_key'],
                ])
            );
        }

        return $S3Sync;
    }
    /* ----------------------------------------- */

    /**
     * +-- メイン処理
     *
     * @access      public
     * @return      void
     */
    public function execute()
    {
        global $argv;
        $shortopts  = "";
        $shortopts .= "ntuq";

        $longopts  = array(
            "dry-run",
            "dry_run",
            "delete",
            'times',
            'update',
            'quiet',
            'delete',
            'del',
            'init',
            'access_key:',
            'secret_key:',
            'acl:',
        );

        $cmd_options = getopt($shortopts, $longopts);

        if (isset($cmd_options['init'])) {
            $setting_file = '/etc/aws_s3sync_php.php';
            if ($this->isWin()) {
                $setting_file = 'C:\Windows\aws_s3sync_php.php';
            }
            $option = array(
                'access_key' => $this->interactiveShell('please input i am access_key..'),
                'secret_key' => $this->interactiveShell('please input i am secret_key..'),
            );

            if (is_file($setting_file)) {
                while ($y = $this->interactiveShell('Over write '.$setting_file.'? Y/n')) {
                    if (strtolower($y) === 'n' || strtolower($y) === 'no' ) {
                        die;
                    }
                    if ($y === 'Y' || strtolower($y) === 'yes' ) {
                        break;
                    }
                }
            }

            umask(0);
            file_put_contents($setting_file, "<?php\n return ".var_export($option, true).";\n");
            $this->stdout("Create:{$setting_file}");
            die;
        }


        $this->command_options['dry_run'] = array_key_exists('dry-run', $cmd_options) || array_key_exists('dry_run', $cmd_options) || array_key_exists('n', $cmd_options);
        $this->command_options['times']   = array_key_exists('times', $cmd_options) || array_key_exists('t', $cmd_options);
        $this->command_options['update']   = array_key_exists('update', $cmd_options) || array_key_exists('u', $cmd_options);
        $this->command_options['quiet']   = array_key_exists('quiet', $cmd_options) || array_key_exists('q', $cmd_options);
        $this->command_options['delete']   = array_key_exists('delete', $cmd_options) || array_key_exists('del', $cmd_options);

        if (array_key_exists('access_key', $cmd_options)) {
            $this->command_options['s3']['access_key'] = $cmd_options['access_key'];
        }
        if (array_key_exists('secret_key', $cmd_options)) {
            $this->command_options['s3']['secret_key'] = $cmd_options['secret_key'];
        }

        $this->command_options['acl'] = CannedAcl::PRIVATE_ACCESS;

        if (array_key_exists('acl', $cmd_options)) {
            switch (strtolower($cmd_options['acl'])) {
            case 'private':
            case 'private_access':
            case 'private-access':
                $this->command_options['acl'] = CannedAcl::PRIVATE_ACCESS;
                break;
            case 'public-read':
            case 'public_read':
                $this->command_options['acl'] = CannedAcl::PUBLIC_READ;
                break;
            case 'public_read_write':
            case 'public-read-write':
                $this->command_options['acl'] = CannedAcl::PUBLIC_READ_WRITE;
                break;
            case 'bucket_owner_read':
            case 'bucket-owner-read':
                $this->command_options['acl'] = CannedAcl::BUCKET_OWNER_READ;
                break;
            case 'bucket_owner_full_control':
            case 'bucket-owner-full-control':
                $this->command_options['acl'] = CannedAcl::BUCKET_OWNER_FULL_CONTROL;
                break;
            case 'authenticated_read':
            case 'authenticated-read':
                $this->command_options['acl'] = CannedAcl::AUTHENTICATED_READ;
                break;
            }
        }

        $this->command_options['is_unix'] = DIRECTORY_SEPARATOR === '/';


        if (!isset($argv[2])) {
            $this->help();
            return;
        }

        $e_path = array_pop($argv);
        $a_path = array_pop($argv);

        $reg = "^s3sync://([^@]+)@(.*)";
        if ($a_path === 'ls' && mb_ereg($reg, $e_path, $match)) {
            $bucket          = $match[1];
            $s3_sync_dir     = $match[2];
            $this->command_options['s3']['bucket'] = $bucket;
            $this->remoteList($s3_sync_dir);
        } elseif (mb_ereg($reg, $a_path, $match)) {
            $root_dir_name   = $e_path;
            $bucket          = $match[1];
            $s3_sync_dir     = $match[2];
            if (!is_dir($root_dir_name)) {
                throw new exception('dist dir is invalid.');
            }
            $this->command_options['s3']['bucket'] = $bucket;
            $this->stdout("pull:$root_dir_name => $s3_sync_dir");
            $this->pull($root_dir_name, $s3_sync_dir);
        } elseif (mb_ereg($reg, $e_path, $match)) {
            $root_dir_name   = $a_path;
            $bucket          = $match[1];
            $s3_sync_dir     = $match[2];
            if (!is_dir($root_dir_name)) {
                throw new exception('src dir is invalid.');
            }
            $this->command_options['s3']['bucket'] = $bucket;

            $this->stdout("push:$root_dir_name => $s3_sync_dir");
            $this->push($root_dir_name, $s3_sync_dir);
        } else{
            $this->help();
            return;
        }
    }
    /* ----------------------------------------- */

    /**
     * +-- Windowsかどうか
     *
     * @access      public
     * @return bool
     * @codeCoverageIgnore
     */
    public function isWin()
    {
        return DIRECTORY_SEPARATOR === '\\';
    }
    /* ----------------------------------------- */

    /**
     * +-- 色つきecho
     *
     * @access      public
     * @param  var_text $text
     * @param  var_text $color
     * @return void
     * @codeCoverageIgnore
     */
    public function cecho($text, $color)
    {
        if ($this->command_options['quiet']) {
            return;
        }
        if ($this->isWin()) {
            $this->ncecho($text);

            return;
        }

        $cmd = 'echo -e "\e[3'.$color.'m'.escapeshellarg($text).'\e[m"';
        echo `$cmd`;
    }

    /* ----------------------------------------- */

    /**
     * +-- 色なしecho
     *
     * @access      public
     * @param  var_text $text
     * @return void
     * @codeCoverageIgnore
     */
    public function ncecho($text)
    {
        if ($this->command_options['quiet']) {
            return;
        }
        if ($this->isWin()) {
            $text = mb_convert_encoding($text, 'SJIS-win', 'utf8');
        }

        echo $text;
    }

    /* ----------------------------------------- */



    /**
     * +-- quietモードかどうか
     *
     * @access      public
     * @return bool
     * @codeCoverageIgnore
     */
    public function isQuiet()
    {
        static $is_quiet;
        if (empty($is_quiet)) {
            $is_quiet = $this->isOption('-q') || $this->isOption('--quiet');
        }
        return $is_quiet;
    }
    /* ----------------------------------------- */


    /**
     * +-- データを送る
     *
     * @access      protected
     * @param       var_text $root_dir_name
     * @param       var_text $s3_sync_dir
     * @return      void
     */
    protected function push($root_dir_name, $s3_sync_dir)
    {
        $root_dir_name = realpath($root_dir_name).DIRECTORY_SEPARATOR;
        $s3_sync_dir = $this->createS3SyncDir($s3_sync_dir);


        $s3_file_list = [];
        if ($this->command_options['update'] || $this->command_options['delete']) {
            $option = array('Bucket' => $this->command_options['s3']['bucket']);
            if (!empty($s3_sync_dir) && $s3_sync_dir !== '/') {
                $option['Prefix'] = $s3_sync_dir;
            }
            $iterators = $this->getClient()->getListObjectsIterator($option);
            foreach ($iterators as $item) {
                $s3_file_list[$item['Key']] = $item;
            }
        }


        $dist_file_list = [];
        foreach ($this->getFileList($root_dir_name) as $file_path) {
            $dist_file = $s3_sync_dir.str_replace($root_dir_name, '', $file_path);
            if (!$this->command_options['is_unix']) {
                $dist_file = str_replace(DIRECTORY_SEPARATOR, '/', $dist_file);
            }
            $dist_file = mb_ereg_replace('^/', '', $dist_file);

            $dist_file_list[$dist_file] = true;

            if (isset($s3_file_list[$dist_file]) || isset($s3_file_list['/'.$dist_file])) {
                $item = isset($s3_file_list[$dist_file]) ? $s3_file_list[$dist_file] : $s3_file_list['/'.$dist_file];
                $last_modified = strtotime($item['LastModified']);
                if ($this->command_options['update'] && filemtime($file_path) <= $last_modified && filesize($file_path) == $item['Size']) {
                    continue;
                }
            }

            $this->stdout($file_path .' => '.$s3_sync_dir.str_replace($root_dir_name, '', $file_path));
            if ($this->command_options['dry_run']) {
                continue;
            }
            $this->putObject($dist_file, $file_path);
        }


        if ($this->command_options['delete']) {
            foreach (array_reverse($s3_file_list) as $dist_file => $item) {
                if (!isset($dist_file_list[$dist_file]) && !isset($dist_file_list['/'.$dist_file])) {
                    $option = array('Bucket' => $this->command_options['s3']['bucket']);
                    $option['Key'] = $this->command_options['is_unix'] ? $item['Key'] : str_replace(DIRECTORY_SEPARATOR, '/', $item['Key']);
                    $this->stdout('delete '.$item['Key']);
                    if ($this->command_options['dry_run']) {
                        continue;
                    }
                    $this->getClient()->deleteObject($option);
                }
            }
        }

    }
    /* ----------------------------------------- */

    protected function remoteList($s3_sync_dir)
    {
        $option = array('Bucket' => $this->command_options['s3']['bucket']);
        if (!empty($s3_sync_dir) && $s3_sync_dir !== '/') {
            $option['Prefix'] = $s3_sync_dir;
        }

        $Client = $this->getClient();
        $iterators = $Client->getListObjectsIterator($option);
        foreach ($iterators as $item) {
            $this->stdout($item['Key']);
        }

    }

    /**
     * +-- データを持ってくる
     *
     * @access      protected
     * @param       var_text $root_dir_name
     * @param       var_text $s3_sync_dir
     * @return      void
     */
    protected function pull($root_dir_name, $s3_sync_dir)
    {
        umask(0);
        $root_dir_name = realpath($root_dir_name).DIRECTORY_SEPARATOR;

        $s3_sync_dir = $this->createS3SyncDir($s3_sync_dir);



        $option = array('Bucket' => $this->command_options['s3']['bucket']);
        if (!empty($s3_sync_dir) && $s3_sync_dir !== '/') {
            $option['Prefix'] = $s3_sync_dir;
        }

        $Client = $this->getClient();
        $iterators = $Client->getListObjectsIterator($option);

        $dist_file_list = [];

        // イテレータの中から一つづつ取得
        foreach ($iterators as $item) {
            // ディレクトリ作成
            if ($item['Size'] == 0 && substr($item['Key'], -1, 1) === '/') {
                $dist_dir = $root_dir_name.mb_substr($item['Key'], mb_strlen($s3_sync_dir) - 1);
                if (!is_dir($dist_dir)) {
                    $this->stdout('create dir '.$root_dir_name.$item['Key']);
                    if ($this->command_options['dry_run']) {
                        continue;
                    }
                    mkdir($root_dir_name.$item['Key'], 0777, true);
                }
                continue;
            }
            if (mb_ereg('(.*/)([^/]+)$',  $item['Key'], $file_match)) {
                if (!is_dir($root_dir_name.$file_match[1])) {
                    $this->stdout('create dir '.$root_dir_name.$file_match[1]);
                    if (!$this->command_options['dry_run']) {
                        mkdir($root_dir_name.$file_match[1], 0777, true);
                    }
                }
            }


            $last_modified = strtotime($item['LastModified']);
            $dist_file = $this->createLocalPath($root_dir_name, mb_substr($item['Key'], mb_strlen($s3_sync_dir) - 1));

            $dist_file_list[$dist_file] = true;

            if ($this->command_options['update'] && is_file($dist_file)) {
                if (filemtime($dist_file) >= $last_modified && filesize($dist_file) == $item['Size']) {
                    continue;
                }
            }

            $this->stdout($item['Key'].' => '. $dist_file);
            if ($this->command_options['dry_run']) {
                continue;
            }

            $contents = (string)$this->getObject($item['Key']);
            file_put_contents($dist_file, $contents);
            if ($this->command_options['times']) {
                touch($dist_file, $last_modified, $last_modified);
            }
        }


        if ($this->command_options['delete']) {
            foreach ($this->getFileList($root_dir_name) as $dist_file) {
                if (!isset($dist_file_list[$dist_file])) {
                    $this->stdout('delete '.$dist_file);
                    if ($this->command_options['dry_run']) {
                        continue;
                    }
                    unlink($dist_file);
                }
            }
        }

    }
    /* ----------------------------------------- */

    /**
     * +-- 標準出力
     *
     * @access      protected
     * @param       var_text $str
     * @return      void
     */
    protected function stdout($str)
    {
        if ($this->command_options['quiet']) {
            return;
        }
        if ($this->command_options['dry_run']) {
            echo "[DRY]";
        }
        echo "{$str}\n";
    }
    /* ----------------------------------------- */

    /**
     * +--
     *
     * @access      public
     * @param       var_text $root_dir_name
     * @return      array
     */
    protected function getFileList($root_dir_name)
    {
        $res = [];
        foreach ($this->globRecursive(realpath($root_dir_name).DIRECTORY_SEPARATOR.'*') as $file_path) {
            if (is_file($file_path)) {
                $res[] = $file_path;
            }
        }
        return $res;

    }
    /* ----------------------------------------- */

    /**
     * +--
     *
     * @access      public
     * @param       var_text $pattern
     * @param       var_text $flags OPTIONAL:0
     * @return      array
     */
    protected function globRecursive($pattern, $flags = 0)
    {
        $files = glob($pattern, $flags);

        foreach (glob(dirname($pattern).DIRECTORY_SEPARATOR.'*', GLOB_ONLYDIR|GLOB_NOSORT) as $dir) {
            $files = array_merge($files, $this->globRecursive($dir.DIRECTORY_SEPARATOR.basename($pattern), $flags));
        }

        return $files;
    }
    /* ----------------------------------------- */

    /**
     * +-- S3クライアントを返す
     *
     * @access      protected
     * @return      object
     */
    protected function getClient()
    {
        static $cached_client = null;
        if (!empty($cached_client)) {
            return $cached_client;
        }

        $setting = $this->command_options['s3'];

        $client = Aws::factory(array(
            'key' => $setting['access_key'],
            'secret' => $setting['secret_key'],
            'region' => Region::AP_NORTHEAST_1,
        ))->get('s3');

        $cached_client = $client;

        return $client;
    }
    /* ----------------------------------------- */

    /**
     * +-- オブジェクトのPUT
     *
     * @access      public
     * @param       var_text $file_name
     * @param       var_text $file_path
     * @param       var_text $content_type OPTIONAL:null
     * @return      void
     */
     protected function putObject($file_name, $file_path, $content_type = null)
     {
         $file_name = strtr($file_name, array('//' => '/'));
         $file_name = mb_ereg_replace('^/+', '', $file_name);

         $file_path = strtr($file_path, array('//' => '/'));

        $params = array(
            'Bucket'    => $this->command_options['s3']['bucket'],
            'Key'       => $this->command_options['is_unix'] ? $file_name : str_replace(DIRECTORY_SEPARATOR, '/', $file_name),
            'Body'      => $this->EntityBodyFactory($file_path),
            'ACL'       => $this->command_options['acl'],
            'MetaData' => array(
                'Cache-Control' => 'max-age=86400',
            ),
        );

        if (!is_null($content_type)) {
            $params['ContentType'] = $content_type;
        }


        return $this->getClient()->putObject($params);
     }
     /* ----------------------------------------- */


    /**
     * +-- オブジェクトのGet
     *
     * @access      public
     * @param       var_text $file_name
     * @return      void
     */
     protected function getObject($file_name)
     {
        $params = array(
            'Bucket'    => $this->command_options['s3']['bucket'],
            'Key'       => $file_name,
        );
        return $this->getClient()->getObject($params);
     }
     /* ----------------------------------------- */


    protected function createLocalPath($root_dir_name, $s3_key)
    {
        if ($this->command_options['is_unix']) {
            return $root_dir_name.$s3_key;
        }
        return $root_dir_name.str_replace('/', DIRECTORY_SEPARATOR, $s3_key);
    }

    protected function createS3SyncDir($s3_sync_dir)
    {
        if (substr($s3_sync_dir, -1, 1) !== '/') {
            $s3_sync_dir .= '/';
        }
        if (substr($s3_sync_dir, -1, 1) !== '/') {
            $s3_sync_dir = mb_ereg_replace('^/', '', $s3_sync_dir);
        }
        return $s3_sync_dir;
    }

    /**
     * +-- EntityBodyを作る
     *
     * @access      protected
     * @param       var_text $file_path
     * @return      EntityBody
     */
    protected function EntityBodyFactory($file_path)
    {
        return EntityBody::factory(fopen($file_path, 'r'));
    }
    /* ----------------------------------------- */

    /**
     * +-- 対話シェル
     *
     * @access      protected
     * @param  var_text    $shell_message
     * @param  bool|string $using_default OPTIONAL:false
     * @return string
     * @codeCoverageIgnore
     */
    protected function interactiveShell($shell_message, $using_default = false)
    {
        if (is_array($shell_message)) {
            $shell_message = join("\n", $shell_message);
        }
        $shell_message .= "\n";
        while (true) {
            $this->ncecho($shell_message);
            $this->ncecho(':');
            $res = trim(fgets(STDIN, 1000));
            if ($res === '') {
                if ($using_default === false) {
                    continue;
                }
                $res = $using_default;
            }

            break;
        }
        return $res;
    }
    /* ----------------------------------------- */


    /**
     * +-- マニュアルを表示
     *
     * @access      protected
     * @return      void
     */
    protected function help()
    {
echo "
S3Sync(",self::VERSION,")

NAME
       S3Sync — AWS S3 fast file-copying tool.

SYNOPSIS
         Pull: S3Sync [OPTIONS] s3sync://[bucket]@[SRC] [DEST]
         Push: S3Sync [OPTIONS] [SRC] s3sync://[bucket]@[DEST]
OPTIONS SUMMARY
        -q, --quiet                             suppress non-error messages
        -u, --update                            skip files that are newer on the receiver
        -n, --dry-run                           perform a trial run with no changes made
        -t, --times                             preserve modification times
            --del                               an alias for --delete-during
            --delete                            delete extraneous files from dest dirs
            --access_key <IAM access_key>       set IAM access_key
            --secret_key <IAM secret_key>       set IAM access_key
            --acl <Access Control Type>         set Access Control Type
            --init                              setup of S3sync

<Access Control Type>
        authenticated_read
        bucket_owner_full_control
        bucket_owner_read
        private_access
        public_read
        public_read_write

";
    }
    /* ----------------------------------------- */
}
