<?php

namespace common\widgets\kindeditor;

use Yii;
use yii\base\Action;
use yii\helpers\Url;

class KindEditorAction extends Action
{
    /**
     * 根目录路径，可以指定绝对路径，比如 /var/www/attached/
     * @var string
     */
    public $rootPath = '@public/uploads';

    /**
     * 根目录URL，可以指定绝对路径，比如 http://www.yoursite.com/attached/
     * @var string
     */
    public $rootUrl = '/uploads';

    /**
     * 文件保存子目录路径
     * @var array
     */
    public $subPath = ['date', 'Ym/d'];

    /**
     * 定义允许上传的文件扩展名
     * @var array
     */
    public $extensions = [
        'image' => ['gif', 'jpg', 'jpeg', 'png', 'bmp'],
        'flash' => ['swf', 'flv'],
        'media' => ['swf', 'flv', 'mp3', 'wma', 'mid', 'avi', 'mp4'],
        'file' => ['doc', 'docx', 'xls', 'xlsx', 'ppt', 'txt', 'zip', 'rar', 'gz', 'bz2'],
    ];

    /**
     * 排序
     * @var string
     */
    public $order = 'name';

    /**
     * the maximum number of bytes required for the uploaded file.
     * 最大文件大小
     * @var int
     */
    private $_maxSize = 1000000;

    public function run()
    {
        $this->handAction();
    }

    public function handAction()
    {
        //获得action 动作
        $action = Yii::$app->request->get('action');
        switch ($action) {
            case 'fileManagerJson':
                $this->fileManagerJsonAction();
                break;
            case 'uploadJson':
                $this->UploadJsonAction();
                break;
            default:
                break;
        }
    }

    public function fileManagerJsonAction()
    {
        //根目录路径，可以指定绝对路径，比如 /var/www/attached/
        $root_path = rtrim(Yii::getAlias($this->rootPath), '\\/') . '/';
        //根目录URL，可以指定绝对路径，比如 http://www.yoursite.com/attached/
        $root_url = rtrim(Yii::getAlias($this->rootUrl), '\\/') . '/';
        if (!file_exists($root_path)) {
            mkdir($root_path, 0755, true);
        }
        //图片扩展名
        $ext_arr = !empty($this->extensions['image']) ? $this->extensions['image'] : [];
        //目录名
        $dir_name = empty($_GET['dir']) ? '' : trim($_GET['dir']);
        if ($dir_name !== '' && !isset($this->extensions[$dir_name])) {
            echo "Invalid Directory name.";
            exit;
        }
        if ($dir_name !== '') {
            $root_path .= $dir_name . "/";
            $root_url .= $dir_name . "/";
            if (!file_exists($root_path)) {
                mkdir($root_path, 0755);
            }
        }

        //根据path参数，设置各路径和URL
        if (empty($_GET['path'])) {
            $current_path = realpath($root_path) . '/';
            $current_url = $root_url;
            $current_dir_path = '';
            $moveup_dir_path = '';
        } else {
            $current_path = realpath($root_path) . '/' . $_GET['path'];
            $current_url = $root_url . $_GET['path'];
            $current_dir_path = $_GET['path'];
            $moveup_dir_path = preg_replace('/(.*?)[^\/]+\/$/', '$1', $current_dir_path);
        }
        //echo realpath($root_path);
        //排序形式，name or size or type
        $this->order = empty($_GET['order']) ? 'name' : strtolower($_GET['order']);

        //不允许使用..移动到上一级目录
        if (preg_match('/\.\./', $current_path)) {
            echo 'Access is not allowed.';
            exit;
        }
        //最后一个字符不是/
        if (!preg_match('/\/$/', $current_path)) {
            echo 'Parameter is not valid.';
            exit;
        }
        //目录不存在或不是目录
        if (!file_exists($current_path) || !is_dir($current_path)) {
            echo 'Directory does not exist.';
            exit;
        }

        //遍历目录取得文件信息
        $file_list = array();
        if ($handle = opendir($current_path)) {
            $i = 0;
            while (false !== ($filename = readdir($handle))) {
                if ($filename{0} == '.') continue;
                $file = $current_path . $filename;
                if (is_dir($file)) {
                    $file_list[$i]['is_dir'] = true; //是否文件夹
                    $file_list[$i]['has_file'] = (count(scandir($file)) > 2); //文件夹是否包含文件
                    $file_list[$i]['filesize'] = 0; //文件大小
                    $file_list[$i]['is_photo'] = false; //是否图片
                    $file_list[$i]['filetype'] = ''; //文件类别，用扩展名判断
                } else {
                    $file_list[$i]['is_dir'] = false;
                    $file_list[$i]['has_file'] = false;
                    $file_list[$i]['filesize'] = filesize($file);
                    $file_list[$i]['dir_path'] = '';
                    $file_ext = strtolower(pathinfo($file, PATHINFO_EXTENSION));
                    $file_list[$i]['is_photo'] = in_array($file_ext, $ext_arr);
                    $file_list[$i]['filetype'] = $file_ext;
                }
                $file_list[$i]['filename'] = $filename; //文件名，包含扩展名
                $file_list[$i]['datetime'] = date('Y-m-d H:i:s', filemtime($file)); //文件最后修改时间
                $i++;
            }
            closedir($handle);
        }

        usort($file_list, [$this, 'cmp_func']);

        $result = [];
        //相对于根目录的上一级目录
        $result['moveup_dir_path'] = $moveup_dir_path;
        //相对于根目录的当前目录
        $result['current_dir_path'] = $current_dir_path;
        //当前目录的URL
        $result['current_url'] = $current_url;
        //文件数
        $result['total_count'] = count($file_list);
        //文件列表数组
        $result['file_list'] = $file_list;

        //输出JSON字符串
        header('Content-type: application/json; charset=UTF-8');
        echo json_encode($result);
        exit;
    }

