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
    static $TYPE = [1 => '伴奏', 2 => '原唱'];
    static $HOT = [STATUS_ON => '是', STATUS_OFF => '否'];

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

    function mergeJson()
    {
        return [
            'file_size_text' => $this->file_size_text,
            'user_nickname' => $this->user_nickname,
            'sex_text' => $this->user->sex_text,
            'user_mobile' => $this->user_mobile
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

    function checkField($files, $is_create = true)
    {
        if (isBlank($files) && $is_create) {
            return [ERROR_CODE_FAIL, '上传文件不能为空'];
        }

        $fields = ['name', 'singer_name', 'rank'];

        foreach ($fields as $field) {
            if (isBlank($this->$field)) {
                return [ERROR_CODE_FAIL, '字段不能为空'];
            }
        }

        $user = \Users::findFirstById($this->user_id);

        if (isBlank($user)) {
            return [ERROR_CODE_FAIL, '用户不存在'];
        }

        if ($files && $files['music']['size']['file'] > 20000000) {
            return [ERROR_CODE_FAIL, '上传文件大小不能超过20M'];
        }

        if ($this->hasChanged('rank')) {
            if (!$this->checkRank()) {
                return [ERROR_CODE_FAIL, '排序不能重复'];
            }
        }

        if ($files) {
            $this->file_md5 = md5_file($files['music']['tmp_name']['file']);
        }

        if ($this->hasChanged('file_md5')) {
            if (!$this->checkFileMd5()) {
                return [ERROR_CODE_FAIL, '不能重复上传文件'];
            }

            $this->file_size = $files['music']['size']['file'];
        }

        return [ERROR_CODE_SUCCESS, ''];
    }

    function checkFileMd5()
    {
        $cond = [
            'conditions' => 'file_md5 = :file_md5: and user_id = :user_id:',
            'bind' => ['file_md5' => $this->file_md5, 'user_id' => $this->user_id]
        ];

        $music = \Musics::findFirst($cond);

        if (isPresent($music)) {
            return false;
        }

        return true;
    }

    function checkRank()
    {
        $music = \Musics::findFirstByRank($this->rank);

        if (isPresent($music)) {
            return false;
        }

        return true;
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