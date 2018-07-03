<?php
namespace cms;

class Auth
{

    /**
     * 是否公开操作
     *
     * @param array $public_action            
     * @return number
     */
    public static function isPublicAction($public_action = [])
    {
        // 当前操作
        $current_action = strtolower(Common::getCurrentAction());
        
        // 公开操作
        $public_action_patern = '#(^' . implode(')|(^', $public_action) . ')#i';
        
        return preg_match($public_action_patern, $current_action);
    }
}