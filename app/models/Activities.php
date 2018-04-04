<?php

/**
 * Created by PhpStorm.
 * User: apple
 * Date: 2018/4/3
 * Time: 下午8:10
 */
class Activities extends BaseModel
{
    static $STATUS = [STATUS_ON => '有效', STATUS_OFF => '无效'];

    static $files = ['image' => APP_NAME . '/activities/image/%s'];

    function getImageUrl()
    {
        $image = $this->image;
        if (isBlank($image)) {
            return '';
        }

        return StoreFile::getUrl($this->image);
    }

    function getImageSmallUrl()
    {
        $image = $this->image;

        if (isBlank($image)) {
            return '';
        }
        return StoreFile::getUrl($image) . '@!small';
    }

    function getStartText()
    {
        $start_at = $this->start_at;
        if (isBlank($start_at)) {
            return '';
        }
        return date("m月d日", $start_at);
    }

    function getEndText()
    {
        $end_at = $this->end_at;
        if (isBlank($end_at)) {
            return '';
        }
        return date("m月d日", $end_at);
    }

    static function findActivity($opts)
    {
        $platform = fetch($opts, 'platform');
        $product_channel_id = fetch($opts, 'product_channel_id');

        $conditions[] = " (platforms like :platform: or platforms like '*' or platforms = '') ";
        $bind['platform'] = $platform;

        $conditions[] = ' (product_channel_ids like :product_channel_id: or product_channel_ids = "") ';
        $bind['product_channel_id'] = '%,' . $product_channel_id . ',%';

        $conditions[] = ' status=:status: ';
        $bind['status'] = STATUS_ON;

        $cond['condition'] = implode(' and ', $conditions);
        $cond['bind'] = $bind;
        $cond['order'] = 'rank desc, id desc';

        debug($cond);

        $activities = Activities::find($cond);

        return $activities;
    }
}