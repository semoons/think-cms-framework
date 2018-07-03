<?php
namespace cms;

class Validate extends \think\Validate
{

    /**
     * 实例
     *
     * @var unknown
     */
    protected static $instances = [];

    /**
     * key
     *
     * @var unknown
     */
    protected $instance_key;

    /**
     * 获取实例
     *
     * @param array $option            
     * @return self
     */
    public static function instance(array $rules = [], $message = [])
    {
        $key = md5(get_called_class() . serialize($rules) . serialize($message));
        if (! isset(static::$instances[$key])) {
            $instance = new static($rules, $message);
            $instance->instance_key = $key;
            static::$instances[$key] = $instance;
        }
        return static::instanceGet($key);
    }

    /**
     * 根据key获取实例
     *
     * @param string $key            
     * @return self
     */
    public static function instanceGet($key)
    {
        return isset(static::$instances[$key]) ? static::$instances[$key] : null;
    }

    /**
     * 获取key
     *
     * @return string
     */
    public function getInstanceKey()
    {
        return $this->instance_key;
    }
}