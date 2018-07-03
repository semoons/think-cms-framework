<?php
namespace cms\login\driver;

use cms\Login;
use think\Session;

class SessionLogin extends Login
{

    /**
     * Key前缀
     *
     * @var unknown
     */
    const PREFIX_KEY = 'session_login_';

    /**
     * !CodeTemplates.overridecomment.nonjd!
     *
     * @see \app\common\driver\Login::storageLogin()
     */
    public function storageLogin($key, $data, $expire = 0)
    {
        $key = $this->getKey($key);
        Session::set($key, $data);
    }

    /**
     * !CodeTemplates.overridecomment.nonjd!
     *
     * @see \app\common\driver\Login::readLogin()
     */
    public function readLogin($key)
    {
        $key = $this->getKey($key);
        return Session::get($key);
    }

    /**
     * !CodeTemplates.overridecomment.nonjd!
     *
     * @see \app\common\driver\Login::clearLogin()
     */
    public function clearLogin($key)
    {
        $key = $this->getKey($key);
        Session::delete($key);
    }

    /**
     * 获取Key
     *
     * @param string $key            
     * @return string
     */
    protected function getKey($key)
    {
        return self::PREFIX_KEY . $key;
    }
}