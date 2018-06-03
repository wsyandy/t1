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
        $this->addFeedCommentList();
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

    function addFeedCommentList()
    {
        $msg_db = Feeds::getMsgDb();
        $feed = $this->feed;
        $feed_comment_list = 'feed_comment_list_' . $this->feed_id;
        $msg_db->zadd($feed_comment_list, time(), $this->id);
        $total = $msg_db->zcard($feed_comment_list);
        $feed->feed_comments_num = $total;
        $feed->update();
    }

    static function createFeedComment($user, $feed, $opts = [])
    {
        $content = fetch($opts, 'content');
        $feed_comment = new \FeedComments();
        $feed_comment->feed_id = $feed->id;
        $feed_comment->user_id = $user->id;
        $feed_comment->user = $user;
        $feed_comment->feed = $feed;
        $feed_comment->content = $content;

        if ($feed_comment->create()) {
            return $feed;
        }

        return null;
    }

    function toSimpleJson()
    {
        $user = $this->user;

        return [
            'id' => $this->id, 'content' => $this->content, 'created_at' => $this->created_at,
            'feed_id' => $this->feed_id,
            'user_id' => $this->user_id, 'avatar_small_url' => $user->avatar_small_url,
            'nickname' => $user->nickname
        ];
    }
}