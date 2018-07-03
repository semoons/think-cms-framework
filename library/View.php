<?php
namespace cms;

use think\Config;
use think\Request;

class View extends \think\View
{

    /**
     * (non-PHPdoc)
     *
     * @see \think\View::fetch()
     */
    public function fetch($template = '', $vars = [], $replace = [], $config = [], $renderContent = false)
    {
        // 路径等变量
        $this->assignView();
        
        return parent::fetch($template, $vars, $replace, $config, $renderContent);
    }

    /**
     * 路径等变量
     */
    public function assignView()
    {
        $request = Request::instance();
        $base_url = Config::get('site_base') ?  : '/';
        
        // 网站版本
        // $site_version = Config::get('site_version') ?  : date('Ymd');
        $site_version = Config::get('app_debug')==1 ? time() : Config::get('site_version') ?: date('Y-m-d');
        $this->assign('site_version', $site_version);
        
        // 前端库路径
        $lib_path = $base_url . 'lib';
        $this->assign('lib_path', $lib_path);
        
        // 静态库路径
        $static_path = $base_url . 'static';
        $this->assign('static_path', $static_path);
        
        // 当前模块路径
        $module_path = $static_path . '/' . $request->module();
        $this->assign('module_path', $module_path);
    }
}