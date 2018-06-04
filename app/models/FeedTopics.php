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
     * @type Users
     */
    private $_user;

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
        $user = $this->user;
        $msg_db = self::getMsgDb();
        $msg_db->zadd('user_feed_topic_list_' . $user->id, $this->created_at, $this->id);
        $this->addWaitAuthFeedTopicList();

        if (isDevelopmentEnv()) {
            $this->addFeedTopicToTotalList();
        }
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

    static function createFeedTopic($user, $opts = [])
    {
        $name = fetch($opts, 'name');
        $feed_topic = new FeedTopics();
        $feed_topic->name = $name;
        $feed_topic->user_id = $user->id;
        $feed_topic->user = $user;
        if ($feed_topic->create()) {
            return $feed_topic;
        }

        return null;
    }

    function addWaitAuthFeedTopicList()
    {
        $msg_db = self::getMsgDb();
        $msg_db->zadd('wait_auth_feed_topic_list', time(), $this->id);
    }

    function addFeedTopicToTotalList()
    {
        $user = $this->user;
        $msg_db = self::getMsgDb();
        $msg_db->zadd('feed_topic_total_list', $this->created_at, $this->id);
        $msg_db->zadd('feed_topic_total_list_product_channel' . $user->product_channel_id, $this->created_at, $this->id);
    }

    static function findFeedTopicsByPage($key, $page, $per_page = 10)
    {
        if ($page <= 1) {
            $page = 1;
        }
        $msg_db = self::getMsgDb();
        $offset = ($page - 1) * $per_page;
        $feed_ids = $msg_db->zrevrange($key, $offset, $offset + $per_page - 1);
        $total = $msg_db->zcard($key);

        $feeds = \Feeds::findByIds($feed_ids);
        $feeds = new \PaginationModel($feeds, $total, $page, $per_page);
        $feeds->clazz = 'Feeds';
        return $feeds;
    }

    static function findWaitAuthFeedTopics($page, $per_page = 10)
    {
        $key = "wait_auth_feed_topic_list";
        return self::findFeedTopicsByPage($key, $page, $per_page);
    }

    static function findTotalFeedTopics($page, $per_page = 10)
    {
        $key = "feed_topic_total_list";
        return self::findFeedTopicsByPage($key, $page, $per_page);
    }

    function toSimpleJson()
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'feed_num' => $this->feed_num,
            'browse_users_num' => $this->browse_users_num,
            'avatar_small_url' => $this->user->avatar_small_url
        ];
    }
}