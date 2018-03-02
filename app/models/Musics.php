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
    static $TYPE = [2 => '原唱', 1 => '伴奏'];
    static $HOT = [STATUS_ON => '是', STATUS_OFF => '否'];

    static $files = ['file' => APP_NAME . '/musics/file/%s'];

    function toSimpleJson()
    {
        $json = [
            'id' => $this->id,
            'name' => $this->name,
            'singer_name' => $this->singer_name,
            'user_name' => $this->user_name,
            'file_size' => $this->file_size_text,
            'file_url' => $this->file_url
        ];

        if (isset($this->down_at)) {
            $json['down_at'] = $this->down_at;
        }

        return $json;
    }

    function toDetailJson()
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'singer_name' => $this->singer_name,
            'file_size' => $this->file_size_text,
            'file_url' => $this->file_url,
            'date' => $this->getDate()
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

    function getDate()
    {
        $format = 'Y-m-d H:i';
        // 时间格式
        $value = $this->created_at;

        if ($value) {
            return date($format, $value);
        } else {
            return "";
        }
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

    function updateFile($filename)
    {
        $old_file = $this->file;
        $dest_filename = APP_NAME . '/musics/file/' . uniqid() . '.mp3';
        $res = \StoreFile::upload($filename, $dest_filename);

        if ($res) {
            $this->file = $dest_filename;
            if ($this->update()) {
                if ($old_file) {
                    \StoreFile::delete($old_file);
                }
            }
        }
    }

    static function upload($files, $opts)
    {
        debug($files);
        if (isBlank($files) || !$files['file']['tmp_name']) {
            return [ERROR_CODE_FAIL, '上传文件非法', ''];
        }

        $name = fetch($opts, 'name');
        $singer_name = fetch($opts, 'singer_name');
        $type = fetch($opts, 'type');
        $user_id = fetch($opts, 'user_id');


        if ($files && $files['file']['size'] > 20000000) {
            return [ERROR_CODE_FAIL, '上传文件大小不能超过20M', ''];
        }

        $music = new Musics();

        if ($files) {
            $music->file_md5 = md5_file($files['file']['tmp_name']);
        }

        $music->user_id = $user_id;

        $fp = fopen($_FILES['file']['tmp_name'], "rb");
        $tag = fread($fp, 8);
        debug($tag);
        if (strstr($tag, 'ID3') === false) {
            return [ERROR_CODE_FAIL, '无效的文件', ''];
        }
        fclose($fp);

        if (!$music->checkFileMd5()) {
            return [ERROR_CODE_FAIL, '不能重复上传文件', ''];
        }

        $music->name = $name;
        $music->singer_name = $singer_name;
        $music->type = $type;
        $music->status = STATUS_ON;


        debug($files['file']['tmp_name']);
//        move_uploaded_file($files['file']['tmp_name'], APP_ROOT . "temp/" . uniqid() . ".mp3");


        $music->file_size = $files['file']['size'];

        $music->save();
        return [ERROR_CODE_SUCCESS, '上传成功', $music];
    }

    static function searchMusic($page, $per_page, $user_id)
    {
        $cond = [
            'conditions' => 'user_id = :user_id:',
            'bind' => ['user_id' => $user_id],
            'order' => 'id desc'
        ];
        return self::findPagination($cond, $page, $per_page);
    }

    static function deleteMusic($user_id, $delete_list)
    {
        $musics = self::findByIds($delete_list);
        foreach ($musics as $music) {
            if ($music->user_id == $user_id) {
                if ($music->file) {
                    \StoreFile::delete($music->file);
                }
                $music->delete();
            }
        }
    }
}