<?php
/**
 * Created by PhpStorm.
 * User: heqichang
 * Date: 14/12/30
 * Time: 下午5:50
 */

namespace BH365;
/**
 * 生成缩略图通用类
 * Class BH_thumb
 * @package BH365
 */

class BH_thumb {

    private function __construct() {

    }

    /**
     * 获取缩略图唯一实例
     * @return BH_thumb  缩略图实例
     */
    public static function instance() {
        static $obj;
        if(empty($obj)) {
            $obj = new self();
        }
        return $obj;
    }

    /**
     * 生成缩略图
     * @param $srcFile      图像原始文件
     * @param $destFile     图像目标文件，如果传null则直接输出到浏览器
     * @param $dstW         目标图像宽度
     * @param $dstH         目标图像高度
     * @param null $cenTer  是否按比例缩放，如果设为true，多余部分会被截断
     * @return bool         是否正常生成缩略图
     */
    public function MakeThumb($srcFile, &$destFile, $dstW, $dstH, $cenTer = null) {

        if(!file_exists($srcFile)) {
            return false;
        }

        $minitemp = $this->GetThumbInfo($srcFile, $dstW, $dstH, $cenTer);
        list($imagecreate, $imagecopyre) = $this->GetImagecreate($minitemp['type']);
        if (empty($minitemp) || !$imagecreate) return false;
        //if ((empty($sameFile) && $dstFile === $srcFile) || empty($minitemp) || !$imagecreate) return false;
        //!empty($sameFile) && $dstFile = $srcFile;
        $imgwidth = $minitemp['width'];
        $imgheight = $minitemp['height'];
        $srcX = $srcY = 0;
        if (!empty($cenTer)) {
            $dsDivision = $imgheight / $imgwidth;
            $fixDivision = $dstH / $dstW;
            if ($dsDivision > $fixDivision) {
                $tmpimgheight = $imgwidth * $fixDivision;
                $srcY = round(($imgheight - $tmpimgheight) / 2);
                $imgheight = $tmpimgheight;
            } else {
                $tmpimgwidth = $imgheight / $fixDivision;
                $srcX = round(($imgwidth - $tmpimgwidth) / 2);
                $imgwidth = $tmpimgwidth;
            }
        }
        $dstX = $dstY = 0;
        $thumb = $imagecreate($minitemp['dstW'], $minitemp['dstH']);

        //var_dump($thumb);
        if (function_exists('ImageColorAllocate') && function_exists('ImageColorTransparent')) {
            //背景透明处理
            $black = ImageColorAllocate($thumb,0,0,0);
            $bgTransparent = ImageColorTransparent($thumb,$black);
        }

        $imagecopyre($thumb, $minitemp['source'], $dstX, $dstY, $srcX, $srcY, $minitemp['dstW'], $minitemp['dstH'], $imgwidth, $imgheight);
        $this->MakeImage($minitemp['type'], $thumb, $destFile, 90);
        imagedestroy($thumb);
        return true;
    }


