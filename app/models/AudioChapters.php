<?php

/**
 * Created by PhpStorm.
 * User: apple
 * Date: 2018/2/5
 * Time: 下午5:17
 */
class AudioChapters extends BaseModel
{
    /**
     * @type Audios
     */
    static $_audio;

    static $STATUS = [STATUS_ON => '有效', STATUS_OFF => '无效'];

    static $files = ['file' => APP_NAME . '/audio_chapter/%s'];

    function toJson()
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'audio_id' => $this->audio_id,
            'file_url' => $this->file_url,
            'status_text' => $this->status_text,
            'rank' => $this->rank,
        ];
    }
    function toSimpleJson()
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'audio_id' => $this->audio_id,
            'file_url' => $this->file_url,
            'rank' => $this->rank,
        ];
    }

    static function findByAudioId($audio_id)
    {
        debug($audio_id);
        return self::find(
            [
                'conditions' => 'audio_id = :audio_id:',
                'bind' => ['audio_id' => $audio_id]
            ]
        );
    }

    function getFileUrl()
    {
        if (isBlank($this->file)) {
            return null;
        }
        return StoreFile::getUrl($this->file);
    }

    function check()
    {
        $cond = [];
        $cond['conditions'] = " rank = :rank:  and id != :id: and audio_id = :audio_id:";
        $cond['bind'] = [
            'id' => $this->id,
            'rank' => $this->rank,
            'audio_id' => $this->audio_id
        ];
        if(self::findFirst($cond))
        {
            return false;
        }
        return  true;
    }


}