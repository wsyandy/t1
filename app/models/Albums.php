<?php

/**
 * Created by PhpStorm.
 * User: apple
 * Date: 18/1/2
 * Time: 下午10:12
 */
class Albums extends BaseModel
{

    /**
     * @type Users
     */
    private $_user;

    static $AUTH_STATUS = [AUTH_WAIT => '等待审核', AUTH_SUCCESS => '审核成功', AUTH_FAIL => '审核失败'];

    static function uploadImage($user, $filenames = [])
    {

        if (count($filenames) < 1) {
            info("文件为空");
            return false;
        }

        foreach ($filenames as $filename) {

            if (!file_exists($filename)) {
                continue;
            }

            $dest_filename = APP_NAME . '/albums/' . $user->id . '_' . date('YmdH') . uniqid() . '.jpg';
            $res = \StoreFile::upload($filename, $dest_filename);

            if ($res) {
                $album = new Albums();
                $album->user_id = $user->id;
                $album->image = $dest_filename;
                $album->save();
            }
        }


        return true;
    }

    function toSimpleJson()
    {
        return [
            'id' => $this->id,
            'user_id' => $this->user_id,
            'image_url' => $this->image_url,
            'image_small_url' => $this->image_small_url,
            'image_big_url' => $this->image_big_url
        ];
    }

    function getImageUrl()
    {
        if (isBlank($this->image)) {
            return '';
        }

        return StoreFile::getUrl($this->image);
    }

    function getImageSmallUrl()
    {
        if (isBlank($this->image)) {
            return '';
        }

        return StoreFile::getUrl($this->image) . '@!small';
    }

    function getImageBigUrl()
    {
        if (isBlank($this->image)) {
            return '';
        }

        return StoreFile::getUrl($this->image) . '@!big';
    }

    static function createAlbum($album_url, $user_id, $auth_status = AUTH_WAIT)
    {
        $album = new \Albums();
        $album->user_id = $user_id;
        $album->auth_status = $auth_status;
        $res = httpGet($album_url);
        $source_file = APP_ROOT . 'temp/album_' . md5(uniqid(mt_rand())) . '.jpg';
        $f = fopen($source_file, 'w');
        fwrite($f, $res);

        $dest_file = APP_NAME . '/albums/' . $user_id . '_' . date('YmdH') . uniqid() . '.jpg';
        $filename = \StoreFile::upload($source_file, $dest_file);
        unlink($source_file);
        if (isBlank($filename)) {
            return false;
        }
        $album->image = $filename;
        if ($album->create()) {
            return $album;
        }
        return false;
    }

}