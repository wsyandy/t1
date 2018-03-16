<?php

class WeixinMenus extends BaseModel
{
    static $TYPE = ['view' => 'view'];


    static function menuButton($data)
    {

        if (is_array($data[1])) {
            $body = ['name' => $data[0]];
            $body['sub_button'] = self::menuBody($data[1], true);
        } else {
            $body = ["name" => $data[0], "type" => "view", "url" => $data[1]];
        }

        return $body;
    }

    static function menuBody($menu_data, $is_recursive = false)
    {
        $body = [];
        foreach ($menu_data as $data) {
            $body[] = self::menuButton($data);
        }

        if (!$is_recursive) {
            $body = ['button' => $body];
            $body = json_encode($body, JSON_UNESCAPED_UNICODE);
        }

        return $body;
    }

    static function createMenu($weixin_menu_template_id, $product_channel)
    {
        $weixin_menus = WeixinMenus::find(
            [
                'conditions' => 'weixin_menu_template_id = :weixin_menu_template_id:',
                'bind' => ['weixin_menu_template_id' => $weixin_menu_template_id],
                'order' => 'rank asc'
            ]
        );

        $domain = $product_channel->weixin_domain;

        $protocol = "http://";

        if (isProduction()) {
            $protocol = "https://";
        }

        $domain = $protocol . $domain;

        $body = [];

        foreach ($weixin_menus as $menu) {
            $weixin_menu_id = $menu->id;

            $weixin_sub_menus = WeixinSubMenus::find(
                [
                    'conditions' => 'weixin_menu_id = :weixin_menu_id: ',
                    'bind' => ['weixin_menu_id' => $weixin_menu_id],
                    'order' => 'rank asc'
                ]
            );

            if ($weixin_sub_menus && count($weixin_sub_menus) > 0) {

                $sub_menus_body = [];

                foreach ($weixin_sub_menus as $weixin_sub_menu) {
                    if (strpos($weixin_sub_menu->url, '?')) {
                        $weixin_sub_menu_url = $weixin_sub_menu->url . "&ts=" . time();
                    } else {
                        $weixin_sub_menu_url = $weixin_sub_menu->url . "?ts=" . time();
                    }
                    if (preg_match('/^http/', $weixin_sub_menu_url)) {
                        $sub_menus_body[] = [$weixin_sub_menu->name, $weixin_sub_menu_url];
                    } else {
                        $sub_menus_body[] = [$weixin_sub_menu->name, $domain . $weixin_sub_menu_url];
                    }
                }

                $body[] = [$menu->name, $sub_menus_body];

                continue;
            }

            if (strpos($menu->url, '?')) {
                $menu_url = $menu->url . "&ts=" . time();
            } else {
                $menu_url = $menu->url . "?ts=" . time();
            }

            if (preg_match('/^http/', $menu_url)) {
                $body[] = [$menu->name, $menu_url];
            } else {
                $body[] = [$menu->name, $domain . $menu_url];
            }
        }

        $menu = self::menuBody($body);

        info($product_channel->id, $menu);

        $weixin_event = new \WeixinEvents($product_channel);
        $access_token = $weixin_event->getAccessToken();
        info($product_channel->id, 'access_token', $access_token);
        return $weixin_event->updateMenu($menu);
    }
}