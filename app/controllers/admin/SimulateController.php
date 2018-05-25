<?php
/**
 * Created by PhpStorm.
 * User: admin
 * Date: 2018/5/24
 * Time: 17:27
 */

namespace admin;


class SimulateController extends BaseController
{
    function indexAction()
    {
        $page = 1;
        $per_page=30;
        $total_page = 1;
        $total_entries = $per_page * $total_page;

        $user_id = $this->params("user[id_eq]");
        $mobile = $this->params("user[mobile_eq]");
        $user_type = $this->params("user[user_type_eq]");
        $user_status = $this->params("user[user_status_eq]");
        $nickname = $this->params("nickname");
        $product_channel_id = $this->params("user[product_channel_id_eq]");

        $cond = $this->getConditions('user');
        $cond['order'] = 'id desc';

        if ($nickname) {

            if (isset($cond['conditions'])) {
                $cond['conditions'] .= " and nickname like '%$nickname%'";
            } else {
                $cond['conditions'] = "nickname like '%$nickname%'";
            }
        }

        if (isset($cond['conditions'])) {
            $cond['conditions'] .= " and current_room_id > 0";
        } else {
            $cond['conditions'] = "current_room_id > 0";
        }

        $users = \Users::findPagination($cond, $page, $per_page,$total_entries);

        //info("数据：",$users);
        $this->view->users = $users;
        $this->view->product_channels = \ProductChannels::find(['order' => 'id desc']);
        $this->view->user_types = \UserEnumerations::$USER_TYPE;
        $this->view->user_type = intval($user_type);
        $this->view->user_status = $user_status == '' ? '' : intval($user_status);
        $this->view->mobile = $mobile;
        $this->view->user_id = $user_id;
        $this->view->nickname = $nickname;
        $this->view->product_channel_id = intval($product_channel_id);
    }
}