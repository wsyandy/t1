<?php
/**
 * Created by PhpStorm.
 * User: apple
 * Date: 2018/2/5
 * Time: 下午4:16
 */
class Audios extends BaseModel
{
    static $STATUS = [STATUS_ON => '有效', STATUS_OFF => '无效'];
    static $AUDIO_TYPE = [AUDIO_TYPE_STORY => '故事' , AUDIO_TYPE_MUSIC => '音乐'];


    function toJson()
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'audio_type_text' => $this->audio_type_text,
            'status_text' => $this->status_text,
            'rank' => $this->rank,
        ];
    }

}