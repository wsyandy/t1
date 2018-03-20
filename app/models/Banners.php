<?php

/**
 * Created by PhpStorm.
 * User: apple
 * Date: 2018/3/6
 * Time: 下午2:35
 */
class Banners extends BaseModel
{
    static $STATUS = [STATUS_ON => '启用', STATUS_OFF => '禁用'];

    static $HOT = [STATUS_ON => '是', STATUS_OFF => '否'];

    static $NEW = [STATUS_ON => '是', STATUS_OFF => '否'];

    static $TYPE = [1 => '附近页面'];

    static $MATERIAL_TYPE = [BANNER_TYPE_ROOM => '房间', BANNER_TYPE_URL => '链接'];

    static $files = ['image' => APP_NAME . '/banners/image/%s'];

    static $PLATFORMS = ['*' => '全部', 'client_ios' => '客户端ios', 'client_android' => '客户端安卓', 'weixin_ios' => '微信ios',
        'weixin_android' => '微信安卓', 'touch_ios' => 'H5ios', 'touch_android' => 'H5安卓'];

    function getImageUrl()
    {
        if (isBlank($this->image)) {
            return null;
        }
        $url = StoreFile::getUrl($this->image);
        return $url;
    }

    function getImageSmallUrl()
    {
        return $this->getImageUrl('small');
    }

    function mergeJson()
    {
        return [
            'image_url' => $this->image_url,
            'image_small_url' => $this->image_small_url,
            'platform_num' => $this->platform_num,
            'product_channel_num' => $this->product_channel_num
        ];
    }

    function toSimpleJson()
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'url' => $this->generateUrl(),
            'image_url' => $this->image_url,
            'image_small_url' => $this->image_small_url
        ];
    }

    function checkFields()
    {
        $clazz = $this->getCheckClass();

        if ($this->isRedirectUrl()) {
            if ($this->material_ids) {
                return [ERROR_CODE_FAIL, '产品类型为链接，则不能有房间ID'];
            }

            if (!$this->url) {
                return [ERROR_CODE_FAIL, '产品类型为链接，则需要URL'];
            }

        } elseif ($this->isRoom()) {
            if ($this->url) {
                return [ERROR_CODE_FAIL, '产品类型为房间，则不能有URL'];
            }

            if ($this->material_ids) {

                $room_ids = explode(',', $this->material_ids);

                foreach ($room_ids as $room_id) {
                    if (!$clazz::findFirstById($room_id)) {
                        return [ERROR_CODE_FAIL, 'id：' . $room_id . '不存在'];
                    }
                }
            } else {
                return [ERROR_CODE_FAIL, "产品id不存在"];
            }
        }
        return [ERROR_CODE_SUCCESS, ''];
    }

    function platformNum()
    {
        $platforms = $this->platforms;
        $num = 0;

        if ('*' == $platforms) {
            $num = count(self::$PLATFORMS) - 1;
        } elseif ($platforms) {
            $platforms = array_filter(explode(',', $platforms));
            $num = count($platforms);
        }

        return $num;
    }

    function productChannelNum()
    {
        $num = 0;

        $product_channel_banners = ProductChannelBanners::findByBannerId($this->id);

        if ($product_channel_banners) {
            $num = count($product_channel_banners);
        }

        return $num;
    }

    function generateUrl()
    {
        if ($this->isRedirectUrl() && $this->url) {
            return $this->url;
        }

        if ($this->isRoom() && $this->material_ids) {
            $material_ids = explode(',', $this->material_ids);
            return self::generateRoomDetailUrl($material_ids[0]);
        }
        return '';
    }

    static function generateRoomDetailUrl($id)
    {
        return "app://rooms/detail?id=" . $id;
    }


    static function searchBanners($current_user, $fields)
    {
        $conds = [
            'conditions' => 'product_channel_id = :product_channel_id:',
            'bind' => ['product_channel_id' => $current_user->product_channel_id]
        ];

        $all_banners = [];
        $product_channel_banner_ids = [];

        $product_channel_banners = ProductChannelBanners::find($conds);
        foreach ($product_channel_banners as $product_channel_banner) {
            $product_channel_banner_ids[] = $product_channel_banner->banner_id;
        }

        if (!$product_channel_banner_ids) {
            return $all_banners;
        }

        $is_client_platform = $current_user->isClientPlatform();
        $platform = $current_user->platform;
        if ($is_client_platform) {
            $platform = "client_" . $platform;
        }

        $product_channel_banner_ids = implode(',', $product_channel_banner_ids);
        $basic_cond = [
            'conditions' => "id in ({$product_channel_banner_ids}) and status=:status: and " .
                "(platforms like '*' or platforms = '' or platforms like :platforms:)",
            'bind' => ['status' => STATUS_ON, 'platforms' => '%' . $platform . '%'],
            'order' => 'rank desc,id desc'];

        $all_banners_json = [];

        foreach ($fields as $key => $value) {
            if ($value) {
                $banner_cond = $basic_cond;
                $banner_cond['conditions'] .= "and $key = :$key:";
                $banner_cond['bind'][$key] = $value;
                debug($banner_cond);

                $banners = self::find($banner_cond);
                $banners_json = [];
                foreach ($banners as $banner) {
                    $banners_json[] = $banner->toSimpleJson();
                }

                if ('new' == $key) {
                    $all_banners_json["latest" . "_banners"] = $banners_json;
                } elseif ('type' == $key && 1 == $key) {
                    $all_banners_json["near_banners"] = $banners_json;
                } else {
                    $all_banners_json[$key . "_banners"] = $banners_json;
                }
            }
        }

        return $all_banners_json;
    }

    function click($current_user)
    {

        if ($current_user && $current_user->mobile) {
            $this->stat($current_user);
        }
    }

//    function stat($user)
//    {
//        $day = date("Ymd");
//
//        $stat_db = \Stats::getStatDb();
//        $hot_cache = \Banners::getHotWriteCache();
//
//        $banner_stat_key = 'banner_stat_' . $day;
//        $cache_key = 'banner_stat_banner_id' . $this->id . '_product_channel_id-1_platform-1';
//
//        $stat_db->hincrby($banner_stat_key, $cache_key . '_num', 1);
//
//        $hot_key = $cache_key . '_' . $day . '_' . $user->id;
//        if (!$hot_cache->get($hot_key)) {
//            $hot_cache->setex($hot_key, endOfDay() - time(), 1);
//            $stat_db->hincrby($banner_stat_key, $cache_key, 1);
//        }
//    }

    function isRoom()
    {
        return $this->material_type == BANNER_TYPE_ROOM;
    }

    function isRedirectUrl()
    {
        return $this->material_type == BANNER_TYPE_URL;
    }

    function getCheckClass()
    {
        if ($this->isRoom()) {
            return 'Rooms';
        }
        return null;
    }
}