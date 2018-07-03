<?php
namespace cms;

use think\Request;

class Common
{

    /**
     * 客户端IP
     *
     * @return string
     */
    public static function getIp()
    {
        return Request::instance()->ip();
    }

    /**
     * 客户端浏览器标识
     *
     * @return string
     */
    public static function getAgent()
    {
        return Request::instance()->server('HTTP_USER_AGENT');
    }

    /**
     * 当前操作
     *
     * @return string
     */
    public static function getCurrentAction()
    {
        $request = Request::instance();
        return $request->module() . '/' . $request->controller() . '/' . $request->action();
    }

    /**
     * 临时文件
     *
     * @param string $prefix            
     * @return string
     */
    public static function tmpFile($prefix = null)
    {
        $tmp_path = RUNTIME_PATH . 'file/';
        
        // 不存在则创建
        is_dir($tmp_path) || mkdir($tmp_path, 777, true);
        
        $prefix || $prefix = 'tmp';
        return tempnam($tmp_path, $prefix);
    }

    /**
     * 读取文件
     *
     * @param string $file_path            
     * @return string
     */
    public static function readFile($file_path)
    {
        try {
            $file = fopen($file_path, 'r');
            $content = fread($file, filesize($file_path));
            fclose($file);
            return $content;
        } catch (\Exception $e) {
            return '';
        }
    }

    /**
     * 文件大小单位转化
     *
     * @param number $size            
     * @param string $delimiter            
     * @return string
     */
    public static function formatBytes($size, $delimiter = '')
    {
        $units = [
            'B',
            'KB',
            'MB',
            'GB',
            'TB',
            'PB'
        ];
        for ($i = 0; $size >= 1024 && $i < 5; $i ++) {
            $size /= 1024;
        }
        return round($size, 2) . $delimiter . $units[$i];
    }

    /**
     * 部署文件夹
     *
     * @return string
     */
    public static function getWebPath()
    {
        static $path;
        if (empty($path)) {
            
            // 当前文件路径
            $file = Request::instance()->server('SCRIPT_NAME');
            
            // 去除文件名
            $arr = explode('/', $file);
            array_pop($arr);
            
            // 当前文件夹路径
            if (count($arr) == 0) {
                $path = '/';
            } else {
                $path = implode('/', $arr) . '/';
            }
        }
        return $path;
    }
}