<?php
namespace cms;

class Captcha
{

    const VERIFY_PREFIX = 'verify_captcha_';

    /**
     * 校验
     *
     * @param string $code            
     * @param string $id            
     * @param array $config            
     * @return boolean
     */
    public static function checkCode($code, $id, $config = array())
    {
        $key = self::getKey($id);
        
        return captcha_check($code, $key, $config);
    }

    /**
     * 验证码二进制流
     *
     * @param string $id            
     * @param array $config            
     * @return \think\Response
     */
    public static function getCodeImage($id, $config = array())
    {
        $key = self::getKey($id);
        
        return captcha($key, $config);
    }

    /**
     * 验证码图片链接
     *
     * @param string $id            
     * @return string
     */
    public static function getCodeSrc($id)
    {
        $key = self::getKey($id);
        
        return captcha_src($key);
    }

    /**
     * 标识
     *
     * @param string $id            
     * @return string
     */
    public static function getKey($id)
    {
        if (empty($id)) {
            $id = 'common';
        }
        return self::VERIFY_PREFIX . $id;
    }
}