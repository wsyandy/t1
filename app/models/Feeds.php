<?php
/**
 * Created by PhpStorm.
 * User: meixinghao
 * Date: 2018/6/3
 * Time: 下午5:52
 */

class Feeds extends BaseModel
{
    public static $_only_cache = true;

    /**
     * @type string
     */
    private $_id;

    /**
     * @type Users
     */
    private $_user;

    /**
     * 内容最多1500字
     * @type string
     */
    private $_content;

    /**
     * 位置信息
     * @type string
     */
    private $_location;

    /**
     * 图片 最多9张
     * @type string
     */
    private $_image_files = '';

    /**
     * 点赞人数
     * @type integer
     */
    private $_like_users_num = 0;

    /**
     * 踩雷人数
     * @type integer
     */
    private $_dislike_users_num = 0;

    /**
     * 分享人数
     * @type integer
     */
    private $_share_users_num;

    /**
     * 评论数量
     * @type integer
     */
    private $_comment_users_num = 0;

    /**
     * @type integer
     */
    private $_created_at;

    /**
     * @type integer
     */
    private $_updated_at;

    /**
     * @type integer
     */
    private $auth_status = AUTH_NONE;

    function beforeCreate()
    {
        $this->id = $this->generateId();
    }

    function afterCreate()
    {

    }

    static function getCacheEndPoint()
    {
        $config = self::di('config');
        $endpoints = explode(',', $config->msg_db);
        return $endpoints[0];
    }

    function generateId()
    {
        return 'feed_' . $this->user_id . '_' . uniqid();
    }

    static function getMsgDb()
    {
        $endpoint = self::config('msg_db');
        return XRedis::getInstance($endpoint);
    }
}