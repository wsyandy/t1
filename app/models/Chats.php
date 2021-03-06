<?php
/**
 * Created by PhpStorm.
 * User: maoluanjuan
 * Date: 17/01/2018
 * Time: 16:09
 */

class Chats extends BaseModel
{

    static $_only_cache = true;

    /**
     * @type Users
     */
    private $_sender;

    /**
     * @type integer
     */
    private $_sender_id;

    /**
     * @type Users
     */
    private $_receiver;

    /**
     * @type integer
     */
    private $_receiver_id;

    /**
     * @type string
     */
    private $_content_type;

    /**
     * @type string
     */
    private $_image_url;

    /**
     * @type string
     */
    private $_url;

    /**
     * @type string
     */
    private $_title;

    /**
     * @type string
     */
    private $_content;

    /**
     * @type integer
     */
    private $_created_at;

    /**
     * @type string
     */
    private $_id;

    static $content_types = array(
        CHAT_CONTENT_TYPE_TEXT => '文本'
    );

    static $VALIDATES = array(
        'sender_id' => array('null' => '不能为空'),
        'receiver_id' => array('null' => '不能为空'),
        'content' => array('null' => '不能为空'),
        'id' => array('null' => '不能为空'),
        'content_type' => array('null' => '不能为空', 'in' => [CHAT_CONTENT_TYPE_TEXT, CHAT_CONTENT_TYPE_TEXT_NEWS]),
    );

    function beforeCreate()
    {
        $this->id = $this->generateId();
    }

    function afterCreate()
    {
        $cache_db = \Chats::getXRedis(0);
        if (!$this->isFromAdmin()) {
            $cache_db->zadd('chat_session_' . $this->sender_id . '_' . $this->receiver_id, time(), $this->id);
            $cache_db->zadd('chat_user_list_' . $this->sender_id, time(), $this->receiver_id);
        }

        if (!$this->isToAdmin()) {
            $cache_db->zadd('chat_session_' . $this->receiver_id . '_' . $this->sender_id, time(), $this->id);
            $cache_db->zadd('chat_user_list_' . $this->receiver_id, time(), $this->sender_id);
        }
    }

    function asyncAfterCreate()
    {
        if ($this->isFromAdmin()) {
            $this->notifyAdminMessage();
        }
    }

    static function getCacheEndPoint()
    {
        $config = self::di('config');
        $endpoints = explode(',', $config->user_db_endpoints);
        return $endpoints[0];
    }

    function generateId()
    {
        return 'chat_' . strval($this->sender_id) . '_' . strval($this->receiver_id) . '_' . time() . mt_rand(1000, 10000);
    }

    static function welcomeMessage()
    {
        return "Hi~终于等到你，还好我没放弃!";
    }

    static function sendWelcomeMessage($user_id)
    {
        $content = \Chats::welcomeMessage();
        $content_type = CHAT_CONTENT_TYPE_TEXT;
        if (is_numeric($user_id)) {
            $user = Users::findFirstById($user_id);
        } else {
            $user = $user_id;
        }

        return \Chats::sendSystemMessage($user, $content_type, $content);
    }

    static function sendTextSystemMessage($user_id, $content = '')
    {
        if (!$content) {
            info("Exce content_error", $user_id);
            return false;
        }

        if (is_numeric($user_id)) {
            $user = Users::findFirstById($user_id);
        } else {
            $user = $user_id;
            $user_id = $user->id;
        }

        $content_type = CHAT_CONTENT_TYPE_TEXT;
        return \Chats::sendSystemMessage($user, $content_type, $content);
    }

    static function sendTextNewsSystemMessage($user_id, $content = '', $opts = [])
    {
        if (!$content) {
            info("Exce content_error", $user_id);
            return false;
        }

        if (is_numeric($user_id)) {
            $user = Users::findFirstById($user_id);
        } else {
            $user = $user_id;
            $user_id = $user->id;
        }

        $content_type = CHAT_CONTENT_TYPE_TEXT_NEWS;

        return \Chats::sendSystemMessage($user, $content_type, $content, $opts);
    }

    static function batchSendTextSystemMessage($user_ids, $content = '')
    {

        if (!$content) {
            info("Exce content_error", $user_ids);
            return false;
        }

        $users = Users::findByIds($user_ids);
        $content_type = CHAT_CONTENT_TYPE_TEXT;
        foreach ($users as $user) {
            debug($user->id, $content);
            \Chats::sendSystemMessage($user, $content_type, $content);
        }
    }

    static function sendSystemMessage($receiver_id, $content_type, $content, $opts = [])
    {
        $title = fetch($opts, 'title');
        $image_url = fetch($opts, 'image_url');
        $url = fetch($opts, 'url');

        if (is_numeric($receiver_id)) {
            $user = Users::findFirstById($receiver_id);
        } else {
            $user = $receiver_id;
            $receiver_id = $user->id;
        }

        if (!$user) {
            info("Exce no user", $receiver_id);
            return null;
        }

        $attrs = array(
            'sender_id' => SYSTEM_ID,
            'receiver_id' => $receiver_id,
            'content' => $content,
            'content_type' => $content_type
        );

        if ($content_type == CHAT_CONTENT_TYPE_TEXT_NEWS) {
            $attrs = array_merge($attrs, [
                'title' => $title,
                'image_url' => $image_url,
                'url' => $url
            ]);
        }

        $user->addUnreadMessagesNum();

        debug($receiver_id, $attrs);

        return \Chats::createChat($attrs);
    }

    static function createChat($attrs)
    {
        $chat = new \Chats();
        foreach (['sender_id', 'receiver_id', 'content', 'title', 'image_url', 'content_type', 'url'] as $column) {
            $chat->$column = fetch($attrs, $column);
        }
        if ($chat->create()) {
            return $chat;
        }
        return false;
    }

    function isFromAdmin()
    {
        return SYSTEM_ID == $this->sender_id;
    }

    function isToAdmin()
    {
        return SYSTEM_ID == $this->receiver_id;
    }

    function notifyAdminMessage()
    {
        $emchat = new \Emchat();
        $action = 'admin_message';
        $target_type = 'users';

        $ext = $this->toJson();
        $emchat->sendCmd($this->sender_id, $this->receiver_id, $action, $target_type, $ext);
    }

    function toJson()
    {
        return array(
            'id' => $this->id,
            'sender_id' => $this->sender_id,
            'receiver_id' => $this->receiver_id,
            'created_at' => $this->created_at,
            'content_type' => $this->content_type,
            'content' => $this->content,
            'created_at_text' => $this->created_at_text,
            'title' => $this->title,
            'image_url' => $this->image_url,
            'url' => $this->url
        );
    }

    static function chatListKey($receiver_id, $sender_id = SYSTEM_ID)
    {
        return "chat_session_" . $receiver_id . '_' . $sender_id;
    }

    static function findChatsList($user, $page, $per_page, $sender_id = SYSTEM_ID)
    {
        $key = \Chats::chatListKey($user->id, $sender_id);
        $cache_db = \Chats::getXRedis(0);

        $total = $cache_db->zcard($key);
        $offset = ($page - 1) * $per_page;
        $chat_ids = $cache_db->zrevrange($key, $offset, $offset + $per_page - 1);
        $chats = \Chats::findByIds($chat_ids);

        return new \PaginationModel($chats, $total, $page, $per_page);
    }

    static function sortByCreatedAt($chats)
    {
        $chat_hash = array();
        foreach ($chats as $chat) {
            $chat_hash[$chat->created_at] = $chat;
        }
        $results = $chats;
        if (asort($chat_hash)) {
            $results = array_values($chat_hash);
        }
        return $results;
    }
}