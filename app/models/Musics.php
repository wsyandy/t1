<?php

/**
 * Created by PhpStorm.
 * User: apple
 * Date: 2018/2/5
 * Time: 下午4:16
 */
class Musics extends BaseModel
{
    /**
     * @type Users
     */
    private $_user;

    static $STATUS = [STATUS_ON => '有效', STATUS_OFF => '无效'];
    static $TYPE = ['1' => '伴奏', '2' => '原唱'];

    static $files = ['file' => APP_NAME . '/musics/file/%s'];

    function toSimpleJson()
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'singer_name' => $this->singer_name,
            'user_name' => $this->user_name,
            'file_size' => $this->file_size_text,
            'file_url' => $this->file_url
        ];
    }

    function getFileUrl()
    {
        if (isBlank($this->file)) {
            return null;
        }
        return StoreFile::getUrl($this->file);
    }

    function getUserName()
    {
        if ($this->user) {
            return $this->user->nickname;
        }

        return '';
    }

    function getFileSizeText()
    {
        $file_size = 0;

        if ($this->file_size) {
            $file_size = round($this->file_size / 1000000, 1);
        }

        return $file_size . "M";
    }

    function down($user_id)
    {
        $db = Users::getUserDb();
        $key = "user_musics_id" . $user_id;

        if (!$db->zscore($key, $this->id)) {
            $db->zadd($key, time(), $this->id);
        }
    }

    function remove($user_id)
    {
        $db = Users::getUserDb();
        $key = "user_musics_id" . $user_id;

        if ($db->zscore($key, $this->id)) {
            $db->zrem($key, $this->id);
        }
    }
}