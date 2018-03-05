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
        if ($is_create && (isBlank($files) || !$files['music']['tmp_name']['file'])) {
            return [ERROR_CODE_FAIL, '上传文件不能为空'];
        }

        $user = \Users::findFirstById($this->user_id);

        if (isBlank($user)) {
            return [ERROR_CODE_FAIL, '用户不存在'];
        }

        $fields = ['歌名' => 'name', '歌手' => 'singer_name'];

        foreach ($fields as $key => $value) {
            if (isBlank($this->$value)) {
                return [ERROR_CODE_FAIL, $key . '不能为空'];
            }
        }

        if ($files) {
            $file_size = $files['music']['size']['file'];
            $file_name = $files['music']['tmp_name']['file'];;

            if ($file_size > 20000000) {
                return [ERROR_CODE_FAIL, '上传文件大小不能超过20M'];
            }

            if (self::isMp3($file_name) === false) {
                return [ERROR_CODE_FAIL, '无效的文件'];
            }

            $this->file_md5 = md5_file($file_name);

            if ($this->hasChanged('file_md5')) {
                $repeating_file = $this->checkFileMd5();
                if ($repeating_file) {
                    $this->file = $repeating_file;
                }
            }

            $this->file_size = $file_size;
        }

        if ($this->hasChanged('rank')) {
            if (!$this->checkRank()) {
                return [ERROR_CODE_FAIL, '排序不能重复'];
            }
        }

        return [ERROR_CODE_SUCCESS, ''];
    }

    static function upload($files, $opts)
    {
        debug($files);
        if (isBlank($files) || !$files['music']['tmp_name']['file']) {
            return [ERROR_CODE_FAIL, '上传文件非法', ''];
        }

        $user_id = fetch($opts, 'user_id');
        $name = fetch($opts, 'name');
        $singer_name = fetch($opts, 'singer_name');
        $type = fetch($opts, 'type');

        $music = new Musics();
        $music->name = $name;
        $music->singer_name = $singer_name;
        $music->user_id = $user_id;

        list($error_code, $error_reason) = $music->checkField($files);
        if ($error_code != ERROR_CODE_SUCCESS) {
            return [$error_code, $error_reason, ''];
        }

        $music->type = $type;
        $music->status = STATUS_ON;
        $music->save();
        return [ERROR_CODE_SUCCESS, '上传成功', $music];
    }

    function checkFileMd5()
    {
        $cond = [
            'conditions' => 'file_md5 = :file_md5: and user_id = :user_id: and file is not null and id != :id:',
            'bind' => ['file_md5' => $this->file_md5, 'user_id' => $this->user_id , 'id' => $this->id]
        ];

        $music = \Musics::findFirst($cond);

        if (isPresent($music)) {
            return $music->file;
        }

        return false;
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

    static function isMp3($filename)
    {
        $fp = fopen($filename, 'rb');
        $head = fread($fp, 8);

        $encode = mb_detect_encoding($head, ["ASCII", "UTF-8", "GB2312", "GBK", "BIG5", "JIS", "EUC-JP", 'ISO-8859-1']);

        debug($encode, $head);

        if ('ISO-8859-1' == $encode) {

            $pattern = '^\\xFF[\\xE2-\\xE7\\xF2-\\xF7\\xFA-\\xFF][\\x00-\\x0B\\x10-\\x1B\\x20-\\x2B\\x30-\\x3B\\x40-\\x4B\\x50-\\x5B\\x60-\\x6B\\x70-\\x7B\\x80-\\x8B\\x90-\\x9B\\xA0-\\xAB\\xB0-\\xBB\\xC0-\\xCB\\xD0-\\xDB\\xE0-\\xEB\\xF0-\\xFB]';

            if (preg_match('/' . $pattern . '/s', $head)) {
                return true;
            }

        } else {

            $head = trim($head);
            debug($head);

            if (strstr($head, 'ID3') !== false) {
                return true;
            }
        }

        fclose($fp);
        return false;
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
                if ($music->file && !$music->checkFileMd5()) {
                    \StoreFile::delete($music->file);
                }
                $music->delete();
            }
        }
    }
}