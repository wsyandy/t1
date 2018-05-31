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

    /**
     * 加入群聊key
     */

    static function getJoinGroupChatKey($uid)
    {
        return "join_group_chat_key_".$uid;
    }

    /**
     * 审核群聊key
     */

    static function getReviewGroupChatKey($uid)
    {
        return "review_group_chat_key_".$uid;
    }

    /**
     * 群管理员key
     */

    static function getAdminGroupChatKey($uid)
    {
        return "admin_group_chat_key_".$uid;
    }


    function joinGroupChat($user)
    {
        if(!$user){
            return false;
        }
        $key = self::getJoinGroupChatKey($this->uid);
        $hot_cache = self::getHotWriteCache();
        $hot_cache->zadd($key,time(),$user->id);

    }

    function reviewJoinGroupChat($user)
    {
        if(!$user){
            return false;
        }
        $key = self::getReviewGroupChatKey($this->uid);
        $hot_cache = self::getHotWriteCache();
        $hot_cache->zadd($key,time(),$user->id);

    }

    function adminGroupChat($user)
    {
        if(!$user){
            return false;
        }
        $key = self::getAdminGroupChatKey($this->uid);
        $hot_cache = self::getHotWriteCache();
        $hot_cache->zadd($key,time(),$user->id);

    }

    /**
     * @param $user
     * @return bool
     * 踢出群成员
     */
    function kickGroupChat($user)
    {
        if(!$user){
            return false;
        }
        $key = self::getJoinGroupChatKey($this->uid);
        $hot_cache = self::getHotWriteCache();
        $hot_cache->zrem($key,$user->id);
    }

    function remReviewGroupChat($user)
    {
        if(!$user){
            return false;
        }
        $key = self::getReviewGroupChatKey($this->uid);
        $hot_cache = self::getHotWriteCache();
        $hot_cache->zrem($key,$user->id);
    }

    function remAdminGroupChat($user)
    {
        if(!$user){
            return false;
        }
        $key = self::getAdminGroupChatKey($this->uid);
        $hot_cache = self::getHotWriteCache();
        $hot_cache->zrem($key,$user->id);
    }

    /**
     * 返回群聊所有成员
     */
    function getAllGroupMembers()
    {
        $key = self::getJoinGroupChatKey($this->uid);
        $hot_cache = self::getHotWriteCache();
        $user_ids = $hot_cache->zrange($key,0,-1);

        $users = \Users::findByIds($user_ids);

        return $users;
    }

    /**
     * 返回群聊所有管理员
     */
    function getAllGroupAdmins()
    {
        $key = self::getAdminGroupChatKey($this->uid);
        $hot_cache = self::getHotWriteCache();
        $user_ids = $hot_cache->zrange($key,0,-1);

        $users = \Users::findByIds($user_ids);

        return $users;
    }




}