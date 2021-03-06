<?php

/**
 * Created by PhpStorm.
 * User: apple
 * Date: 2018/1/30
 * Time: 下午3:56
 */
class EmoticonImages extends BaseModel
{
    //表情状态
    static $STATUS = [STATUS_ON => '有效', STATUS_OFF => '无效'];

    //图片文件
    static $files = ['image' => APP_NAME . '/emoticon_images/image/%s', 'dynamic_image' => APP_NAME . '/emoticon_images/dynamic_image/%s'];

    function getDynamicImageUrl()
    {
        if (isBlank($this->dynamic_image)) {
            return '';
        }

        return StoreFile::getUrl($this->dynamic_image);
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

    function toSimpleJson()
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'code' => $this->code,
            'duration' => $this->duration,
            'image_url' => $this->image_url,
            'image_small_url' => $this->image_small_url,
            'dynamic_image_url' => $this->dynamic_image_url
        ];
    }

    function toJson()
    {
        return [
            'id' => $this->id,
            'image_url' => $this->image_url,
            'image_small_url' => $this->image_small_url,
            'name' => $this->name,
            'code' => $this->code,
            'status_text' => $this->status_text,
            'rank' => $this->rank,
            'duration' => $this->duration,
            'dynamic_image_url' => $this->dynamic_image_url,
            'platform_num' => $this->platform_num,
            'product_channel_num' => $this->product_channel_num
        ];
    }

    //是否存在 code 或 rank 相同的表情
    function checkFields()
    {

        $fields = ['code', 'rank'];

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

    /**
     * 获取有效的表情
     * @return PaginationModel
     */
    static function findValidList($user, $page, $per_page = 10)
    {
        $platform = $user->platform;
        $product_channel_id = $user->product_channel_id;
        $cond = [
            'conditions' => 'status = :status:',
            'bind' => [
                'status' => STATUS_ON
            ],
            'order' => 'rank desc'
        ];

        $cond['conditions'] .= " and ( platforms like '*' or platforms like :platforms: or platforms = '')";
        $cond['bind']['platforms'] = "%" . $platform . "%";

        $cond['conditions'] .= " and (product_channel_ids = '' or product_channel_ids is null or product_channel_ids like :product_channel_ids:)";
        $cond['bind']['product_channel_ids'] = "%," . $product_channel_id . ",%";

        return self::findPagination($cond, $page, $per_page);
    }

    function productChannelNum()
    {
        $num = 0;
        if ($this->product_channel_ids) {
            $product_channel_ids = explode(',', $this->product_channel_ids);
            $product_channel_ids = array_filter(array_unique($product_channel_ids));
            $num = count($product_channel_ids);
        }

        return $num;
    }

    function platformNum()
    {
        $platforms = $this->platforms;
        $num = 'all';

        if ($platforms && '*' != $platforms) {
            $platforms = array_filter(explode(',', $platforms));
            $num = count($platforms);
        }

        return $num;
    }
}