<?php
namespace cms;

abstract class Login
{
    use \kucms\traits\Instance;

    /**
     * Session
     *
     * @var unknown
     */
    const TYPE_SESSION = 'session';

    /**
     * Cookie
     *
     * @var unknown
     */
    const TYPE_COOKIE = 'cookie';

    /**
     * 创建login对象
     *
     * @param string $type            
     * @return self
     */
    public static function create($type = self::TYPE_SESSION)
    {
        $class_name = '\\cms\\login\\driver\\' . ucfirst($type) . 'Login';
        if (class_exists($class_name)) {
            try {
                return $class_name::instance();
            } catch (\Exception $e) {}
        }
    }

    /**
     * 存储登录
     *
     * @param string $key            
     * @param mixed $data            
     * @param number $expire            
     */
    abstract public function storageLogin($key, $data, $expire = 0);

    /**
     * 读取登录
     *
     * @param string $key            
     */
    abstract public function readLogin($key);

    /**
     * 清除登录
     *
     * @param string $key            
     */
    abstract public function clearLogin($key);
}