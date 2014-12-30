<?php
/**
 * Created by PhpStorm.
 * User: heqichang
 * Date: 14/12/30
 * Time: 上午10:22
 */

namespace BH365;
/**
 * todo: 里面使用了date()函数，需要设置date_default_timezone_set，不然会有warinning
 * 上传通用类
 * Class BH_upload
 * @package BH365
 */

class BH_upload {

    private $attach = array();
    private $default_allow_ext = array('jpg', 'jpeg', 'gif', 'png', 'swf', 'bmp', 'txt', 'zip', 'rar', 'mp3');
    private $upload_root = 'upload';
    private $timestamp;

    public function __construct() {
        $this->timestamp = $_SERVER['REQUEST_TIME'];
    }

    /**
     * 上传程序初始化
     * @param $file             $_FILES数组中的值
     * @param int $size_limit   上传文件大小限制
     * @param array $allow_ext  文件类型限制
     */
    public function init($file, $size_limit = '2m', $allow_ext = array()) {
        $this->attach['size_limit'] = $this->return_bytes($size_limit);
        if(!empty($allow_ext)) {
            $this->attach['allow_ext'] = $allow_ext;
        } else {
            $this->attach['allow_ext'] = $this->default_allow_ext;
        }
        $this->attach['size'] = intval($file['size']);
        $this->attach['name'] = htmlspecialchars(trim($file['name']), ENT_QUOTES);
        $this->attach['ext'] = $this->fileext($this->attach['name']);
        $this->attach['isimage'] = $this->is_image_mime($this->attach['type']) || $this->is_image_ext($this->attach['ext']);
        $this->attach['source'] = $file['tmp_name'];
        $this->attach['target'] = $this->get_target_filename();
    }

    /**
     * @param bool $need_auth      是否需要用户身份验证
     * @param null $auth_callback  验证callback
     * @return array               上传信息
     */
    public function save($need_auth = false, $auth_callback = null) {

        $arrry = array();

        // 用户身份验证
        if($need_auth) {
            if(!isset($auth_callback) || !call_user_func($auth_callback)) {
                $arrry[] = array('error' => '用户身份不允许上传');
                return $arrry;
            }
        }

        // 文件大小校验
        if($this->attach['size'] > $this->attach['size_limit']) {
            $arrry[] = array('error' => '文件大小大于上传限制');
            return $arrry;
        }

        // 后缀检验
        if(!empty($this->attach['allow_ext'])) {
            if(!in_array($this->attach['ext'], $this->attach['allow_ext'])) {
                $arrry[] = array('error' => '文件上传类型不允许');
                return $arrry;
            }
        }

        // 图像文件验证，防止冒充
        if($this->attach['isimage']) {
            if(!$this->check_is_image($this->attach['source'])) {
                $arrry[] = array('error' => '上传文件不是图片类型');
                return $arrry;
            }
        }

        // 上传文件
        if(!$this->save_to_local($this->attach['source'], $this->attach['target'])) {
            $arrry[] = array('error' => '上传失败');
            return $arrry;
        }

        $arrry = array('filepath' => $this->attach['target'], 'type' => $this->attach['ext']);

        return $arrry;
    }

    private function fileext($filename) {
        return addslashes(strtolower(substr(strrchr($filename, '.'), 1, 10)));
    }

    private function is_image_ext($ext) {
        static $imgext  = array('jpg', 'jpeg', 'gif', 'png', 'bmp');
        return in_array($ext, $imgext) ? 1 : 0;
    }

    private function is_image_mime($type) {
        static $img_mime = array('image/gif','image/jpeg','image/png','image/bmp','application/x-shockwave-flash');
        return in_array($type, $img_mime) ? 1 : 0;
    }

    private function check_is_image($file) {
        return getimagesize($file);
    }

    private function get_target_filename() {

        if(!is_dir($this->upload_root)) {
            $this->make_dir($this->upload_root);
        }

        $dest_dir =  $this->upload_root . '/' . date('Ym');

        if(!is_dir($dest_dir)) {
           $this->make_dir($dest_dir);
        }

        $dest_filename = md5($this->attach['name'] . $this->timestamp);
        $dest_path = $dest_dir . '/' . $dest_filename;

        return $dest_path;
    }

    private function make_dir($dir) {
        $res = true;
        if(!is_dir($dir)) {
            $res = @mkdir($dir, 0777);
        }
        return $res;
    }

    private function save_to_local($source, $target) {
        if(!$this->is_upload_file($source)) {
            $succeed = false;
        }elseif(@copy($source, $target)) {
            $succeed = true;
        }elseif(function_exists('move_uploaded_file') && @move_uploaded_file($source, $target)) {
            $succeed = true;
        }elseif (@is_readable($source) && (@$fp_s = fopen($source, 'rb')) && (@$fp_t = fopen($target, 'wb'))) {
            while (!feof($fp_s)) {
                $s = @fread($fp_s, 1024 * 512);
                @fwrite($fp_t, $s);
            }
            fclose($fp_s); fclose($fp_t);
            $succeed = true;
        }
        if($succeed)  {
            @chmod($target, 0644); @unlink($source);
        }

        return $succeed;
    }

    private function is_upload_file($source) {
        return $source && ($source != 'none') && (is_uploaded_file($source) || is_uploaded_file(str_replace('\\\\', '\\', $source)));
    }

    private function return_bytes($val) {
        $val = trim($val);
        $last = strtolower($val{strlen($val)-1});
        switch($last) {
            case 'g': $val *= 1024;
            case 'm': $val *= 1024;
            case 'k': $val *= 1024;
        }
        return $val;
    }
}
