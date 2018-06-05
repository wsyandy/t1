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
     * 图片张数
     * @type integer
     */
    private $_image_num = 0;

    /**
     * 音频
     * @type string
     */
    private $_voice_file;

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
     * 关注的人数
     * @type integer
     */
    private $_follow_users_num = 0;

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

    /**
     * 动态话题id
     * @type integer
     */
    private $_feed_topic_id = 0;

    /**
     * 审核人员
     * @type integer
     */
    private $_operator_id = 0;

    /**
     * @type Operators
     */
    private $_operator;

    function beforeCreate()
    {
        $this->id = $this->generateId();
    }

    function afterCreate()
    {
        $user = $this->user;
        $feed_topic = $this->feed_topic;
        $msg_db = self::getMsgDb();
        $msg_db->zadd('user_feed_list_' . $user->id, $this->created_at, $this->id);
        $user->feed_num += 1;
        $user->update();
        $this->addWaitAuthFeedList();

        if ($feed_topic) {
            $msg_db->zadd('feed_topic_list_' . $feed_topic->id, $this->created_at, $this->id);
            $feed_topic->feed_num += 1;
            $feed_topic->update();
        }

        if (isDevelopmentEnv()) {
            $this->addFeedToTotalList();
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
        return 'feed_' . $this->user_id . '_' . uniqid();
    }

    static function getMsgDb()
    {
        $endpoint = self::config('msg_db');
        return XRedis::getInstance($endpoint);
    }

    function uploadFiles($files)
    {
        $image_files = [];

        for ($i = 0; $i < 9; $i++) {
            $file = 'image_file' . $i;
            if (isset($files[$file])) {
                $image_files[] = $files[$file]['tmp_name'];
            }
        }

        if (count($image_files) > 0) {
            $this->updateImages($image_files);
        }

        if (isset($files['voice_file'])) {
            $this->updateVideo($files['voice_file']['tmp_name']);
        }
    }

    function updateImages($source_file_names)
    {
        $dest_file_names = [];
        foreach ($source_file_names as $file_name) {
            $dest_file_name = APP_NAME . '/feeds/image/' . uniqid() . '.jpg';
            $res = \StoreFile::upload($file_name, $dest_file_name);
            debug('upload ', $file_name);
            if ($res) {
                $dest_file_names[] = $dest_file_name;
            }
        }
        $this->image_num = count($dest_file_names);
        $this->image_files = json_encode($dest_file_names, JSON_UNESCAPED_UNICODE);
    }

    function updateVideo($voice_file)
    {
        $dest_file_name = APP_NAME . '/feeds/voice/' . uniqid() . '.mp3';
        $res = \StoreFile::upload($voice_file, $dest_file_name);
        if ($res) {
            $this->voice_file = $dest_file_name;
        }
    }

    function isLiked($user_id)
    {
        $msg_db = self::getMsgDb();
        return intval($msg_db->zscore('feed_like_users_' . $this->id, $user_id)) > 0;
    }

    function isDisliked($user_id)
    {
        $msg_db = self::getMsgDb();
        return intval($msg_db->zscore('feed_dislike_users_' . $this->id, $user_id)) > 0;
    }

    function isFollow($user_id)
    {
        $msg_db = self::getMsgDb();
        return intval($msg_db->zscore('user_follow_feed_list_' . $user_id, $this->id)) > 0;
    }

    static function createdFeed($user, $opts = [])
    {
        $content = fetch($opts, 'content');
        $feed_topic = fetch($opts, 'feed_topic');

        if (isBlank($content)) {
            return null;
        }

        $feed = new \Feeds();
        $feed->user_id = $user->id;
        $feed->user = $user;
        $feed->content = $content;
        $feed->location = fetch($opts, 'location');
        $feed->feed_topic = $feed_topic;
        $feed->feed_topic_id = fetch($opts, 'feed_topic_id', 0);
        $files = fetch($opts, 'files', []);
        $feed->uploadFiles($files);
        if ($feed->create()) {
            return $feed;
        }

        return null;
    }

    function getFeedImages()
    {
        $feed_images = [];

        if ($this->image_files) {
            $images = json_decode($this->image_files, true);

            foreach ($images as $image) {
                $url = \StoreFile::getUrl($image);
                $feed_images[] = [
                    'image_big_url' => $url . '@!big',
                    'image_small_url' => $url . '@!small',
                ];
            }
        }
        return $feed_images;
    }

    function getVoiceFileUrl()
    {
        if ($this->voice_file) {
            $url = \StoreFile::getUrl($this->voice_file);
            return $url;
        }

        return '';
    }

    function addWaitAuthFeedList()
    {
        $msg_db = self::getMsgDb();
        $msg_db->zadd('wait_auth_feed_list', time(), $this->id);
    }

    static function findFeedsByPage($key, $page, $per_page = 10)
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

    static function findWaitAuthFeeds($page, $per_page = 10)
    {
        $key = "wait_auth_feed_list";
        return self::findFeedsByPage($key, $page, $per_page);
    }

    static function findTotalFeeds($page, $per_page = 10)
    {
        $key = "feed_total_list";
        return self::findFeedsByPage($key, $page, $per_page);
    }

    static function findFollowFeeds($user, $page, $per_page = 10)
    {
        $key = "user_follow_feed_list_" . $user->id;
        return self::findFeedsByPage($key, $page, $per_page);
    }


    public function deleteFromWaitAuthList()
    {
        $msg_db = self::getMsgDb();
        $key = "wait_auth_feed_list";
        $msg_db->zrem($key, $this->id);
    }

    function addFeedToTotalList()
    {
        $user = $this->user;
        $msg_db = self::getMsgDb();
        $msg_db->zadd('feed_total_list', $this->created_at, $this->id);
        $msg_db->zadd('feed_total_list_product_channel' . $user->product_channel_id, $this->created_at, $this->id);
    }

    function like($user)
    {
        $msg_db = self::getMsgDb();
        $msg_db->zadd('feed_like_users_' . $this->id, time(), $user->id);
        $this->like_users_num = $msg_db->zcard('feed_like_users_' . $this->id);
        $this->update();
    }

    function disLike($user)
    {
        $msg_db = self::getMsgDb();
        $msg_db->zadd('feed_dislike_users_' . $this->id, time(), $user->id);
        $this->dislike_users_num = $msg_db->zcard('feed_like_users_' . $this->id);
        $this->update();
    }

    function follow($user)
    {
        $msg_db = self::getMsgDb();
        $msg_db->zadd('feed_follow_users_' . $this->id, time(), $user->id);
        $this->follow_users_num = $msg_db->zcard('feed_like_users_' . $this->id);
        $this->update();

        $msg_db->zadd('user_follow_feed_list_' . $user->id, time(), $this->id);
    }

    function toSimpleJson()
    {
        $user = $this->user;
        $json = [
            'id' => $this->id,
            'created_at' => $this->created_at,
            'created_at_text' => $this->created_at_text,
            'content' => $this->content,
            'like_users_num' => $this->like_users_num,
            'dislike_users_num' => $this->dislike_users_num,
            'comment_users_num' => $this->comment_users_num,
            'user_id' => $this->user_id,
            'feed_images' => $this->feed_images,
            'location' => $this->location,
            'voice_file_url' => $this->voice_file_url,
            'duration' => $this->duration,
            'avatar_small_url' => $user->avatar_small_url,
            'sex' => $user->sex,
            'nickname' => $user->nickname,
            'age' => $user->age,
            'share_users_num' => $this->share_users_num,
            'is_liked' => $this->is_liked,
            'is_disliked' => $this->is_disliked,
            'is_follow' => $this->is_follow
        ];

        return $json;
    }

    function toDetailJson()
    {
        $user = $this->user;
        $json = [
            'id' => $this->id,
            'created_at' => $this->created_at,
            'created_at_text' => $this->created_at_text,
            'content' => $this->content,
            'like_users_num' => $this->like_users_num,
            'dislike_users_num' => $this->dislike_users_num,
            'comment_users_num' => $this->comment_users_num,
            'user_id' => $this->user_id,
            'feed_images' => $this->feed_images,
            'location' => $this->location,
            'voice_file_url' => $this->voice_file_url,
            'duration' => $this->duration,
            'avatar_small_url' => $user->avatar_small_url,
            'sex' => $user->sex,
            'nickname' => $user->nickname,
            'age' => $user->age,
            'share_users_num' => $this->share_users_num,
            'is_liked' => $this->is_liked,
            'is_disliked' => $this->is_disliked,
            'is_follow' => $this->is_follow
        ];

        return $json;
    }

    function findFeedCommentList($page, $per_page)
    {
        $msg_db = self::getMsgDb();
        $key = 'feed_comment_list_' . $this->id;
        $total = $msg_db->zcard($key);
        $offset = ($page - 1) * $per_page;
        $feed_comment_ids = $msg_db->zrevrange($key, $offset, $offset + $per_page - 1);
        $feed_comments = FeedComments::findByIds($feed_comment_ids);
        Users::findBatch($feed_comments);
        $feed_comments = new PaginationModel($feed_comments, $total, $page, $per_page);
        $feed_comments->clazz = 'FeedComments';
        return $feed_comments;
    }
}