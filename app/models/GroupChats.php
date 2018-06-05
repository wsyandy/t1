<?php

class GroupChats extends BaseModel
{
    /**
     * @type Users
     */
    private $_user;

    static $TYPE = [USER_CHATS => '个人', UNION_CHATS => '家族'];

    function beforeCreate()
    {

    }

    function afterCreate()
    {
        if (!$this->uid) {
            $this->uid = $this->id;
            $this->update();
        }

    }

    function toDataJson()
    {
        return [
            'id'=>$this->id,
            'group_id'=>$this->group_id,
            'user_id'=>$this->user_id,
            'name'=>$this->name,
            'introduce'=>$this->introduce,
            'avatar_file'=>$this->avatar_file_small_url,
            'uid'=>$this->uid,
            'status'=>$this->status,
            'join_type'=>$this->join_type,
            'created_at'=>$this->created_at_text,
            'last_at'=>$this->last_at_text,
            'chat'=>$this->chat,
        ];
    }

    function getAvatarFileSmallUrl()
    {
        if (isBlank($this->avatar_file)) {
            return null;
        }
        $url = StoreFile::getUrl($this->avatar_file)."@!small";

        return $url;
    }

    static function createGroupChat($user, $opts)
    {
        $name = fetch($opts, 'name');
        $introduce = fetch($opts, 'introduce');
        $join_type = fetch($opts, 'join_type');

        $group_chats = new GroupChats();
        $group_chats->name = $name;
        $group_chats->introduce = $introduce;
        $group_chats->user_id = $user->id;
        $group_chats->status = STATUS_ON;
        $group_chats->join_type = $join_type;
        $group_chats->chat = true;

        $group_chats->last_at = time();

        $group_chats->save();

        return $group_chats;

    }

    /**
     * 上传头像
     * @param $filename
     * @return bool
     */
    function updateAvatar($filename)
    {
        $old_avatar = $this->avatar_file;
        $dest_filename = APP_NAME . '/group_chats/avatar/' . date('YmdH') . uniqid() . '.jpg';
        $res = \StoreFile::upload($filename, $dest_filename);

        if ($res) {
            $this->avatar_file = $dest_filename;
            $this->avatar_status = AUTH_SUCCESS;
            if ($this->update()) {
                //  删除旧头像
                if ($old_avatar) {
                    \StoreFile::delete($old_avatar);
                }
            }
        }
    }

    function updateGroupChat($opts)
    {
        $name = fetch($opts, 'name');
        $introduce = fetch($opts, 'introduce');
        $group_id = fetch($opts,'group_id');

        $this->name = $name;
        $this->introduce = $introduce;
        $this->group_id = $group_id;

        $this->update();

    }

    static function getGroupChatsDb()
    {
        $endpoint = self::config('msg_db');
        return XRedis::getInstance($endpoint);
    }

    /**
     * 加入群聊key
     */

    static function getJoinGroupChatKey($id)
    {
        return "join_group_chat_key_" . $id;
    }

    /**
     * 审核群聊key
     */

    static function getReviewGroupChatKey($id)
    {
        return "review_group_chat_key_" . $id;
    }

    /**
     * 群管理员key
     */

    static function getManagerGroupChatKey($id)
    {
        return "manager_group_chat_key_" . $id;
    }


    function joinGroupChat($user_id)
    {
        if (!$user_id) {
            return false;
        }
        $key = self::getJoinGroupChatKey($this->id);
        $msg_db = self::getGroupChatsDb();
        $msg_db->zadd($key, time(), $user_id);

    }

    function reviewJoinGroupChat($user_id)
    {
        if (!$user_id) {
            return false;
        }
        $key = self::getReviewGroupChatKey($this->id);
        $msg_db = self::getGroupChatsDb();
        $msg_db->zadd($key, time(), $user_id);

    }

    function managerGroupChat($user_id)
    {
        if (!$user_id) {
            return false;
        }
        $key = self::getManagerGroupChatKey($this->id);
        $msg_db = self::getGroupChatsDb();
        $msg_db->zadd($key, time(), $user_id);

    }

    /**
     * @param $user
     * @return bool
     * 踢出群成员
     */
    function kickGroupChat($user_id)
    {
        if (!$user_id) {
            return false;
        }
        $key = self::getJoinGroupChatKey($this->id);
        $msg_db = self::getGroupChatsDb();
        $msg_db->zrem($key, $user_id);
    }

    function remReviewGroupChat($user_id)
    {
        if (!$user_id) {
            return false;
        }
        $key = self::getReviewGroupChatKey($this->id);
        $msg_db = self::getGroupChatsDb();
        $msg_db->zrem($key, $user_id);
    }

    function remManagerGroupChat($user_id)
    {
        if (!$user_id) {
            return false;
        }
        $key = self::getManagerGroupChatKey($this->id);
        $msg_db = self::getGroupChatsDb();
        $msg_db->zrem($key, $user_id);
    }

    /**
     * 解散群
     */
    function remAllGroupMembers()
    {
        $key = self::getJoinGroupChatKey($this->id);
        $msg_db = self::getGroupChatsDb();
        $msg_db->zremrangebyrank($key, 0, -1);
    }

    /**
     * 返回群聊所有成员
     */
    function getAllGroupMembers()
    {
        $key = self::getJoinGroupChatKey($this->id);
        $msg_db = self::getGroupChatsDb();
        $user_ids = $msg_db->zrange($key, 0, -1);

        return $user_ids;
    }

    /**
     * 返回群聊所有管理员
     */
    function getAllGroupManagers()
    {
        $key = self::getManagerGroupChatKey($this->id);
        $msg_db = self::getGroupChatsDb();
        $user_ids = $msg_db->zrange($key, 0, -1);

        return $user_ids;
    }


    function isGroupManager($user_id)
    {
        $manager_ids = $this->getAllGroupManagers();
        if (in_array($user_id, $manager_ids)) {
            return true;
        }
        return false;
    }

    function isGroupMember($user_id)
    {
        $member_ids = $this->getAllGroupMembers();
        if (in_array($user_id, $member_ids)) {
            return true;
        }
        return false;
    }

    function setChat($chat, $user_id)
    {
        $msg_db = self::getGroupChatsDb();

        if ($chat) {
            $msg_db->zrem("status_group_chat_user_ban_{$this->id}", $user_id);
            return;
        }

        $msg_db->zadd("status_group_chat_user_ban_{$this->id}", time(), $user_id);
    }

    function canChat($user_id)
    {
        $msg_db = self::getGroupChatsDb();
        $key = "status_group_chat_user_ban_{$this->id}";
        $chat = $msg_db->zscore($key, $user_id);

        if ($chat) {
            return false;
        }

        return true;
    }


}