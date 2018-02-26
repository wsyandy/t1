<?php
/**
 * Created by PhpStorm.
 * User: apple
 * Date: 2018/2/5
 * Time: 下午4:16
 */
class Musics extends BaseModel
{
    static $STATUS = [STATUS_ON => '有效', STATUS_OFF => '无效'];
    static $TYPE = ['1' => '伴奏', '2' => '原唱'];

    static $files = ['file' => APP_NAME . '/musics/file/%s'];

    function toSimpleJson()
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'singer_name' => $this->singer_name,
            'status_text' => $this->status_text,
            'type_text' => $this->type_text,
            'rank' => $this->rank,
        ];
    }

    function getFileUrl()
    {
        if (isBlank($this->file)) {
            return null;
        }
        return StoreFile::getUrl($this->file);
    }

}