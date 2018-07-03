<?php
namespace cms\behavior;

class LoadCommonBehavior
{

    /**
     * 加载模块通用函数
     *
     * @param unknown $params            
     */
    public function run(&$params)
    {
        $common_dir = dirname(dirname(__DIR__)) . DS . 'common';
        if (is_dir($common_dir)) {
            foreach (scandir($common_dir) as $file) {
                if ($file == '.' || $file == '..') {
                    continue;
                } else {
                    is_file($common_dir . DS . $file) && require_once ($common_dir . DS . $file);
                }
            }
        }
    }
}