    /**
     * 排序算法
     * @param array $a
     * @param array $b
     * @return int
     */
    protected function cmp_func($a, $b)
    {
        $order = $this->order;
        if ($a['is_dir'] && !$b['is_dir']) {
            return -1;
        } else if (!$a['is_dir'] && $b['is_dir']) {
            return 1;
        } else {
            if ($order == 'size') {
                if ($a['filesize'] > $b['filesize']) {
                    return 1;
                } else if ($a['filesize'] < $b['filesize']) {
                    return -1;
                } else {
                    return 0;
                }
            } else if ($order == 'type') {
                return strcmp($a['filetype'], $b['filetype']);
            } else {
                return strcmp($a['filename'], $b['filename']);
            }
        }
    }

    public function UploadJsonAction()
    {
        //文件保存目录路径
        $save_path = rtrim(Yii::getAlias($this->rootPath), '\\/') . '/';
        //文件保存目录URL
        $save_url = rtrim(Yii::getAlias($this->rootUrl), '\\/') . '/';
        //定义允许上传的文件扩展名
        $ext_arr = $this->extensions;
        //最大文件大小
        $max_size = $this->getMaxSize();
        //PHP上传失败
        if (!empty($_FILES['imgFile']['error'])) {
            switch ($_FILES['imgFile']['error']) {
                case '1':
                    $error = '超过php.ini允许的大小。';
                    break;
                case '2':
                    $error = '超过表单允许的大小。';
                    break;
                case '3':
                    $error = '图片只有部分被上传。';
                    break;
                case '4':
                    $error = '请选择图片。';
                    break;
                case '6':
                    $error = '找不到临时目录。';
                    break;
                case '7':
                    $error = '写文件到硬盘出错。';
                    break;
                case '8':
                    $error = 'File upload stopped by extension。';
                    break;
                case '999':
                default:
                    $error = '未知错误。';
            }
            $this->alert($error);
        }

        //有上传文件时
        if (empty($_FILES) === false) {
            //原文件名
            $file_name = $_FILES['imgFile']['name'];
            //服务器上临时文件名
            $tmp_name = $_FILES['imgFile']['tmp_name'];
            //文件大小
            $file_size = $_FILES['imgFile']['size'];
            //检查文件名
            if (!$file_name) {
                $this->alert("请选择文件。");
            }
            //检查目录
            if (@is_dir($save_path) === false) {
                $this->alert("上传目录不存在。");
            }
            //检查目录写权限
            if (@is_writable($save_path) === false) {
                $this->alert("上传目录没有写权限。");
            }
            //检查是否已上传
            if (@is_uploaded_file($tmp_name) === false) {
                $this->alert("上传失败。");
            }
            //检查文件大小
            if ($file_size > $max_size) {
                $this->alert("上传文件大小超过限制。");
            }
            //检查目录名
            $dir_name = empty($_GET['dir']) ? 'image' : trim($_GET['dir']);
            if (empty($ext_arr[$dir_name])) {
                $this->alert("目录名不正确。");
            }
            //获得文件扩展名
            $temp_arr = explode(".", $file_name);
            $file_ext = array_pop($temp_arr);
            $file_ext = trim($file_ext);
            $file_ext = strtolower($file_ext);
            //检查扩展名
            if (in_array($file_ext, $ext_arr[$dir_name]) === false) {
                $this->alert("上传文件扩展名是不允许的扩展名。\n只允许" . implode(",", $ext_arr[$dir_name]) . "格式。");
            }
            //创建文件夹
            if ($dir_name !== '') {
                $save_path .= $dir_name . "/";
                $save_url .= $dir_name . "/";
            }
            $ymd = trim($this->resolveSubPath($this->subPath, $file_name));
            if ($ymd !== '') {
                $ymd = rtrim($ymd, '\\/');
                $save_path .= $ymd . "/";
                $save_url .= $ymd . "/";
            }
            if (!file_exists($save_path)) {
                mkdir($save_path, 0755, true);
            }
            //新文件名
            $new_file_name = date("YmdHis") . '_' . rand(10000, 99999) . '.' . $file_ext;
            //移动文件
            $file_path = $save_path . $new_file_name;
            if (move_uploaded_file($tmp_name, $file_path) === false) {
                $this->alert("上传文件失败。");
            }
            @chmod($file_path, 0644);
            $file_url = ltrim($save_url . $new_file_name, '\\/');
            $file_url = Url::to('/' . $file_url);
            header('Content-type: text/html; charset=UTF-8');
            echo json_encode(['error' => 0, 'url' => $file_url]);
            exit;
        }
    }

    /**
     * @param string $msg
     */
    private function alert($msg)
    {
        header('Content-type: text/html; charset=UTF-8');
        echo json_encode(['error' => 1, 'message' => $msg]);
        exit;
    }

    /**
     * @return int
     */
    private function getMaxSize()
    {
        return $this->_maxSize;
    }

    /**
     * @param int $maxSize
     */
    public function setMaxSize($maxSize)
    {
        $this->_maxSize = intval($maxSize);
    }

    /**
     * @param array|\Closure|string $subPath
     * @param string $fileName
     * @return string
     */
    private function resolveSubPath($subPath, $fileName)
    {
        $path = '';
        if ($subPath instanceof \Closure) {
            $path = call_user_func($subPath, $fileName);
        } elseif (is_array($subPath)) {
            $func = array_shift($subPath);
            $params = $subPath;
            $path = call_user_func_array($func, $params);
        } elseif (is_string($subPath)) {
            $path = $subPath;
        }
        return $path;
    }
}
