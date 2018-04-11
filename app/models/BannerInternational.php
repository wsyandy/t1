<?php

/**
 * Created by PhpStorm.
 * User: apple
 * Date: 2018/3/6
 * Time: 下午2:35
 */
trait BannerInternational
{

    static function searchBannersByInternational($current_user, $fields)
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

        info($basic_cond);


        foreach ($fields as $key => $value) {
            if ($value) {
                $banner_cond = $basic_cond;
                $banner_cond['conditions'] .= "and $key = :$key:";
                $banner_cond['bind'][$key] = $value;
                debug($banner_cond);
            }
        }

        $banners = self::find($banner_cond);

        return $banners;

    }

}