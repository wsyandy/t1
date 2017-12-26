<?php

class WordVisits extends BaseModel
{

    public function generateUuid()
    {
        $uuid = "word_{$this->id}_" . time();

        for ($i = 0; $i < 15; $i++) {
            $uuid .= mt_rand(0, 9);
        }

        return substr($uuid, 0, 30);
    }
}