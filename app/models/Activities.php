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
    static $PLATFORMS = ['client_ios' => '客户端ios', 'client_android' => '客户端安卓', 'weixin_ios' => '微信ios',
        'weixin_android' => '微信安卓', 'touch_ios' => 'H5ios', 'touch_android' => 'H5安卓'];

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

    function mergeJson()
    {
        return [
            'image_small_url' => $this->image_small_url
        ];
    }

    //是否存在 code
    function checkFields()
    {
        $fields = ['code'];

        foreach ($fields as $field) {
            $val = $this->$field;
            if (isBlank($val)) {
                return [ERROR_CODE_FAIL, $field . "不能为空"];
            }

            if ($this->hasChanged($field)) {
                $obj = self::findFirst([
                    'conditions' => "$field  = :$field:",
                    'bind' => [$field => $val]
                ]);

                if (isPresent($obj)) {
                    return [ERROR_CODE_FAIL, $field . "不能重复"];
                }
            }
        }
        return [ERROR_CODE_SUCCESS, ''];
    }


    static function findActivity($opts)
    {
        $platform = fetch($opts, 'platform');
        $product_channel_id = fetch($opts, 'product_channel_id');
        $conditions = [];
        $bind = [];

        $conditions[] = " (platforms like :platform: or platforms like '*' or platforms = '') ";
        $bind['platform'] = $platform;

        $conditions[] = " (product_channel_ids like :product_channel_id: or product_channel_ids = '') ";
        $bind['product_channel_id'] = '%,' . $product_channel_id . ',%';

        $conditions[] = ' status = :status: ';
        $bind['status'] = STATUS_ON;

        $cond['conditions'] = implode(' and ', $conditions);
        $cond['bind'] = $bind;
        $cond['order'] = 'rank desc, id desc';

        debug($cond);

        $activities = Activities::find($cond);

        return $activities;
    }
}