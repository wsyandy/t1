<?php

/**
 * Created by PhpStorm.
 * User: apple
 * Date: 2018/2/24
 * Time: 上午10:35
 */
class RoomThemes extends BaseModel
{
    static $STATUS = [STATUS_ON => '有效', STATUS_OFF => '无效'];

    static $files = ['icon' => APP_NAME . '/room_themes/icon/%s', 'theme_image' => APP_NAME . '/room_themes/theme_image/%s'];

    function getIconUrl()
    {
        if (isBlank($this->icon)) {
            return '';
        }

        return StoreFile::getUrl($this->icon);
    }

    function getThemeImageUrl()
    {
        if (isBlank($this->theme_image)) {
            return '';
        }

        return StoreFile::getUrl($this->theme_image);
    }

    function getThemeImageSmallUrl()
    {
        if (isBlank($this->theme_image)) {
            return '';
        }
        return StoreFile::getUrl($this->theme_image) . '@!small';
    }

    function toSimpleJson()
    {
        return [
            'id' => $this->id,
            'icon_url' => $this->icon_url,
            'name' => $this->name,
            'theme_image_url' => $this->theme_image_url
        ];
    }

    function toJson()
    {
        return [
            'id' => $this->id,
            'theme_image_url' => $this->theme_image_url,
            'icon_url' => $this->icon_url,
            'name' => $this->name,
            'status_text' => $this->status_text,
            'rank' => $this->rank,
            'platform_num' => $this->platform_num,
            'product_channel_num' => $this->product_channel_num
        ];
    }

    /**
     * 获取有效的主题
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

