<?php
namespace cms;

use cms\View;
use think\Request;
use kucms\common\Format;

class Controller
{

    /**
     * 标题
     *
     * @var unknown
     */
    protected $site_title = '';

    /**
     * 构造函数
     */
    public function __construct()
    {
        $this->_initialize();
    }

    /**
     * 初始化
     */
    protected function _initialize()
    {}

    /**
     * 渲染模板
     *
     * @param string $template            
     * @param array $vars            
     * @param array $replace            
     * @param array $config            
     * @return string
     */
    protected function fetch($template = '', $vars = [], $replace = [], $config = [])
    {
        // 页面标题
        $this->assign('site_title', $this->site_title);
        
        return $this->view->fetch($template, $vars, $replace, $config);
    }

    /**
     * 处理成功
     *
     * @param string $msg            
     * @param string $url            
     * @param string $data            
     * @param number $wait            
     * @return mixed
     */
    protected function success($msg = '', $url = '', $data = '', $wait = 3)
    {
        return $this->jump(1, $msg, $url, $data, $wait);
    }

    /**
     * 发生错误
     *
     * @param string $msg            
     * @param string $url            
     * @param string $data            
     * @param number $wait            
     * @return mixed
     */
    protected function error($msg = '', $url = '', $data = '', $wait = 3)
    {
        return $this->jump(0, $msg, $url, $data, $wait);
    }

    /**
     * 跳转链接
     *
     * @param number $code            
     * @param string $msg            
     * @param string $url            
     * @param string $data            
     * @param number $wait            
     * @return mixed
     */
    protected function jump($code = 1, $msg = '', $url = '', $data = '', $wait = 3)
    {
        $jump = Format::formatJump($code, $msg, $url, $data, $wait);
        if (Request::instance()->isAjax()) {
            responseReturn($jump, 'json', true);
        } else {
            $this->site_title || $this->site_title = $msg;
            
            $this->assign('jump', $jump);
            
            return $this->fetch('common/jump');
        }
    }

    /**
     * 视图对象
     *
     * @return \think\View
     */
    public function getView()
    {
        empty($this->view) && $this->view = new View();
        return $this->view;
    }

    /**
     * 模板赋值
     *
     * @param string $name            
     * @param mixed $value            
     */
    public function assign($name, $value)
    {
        $this->view->assign($name, $value);
    }

    /**
     * 魔术方法
     *
     * @param string $name            
     */
    public function __get($name)
    {
        $method = 'get' . $name;
        if (method_exists($this, $method)) {
            return $this->$method();
        }
    }
}