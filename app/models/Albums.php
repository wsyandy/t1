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

    static $AUTH_STATUS = [VERIFY_WAIT => '等待审核', VERIFY_SUCCESS => '审核成功', VERIFY_FAIL => '审核失败'];

    static function uploadImage($user, $filename)
    {

        if (!file_exists($filename)) {
            return null;
        }

        $dest_filename = APP_NAME . '/albums/' . $user->id . '_' . date('YmdH') . uniqid() . '.jpg';
        $res = \StoreFile::upload($filename, $dest_filename);
        if ($res) {
            $album = new Albums();
            $album->user_id = $user->id;
            $album->image = $dest_filename;
            $album->save();

            return $album;
        }

        return null;
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

}