    // 按比例缩放
    private function GetThumbInfo($srcFile, $dstW, $dstH, $cenTer = null) {
        $imgdata = array();
        $imgdata = $this->GetImgInfo($srcFile);
        if (empty($imgdata) || (($dstW && $imgdata['width'] <= $dstW) && ($dstH && $imgdata['height'] <= $dstH))) return false;

        if (empty($dstW) && $dstH > 0 && $imgdata['height'] > $dstH) {
            if (!empty($cenTer)) {
                $imgdata['dstW'] = $imgdata['dstH'] = $dstH;
            } else {
                $imgdata['dstH'] = $dstH;
                $imgdata['dstW'] = round($dstH / $imgdata['height'] * $imgdata['width']);
            }
        } elseif (empty($dstH) && $dstW > 0 && $imgdata['width'] > $dstW) {
            if (!empty($cenTer)) {
                $imgdata['dstW'] = $imgdata['dstH'] = $dstW;
            } else {
                $imgdata['dstW'] = $dstW;
                $imgdata['dstH'] = round($dstW / $imgdata['width'] * $imgdata['height']);
            }
        } elseif ($dstW > 0 && $dstH > 0) {
            if (($imgdata['width'] / $dstW) < ($imgdata['height'] / $dstH)) {
                if (!empty($cenTer)) {
                    $imgdata['dstW'] = $dstW;
                    $imgdata['dstH'] = $dstH;
                } else {
                    $imgdata['dstW'] = round($dstH / $imgdata['height'] * $imgdata['width']);
                    $imgdata['dstH'] = $dstH;
                }
            } elseif (($imgdata['width'] / $dstW) > ($imgdata['height'] / $dstH)) {
                if (!empty($cenTer)) {
                    $imgdata['dstW'] = $dstW;
                    $imgdata['dstH'] = $dstH;
                } else {
                    $imgdata['dstW'] = $dstW;
                    $imgdata['dstH'] = round($dstW / $imgdata['width'] * $imgdata['height']);
                }
            } else {
                $imgdata['dstW'] = $dstW;
                $imgdata['dstH'] = $dstH;
            }
        } else {
            return false;
        }
        return $imgdata;
    }

    private function GetImgInfo($srcFile) {
        $imgdata = (array) $this->GetImgSize($srcFile);
        if ($imgdata['type'] == 1) {
            $imgdata['type'] = 'gif';
        } elseif ($imgdata['type'] == 2) {
            $imgdata['type'] = 'jpeg';
        } elseif ($imgdata['type'] == 3) {
            $imgdata['type'] = 'png';
        } elseif ($imgdata['type'] == 6) {
            $imgdata['type'] = 'bmp';
        } else {
            return false;
        }
        if (empty($imgdata) || !function_exists('imagecreatefrom' . $imgdata['type'])) {
            return false;
        }
        $imagecreatefromtype = 'imagecreatefrom' . $imgdata['type'];
        $imgdata['source'] = $imagecreatefromtype($srcFile);
        !$imgdata['width'] && $imgdata['width'] = imagesx($imgdata['source']);
        !$imgdata['height'] && $imgdata['height'] = imagesy($imgdata['source']);
        return $imgdata;
    }

    private function GetImgSize($srcFile, $srcExt = null) {
        empty($srcExt) && $srcExt = strtolower(substr(strrchr($srcFile, '.'), 1));
        $srcdata = array();
        $exts = array('jpg', 'jpeg', 'jpe', 'jfif');
        if (function_exists('read_exif_data') && in_array($srcExt, $exts)) {
            $datatemp = @read_exif_data($srcFile);
            $srcdata['width'] = $datatemp['COMPUTED']['Width'];
            $srcdata['height'] = $datatemp['COMPUTED']['Height'];
            $srcdata['type'] = 2;
            unset($datatemp);
        }
        !$srcdata['width'] && list($srcdata['width'], $srcdata['height'], $srcdata['type']) = @getimagesize($srcFile);
        if (!$srcdata['type'] || ($srcdata['type'] == 1 && in_array($srcExt, $exts))) { //noizy fix
            return false;
        }
        return $srcdata;
    }



    private function GetImagecreate($imagetype) {
        if ($imagetype != 'gif' && function_exists('imagecreatetruecolor') && function_exists('imagecopyresampled')) {
            return array(
                'imagecreatetruecolor',
                'imagecopyresampled'
            );
        } elseif (function_exists('imagecreate') && function_exists('imagecopyresized')) {
            return array(
                'imagecreate',
                'imagecopyresized'
            );
        } else {
            return array();
        }
    }

    private function MakeImage($type, $image, $filename, $quality = 75) {
        $makeimage = 'image' . $type;
        if (!function_exists($makeimage)) {
            return false;
        }
        if ($type == 'jpeg') {
            $makeimage($image, $filename, $quality);
        } else {
            $makeimage($image, $filename);
        }
        return true;
    }


}
