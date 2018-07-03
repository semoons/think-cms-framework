<?php
namespace cms\login\driver;

use cms\Login;
use think\Cookie;
use think\Cache;

class CookieLogin extends Login
{

    /**
     * Key前缀
     *
     * @var unknown
     */
    const PREFIX_KEY = 'cookie_login_';

    /**
     * !CodeTemplates.overridecomment.nonjd!
     *
     * @see \app\common\driver\Login::storageLogin()
     */
    public function storageLogin($key, $data, $expire = 0)
    {
        $ticket = md5(time() . rand(1000, 9999) . serialize($data));
        $key = $this->getKey($key);
        
        // 设置Cookie
        Cookie::set($key, $ticket, $expire);
        
        // 缓存数据
        Cache::set($ticket, $data, $expire);
    }

    /**
     * !CodeTemplates.overridecomment.nonjd!
     *
     * @see \app\common\driver\Login::readLogin()
     */
    public function readLogin($key)
    {
        $key = $this->getKey($key);
        
        $ticket = Cookie::get($key);
        return $ticket ? Cache::get($ticket) : null;
    }

    /**
     * !CodeTemplates.overridecomment.nonjd!
     *
     * @see \app\common\driver\Login::clearLogin()
     */
    public function clearLogin($key)
    {
        $key = $this->getKey($key);
        
        // 清除Cookie
        Cookie::delete($key);
        
        // 清除Cache
        $ticket = Cookie::get($key);
        $ticket && Cache::rm($ticket);
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