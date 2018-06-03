<?php
/**
 * Created by PhpStorm.
 * User: meixinghao
 * Date: 2018/6/3
 * Time: 下午5:52
 */

class FeedTopics extends BaseModel
{
    public static $_only_cache = true;

    /**
     * @type string
     */
    private $_id;

    /**
     * @type integer
     */
    private $_feed_num;

    /**
     * @type string
     */
    private $_name;

    /**
     * @type integer
     */
    private $_created_at;

    /**
     * @type integer
     */
    private $_user_id;

    /**
     * 浏览人数
     * @type integer
     */
    private $_browse_users_num;

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
        return 'feed_topic_' . $this->user_id . '_' . uniqid();
    }

    static function getMsgDb()
    {
        $endpoint = self::config('msg_db');
        return XRedis::getInstance($endpoint);
    }
}