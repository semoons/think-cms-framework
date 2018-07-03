<?php
use think\Hook;
use think\Loader;

// 注册行为，加载模块公共函数
Hook::add('app_init', '\\cms\\behavior\\LoadCommonBehavior');

// 注册命令空间
if (defined('APP_PATH')) {
    // core
    Loader::addNamespace('core', APP_PATH . 'core');
    
    // module
    Loader::addNamespace('module', APP_PATH . 'module');
}

/**
 * 接口返回
 *
 * @param number $code            
 * @param string $info            
 * @param string $data            
 * @throws \think\exception\HttpResponseException
 */
function apiReturn($code = 1, $info = '', $data = '')
{
    $res = \kucms\common\Format::formatResult($code, $info, $data);
    responseReturn($res, 'json');
}

/**
 * 输出结果
 *
 * @param mixed $data            
 * @param string $type            
 * @throws \think\exception\HttpResponseException
 */
function responseReturn($data, $type = 'auto')
{
    $type == 'auto' && $type = is_array($data) ? 'json' : 'text';
    $response = \think\Response::create($data, $type);
    throw new \think\exception\HttpResponseException($response);
}

/**
 * 退出返回
 *
 * @param mixed $data            
 */
function exitReturn($data)
{
    echo is_array($data) ? json_encode($data, JSON_UNESCAPED_UNICODE) : $data;
    exit();
}

/**
 * 跳转链接
 *
 * @param string $url            
 * @param array $param            
 * @param string $need_build            
 * @throws \think\exception\HttpResponseException
 */
function responseRedirect($url, $param = [], $need_build = true)
{
    if ($need_build) {
        $url = \think\Url::build($url, $param);
    } elseif (is_array($param) && count($param)) {
        $url .= strpos($url, '?') ? '&' : '?';
        $url .= http_build_query($param);
    }
    
    $response = new \think\Response();
    $response->header('Location', $url);
    throw new \think\exception\HttpResponseException($response);
}