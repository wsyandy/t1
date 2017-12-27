<?php
/**
 * Created by PhpStorm.
 * User: apple
 * Date: 2017/12/26
 * Time: 下午3:21
 */

trait UserAttrs
{
    function toDetailJson()
    {
        return array(
            'uid' => $this->uid,
            'sex' => $this->sex,
            'province_id' => $this->province_id,
            'province_name' => $this->province_name,
            'city_id' => $this->city_id,
            'city_name' => $this->city_name,
            'avatar_url' => $this->avatar_url,
            'avatar_small_url' => $this->avatar_small_url,
            'login_name' => $this->login_name,
            'nickname' => $this->nickname,
            'mobile' => $this->mobile
        );
    }
}
