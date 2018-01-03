<?php

/**
 * Created by PhpStorm.
 * User: apple
 * Date: 18/1/2
 * Time: 下午10:13
 */
class Gifts extends BaseModel
{

    function toSimpleJson()
    {
        return [
            'id' => $this->id,
            'image_url' => $this->image_url,
            'image_small_url' => $this->image_small_url,
            'image_big_url' => $this->image_big_url,
            'name' => $this->name,
            'gold' => $this->gold,
            'diamond' => $this->diamond
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