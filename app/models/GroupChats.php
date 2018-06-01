<?php

class GroupChats extends BaseModel
{
    /**
     * @type Users
     */
    private $_user;

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

    static function createGroupChat($user, $opts)
    {
        $name = fetch($opts,'name');
        $introduce = fetch($opts,'introduce');
        $avatar = fetch($opts,'avatar');

        $group_chats = new GroupChats();
        $group_chats->name = $name;
        $group_chats->introduce = $introduce;
        $group_chats->avatar = $avatar;
        $group_chats->user_id = $user->id;
        $group_chats->status = STATUS_ON;
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
                //  删除老头像
                if ($old_avatar) {
                    \StoreFile::delete($old_avatar);
                }
            }
        }
    }

    function updateGroupChat($opts)
    {
        $name = fetch($opts,'name');
        $introduce = fetch($opts,'introduce');
        $avatar = fetch($opts,'avatar');
        $join_type = fetch($opts,'join');
        $chat = fetch($opts,'chat','true');

        $this->name = $name;
        $this->introduce = $introduce;
        $this->avatar = $avatar;
        $this->join_type = $join_type;
        $this->chat = $chat;

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
        return "join_group_chat_key_".$id;
    }

    /**
     * 审核群聊key
     */

    static function getReviewGroupChatKey($id)
    {
        return "review_group_chat_key_".$id;
    }

    /**
     * 群管理员key
     */

    static function getManagerGroupChatKey($id)
    {
        return "manager_group_chat_key_".$id;
    }


    function joinGroupChat($user_id)
    {
        if(!$user_id){
            return false;
        }
        $key = self::getJoinGroupChatKey($this->id);
        $msg_db = self::getGroupChatsDb();
        $msg_db->zadd($key,time(),$user_id);

    }

    function reviewJoinGroupChat($user_id)
    {
        if(!$user_id){
            return false;
        }
        $key = self::getReviewGroupChatKey($this->id);
        $msg_db = self::getGroupChatsDb();
        $msg_db->zadd($key,time(),$user_id);

    }

    function managerGroupChat($user_id)
    {
        if(!$user_id){
            return false;
        }
        $key = self::getManagerGroupChatKey($this->id);
        $msg_db = self::getGroupChatsDb();
        $msg_db->zadd($key,time(),$user_id);

    }

    /**
     * @param $user
     * @return bool
     * 踢出群成员
     */
    function kickGroupChat($user_id)
    {
        if(!$user_id){
            return false;
        }
        $key = self::getJoinGroupChatKey($this->id);
        $msg_db = self::getGroupChatsDb();
        $msg_db->zrem($key,$user_id);
    }

    function remReviewGroupChat($user_id)
    {
        if(!$user_id){
            return false;
        }
        $key = self::getReviewGroupChatKey($this->id);
        $msg_db = self::getGroupChatsDb();
        $msg_db->zrem($key,$user_id);
    }

    function remManagerGroupChat($user_id)
    {
        if(!$user_id){
            return false;
        }
        $key = self::getManagerGroupChatKey($this->id);
        $msg_db = self::getGroupChatsDb();
        $msg_db->zrem($key,$user_id);
    }

    /**
     * 返回群聊所有成员
     */
    function getAllGroupMembers()
    {
        $key = self::getJoinGroupChatKey($this->id);
        $msg_db = self::getGroupChatsDb();
        $user_ids = $msg_db->zrange($key,0,-1);

        return $user_ids;
    }

    /**
     * 返回群聊所有管理员
     */
    function getAllGroupManagers()
    {
        $key = self::getManagerGroupChatKey($this->id);
        $msg_db = self::getGroupChatsDb();
        $user_ids = $msg_db->zrange($key,0,-1);

        return $user_ids;
    }


    function isGroupManager($user_id)
    {
        $manager_ids = $this->getAllGroupManagers();
        if(in_array($user_id,$manager_ids)){
            return true;
        }
        return false;
    }



}