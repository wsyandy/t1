<?php


class ThirdAuths extends BaseModel
{
    /**
     * @type Users
     */
    private $_user;

    /**
     * @type ProductChannels
     */
    private $_product_channel;

    static $THIRD_NAME = [
        THIRD_AUTH_THIRD_NAME_WEIXIN => '微信登陆',
        THIRD_AUTH_THIRD_NAME_QQ => 'QQ登陆',
        THIRD_AUTH_THIRD_NAME_SINAWEIBO => '新浪微博登录',
    ];

    function isWeixin()
    {
        return THIRD_AUTH_THIRD_NAME_WEIXIN == $this->third_name;
    }

    function isQQ()
    {
        return THIRD_AUTH_THIRD_NAME_QQ == $this->third_name;
    }

    function isSinaweibo()
    {
        return THIRD_AUTH_THIRD_NAME_SINAWEIBO == $this->third_name;
    }
}