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
    private $_audio;

    static $STATUS = [STATUS_ON => '有效', STATUS_OFF => '无效'];

    static $files = ['file' => APP_NAME . '/audio_chapter/file/%s'];

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
        ];
    }

    static function findByAudioId($audio_id)
    {
        return self::find(
            [
                'conditions' => 'audio_id = :audio_id:',
                'bind' => ['audio_id' => $audio_id],
                'order' => 'rank desc'
            ]
        );
    }

    static function search($audio_id, $page, $per_page)
    {
        $cond = [
            'conditions' => 'audio_id = :audio_id: and status = :status:',
            'bind' => ['audio_id' => $audio_id, 'status' => STATUS_ON]
        ];

        $res = AudioChapters::findPagination($cond, $page, $per_page);

        return $res;
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
        if (self::findFirst($cond)) {
            return false;
        }
        return true;
    }
}