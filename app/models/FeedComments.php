<?php
/**
 * Created by PhpStorm.
 * User: meixinghao
 * Date: 2018/6/3
 * Time: 下午5:52
 */

class FeedComments extends BaseModel
{
    public static $_only_cache = true;

    /**
     * @type string
     */
    private $_id;

    /**
     * @type string
     */
    private $_feed_id;

    /**
     * @type string
     */
    private $_content;

    /**
     * @type integer
     */
    private $_created_at;

    /**
     * @type integer
     */
    private $_user_id;

    /**
     * @type Feeds
     */
    private $_feed;

    /**
     * @type Users
     */
    private $_user;

    /**
     * 点赞人数
     * @type integer
     */
    private $_like_users_num = 0;


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
        return 'feed_comment_' . $this->user_id . '_' . $this->feed_id . '_' . uniqid();
    }

    static function getMsgDb()
    {
        $endpoint = self::config('msg_db');
        return XRedis::getInstance($endpoint);
    }
}