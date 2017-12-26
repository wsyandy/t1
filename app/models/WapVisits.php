<?php

class WapVisits extends BaseModel
{
    public function generateUuid()
    {
        $uuid = "wap_{$this->id}_" . time();

        for ($i = 0; $i < 15; $i++) {
            $uuid .= mt_rand(0, 9);
        }

        return substr($uuid, 0, 30);
    }
}