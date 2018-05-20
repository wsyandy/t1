<?php
/**
 * Created by PhpStorm.
 * User: apple
 * Date: 2018/5/20
 * Time: 上午1:33
 */

namespace admin;

class CouplesController extends BaseController
{
    function indexAction()
    {
        $db = \Users::getUserDb();
        $key = \Couples::generateCpInfoKey();
        $res = $db->zrevrange($key, 0, -1);

        $sponsor_ids = [];
        $pursuer_ids = [];

        foreach ($res as $re) {
            $ids = explode('_', $re);

            $users = \Users::findByIds($ids);

            $sponsor_ids[] = $ids[0];
            $pursuer_ids[] = $ids[1];
        }

        $sponsor_users = \Users::findByIds([1001303, 1001303]);
        echoLine(count($sponsor_users));
        $pursuer_users = \Users::findByIds($pursuer_ids);

        $is_on_the_list = false;
//        if (in_array($current_user_id, $sponsor_ids) || in_array($current_user_id, $pursuer_ids)) {
//            $is_on_the_list = true;
//        }

        if ($sponsor_users && $pursuer_users) {
            return $this->renderJSON(ERROR_CODE_SUCCESS, '',
                array_merge($sponsor_users->toJson('sponsor_users', 'toCpJson'),
                    $pursuer_users->toJson('pursuer_users', 'toCpJson'), ['is_on_the_list' => $is_on_the_list]
                ));
        }

        return $this->renderJSON(ERROR_CODE_FAIL, '暂无数据', array_merge(['sponsor_users' => []], ['pursuer_users' => []], ['is_on_the_list' => false]));
    }
}