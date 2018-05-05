<?php
/**
 * Created by PhpStorm.
 * User: apple
 * Date: 2018/3/13
 * Time: 上午11:07
 */

namespace admin;

class UnionsController extends BaseController
{
    function indexAction()
    {
        $page = $this->params('page');
        $per_page = $this->params('per_page');
        $union_id = $this->params('union_id');
        $auth_status = $this->params('auth_status');

        $cond = ['conditions' => 'auth_status = :auth_status: and status = :status: and type = :type:',
            'bind' => ['auth_status' => $auth_status, 'status' => STATUS_ON, 'type' => UNION_TYPE_PUBLIC]];

        if ($union_id) {
            $cond = ['conditions' => 'id = ' . $union_id];
        }

        $unions = \Unions::findPagination($cond, $page, $per_page);

        $this->view->unions = $unions;
        $this->view->auth_status = $auth_status;
    }

    function editAction()
    {
        $id = $this->params('id');
        $union = \Unions::findFirstById($id);
        $room_ids = $union->room_ids;
        $user_uids = [];

        if ($room_ids) {

            $room_ids = explode(',', $room_ids);

            foreach ($room_ids as $room_id) {
                $room = \Rooms::findFirstById($room_id);

                if ($room) {
                    $user_uids[] = $room->user->uid;
                }
            }
        }

        $union->user_uids = implode(',', $user_uids);
        $this->view->union = $union;
    }

    function updateAction()
    {
        $id = $this->params('id');
        $union = \Unions::findFirstById($id);
        $this->assign($union, 'union');

        if ($union->hasChanged('status') && $union->status == STATUS_OFF) {
            list($error_code, $error_reason) = $union->dissolutionUnion($union->user);

            if (ERROR_CODE_SUCCESS != $error_code) {
                return renderJSON($error_code, $error_reason);
            }
        }

        $user_uids = trim(preg_replace('/，/', ',', $union->user_uids), ',');

        $user_uids = explode(',', $user_uids);
        $room_ids = [];

        foreach ($user_uids as $user_uid) {
            $user = \Users::findFirstByUid($user_uid);

            if ($user && $user->room_id) {

                $room_ids[] = $user->room_id;
            }
        }

        if ($room_ids) {
            $union->room_ids = implode(',', $room_ids);
        } else {
            $union->room_ids = '';
        }

        if ($union->update()) {
            return renderJSON(ERROR_CODE_SUCCESS, $room_ids);
        }

        return $this->renderJSON(ERROR_CODE_FAIL, '');
    }

    function deleteAction()
    {
        $id = $this->params('id');
        $union = \Unions::findFirstById($id);

        if ($union->delete()) {
            return $this->renderJSON(ERROR_CODE_SUCCESS, '删除成功');
        }

        return $this->renderJSON(ERROR_CODE_FAIL, '');
    }

    function usersAction()
    {
        $page = $this->params('page');
        $per_page = $this->params('page', 20);
        $id = $this->params('id');
        $users = \Users::findPagination(['conditions' => 'union_id = ' . $id], $page, $per_page);
        $this->view->users = $users;
        $this->view->id = $id;
    }

    function addUserAction()
    {
        $id = $this->params('id');
        $union = \Unions::findFirstById($id);

        if ($this->request->isPost()) {

            if (!$union) {
                return $this->renderJSON(ERROR_CODE_FAIL, "公会不存在");
            }

            $user_id = $this->params('user_id');

            $user = \Users::findFirstById($user_id);

            if (!$user) {
                return $this->renderJSON(ERROR_CODE_FAIL, "用户不存在");
            }

            if ($user->union_id) {
                if (UNION_TYPE_PRIVATE == $user->union_type) {
                    return $this->renderJSON(ERROR_CODE_FAIL, "用户已加入其它家族");
                } else {
                    return $this->renderJSON(ERROR_CODE_FAIL, "用户已加入其它公会");
                }
            }

            $db = \Users::getUserDb();
            $key = $union->generateUsersKey();

            if ($db->zscore($key, $user->id)) {
                return $this->renderJSON(ERROR_CODE_FAIL, "该用户已经加入您的家族");
            }

            $db->zadd($key, time(), $user_id);

            $user->union_id = $union->id;
            $user->union_type = $union->type;
            $user->update();

            \UnionHistories::createRecord($user_id, $union->id);

            return $this->renderJSON(ERROR_CODE_SUCCESS, "", ['error_url' => '/admin/unions/users/' . $id]);
        }

        $this->view->union = $union;
        $this->view->id = $id;
    }

    function deleteUserAction()
    {
        $user_id = $this->params('user_id');

        $user = \Users::findFirstById($user_id);

        $union = \Unions::findFirstById($user->union_id);

        if (!$user) {
            return $this->renderJSON(ERROR_CODE_FAIL, "用户不存在");
        }

        if (isDevelopmentEnv()) {
            $opts = ['exit' => 'exit'];
            $union->exitUnion($user, $opts);
        } else {
            return $this->renderJSON(ERROR_CODE_FAIL, '线上禁止此操作');
        }


        return $this->renderJSON(ERROR_CODE_SUCCESS, '删除成功');
    }

    function familyAction()
    {
        $page = $this->params('page');
        $per_page = $this->params('per_page');
        $user_id = $this->params('user_id');
        $id = $this->params('id');
        $uid = $this->params('uid');
        $status = $this->params('status', STATUS_ON);
        $auth_status = $this->params('auth_status', AUTH_SUCCESS);

        $cond = [];
        $cond['order'] = "id desc";
        $cond['conditions'] = " type = " . UNION_TYPE_PRIVATE;

        if ($id) {
            $cond['conditions'] .= " and id = " . $id;
        }

        if ($uid) {
            $cond['conditions'] .= " and uid = " . $uid;
        }

        if ($auth_status) {
            $cond['conditions'] .= " and auth_status = " . $auth_status;
        }

        if ($user_id) {
            $user = \Users::findFirstById($user_id);
            $union_id = $user->union_id;
            if (!$id && $union_id) {
                $cond['conditions'] .= " and (id = $union_id  or  user_id = $user_id) ";
            } else {
                $cond['conditions'] .= " and user_id = $user_id ";
            }
        }

        if (!$id && !$user_id && !$uid) {
            $cond['conditions'] .= " and status = " . $status;
        }

        debug($cond);

        $unions = \Unions::findPagination($cond, $page, $per_page);

        $this->view->unions = $unions;
        $this->view->status = $status;
        $this->view->auth_status = $auth_status;
    }

    function settledAmountAction()
    {
        $id = $this->params('union_id');
        $union = \Unions::findFirstById($id);

        if ($this->request->isPost()) {
            $amount = $this->params('amount');
            $union->amount = $amount;

            if ($union->update()) {
                return $this->renderJSON(ERROR_CODE_SUCCESS, '');
            }

            return $this->renderJSON(ERROR_CODE_FAIL, '添加失败');
        }

        $this->view->union = $union;
        $this->view->amount = $union->amount;
        $this->view->union_id = $id;
    }

    function dayRankListAction()
    {
        $start_at = $this->params('start_at', date('Y-m-d', beginOfDay()));

        $product_channel_id = $this->params('product_channel_id');

        $page = $this->params('page', 1);

        $per_page = $this->params('per_page', 20);

        $opts = ['product_channel_id' => $product_channel_id, 'date' => date("Ymd", strtotime($start_at))];

        $unions = \Unions::findFameValueRankList('day', $page, $per_page, $opts);

        $this->view->unions = $unions;

        $this->view->start_at = $start_at;
        $this->view->product_channels = \ProductChannels::find(['order' => 'id desc']);
        $this->view->product_channel_id = intval($product_channel_id);

    }

    function weekRankListAction()
    {
        $start_at = $this->params('start_at', date('Y-m-d', beginOfWeek()));

        $start = date("Ymd", strtotime($start_at));

        $end = date("Ymd", strtotime($start) + 6 * 86400);

        $product_channel_id = $this->params('product_channel_id');

        $opts = ['product_channel_id' => $product_channel_id, 'start' => $start, 'end' => $end];

        $page = $this->params('page', 1);

        $per_page = $this->params('per_page', 20);

        $unions = \Unions::findFameValueRankList('week', $page, $per_page, $opts);

        $this->view->unions = $unions;

        $this->view->start_at = $start_at;
        $this->view->product_channels = \ProductChannels::find(['order' => 'id desc']);
        $this->view->product_channel_id = intval($product_channel_id);

    }

    function totalRankListAction()
    {
        $page = $this->params('page');
        $per_page = $this->params('per_page');
        $unions = \Unions::findPagination(['order' => 'fame_value desc'], $page, $per_page);

        $this->view->unions = $unions;
    }

    function authAction()
    {
        $id = $this->params('id');
        $union = \Unions::findFirstById($id);

        if (!$union) {
            echo "参数非法";
            return false;
        }

        if ($this->request->isPost()) {

            $amount = $this->params('amount', 0);
            $auth_status = $this->params('auth_status');
            $union->auth_status = $auth_status;

            if ($amount) {

                if ($amount < 0) {
                    return $this->renderJSON(ERROR_CODE_FAIL, '返还金额有误');
                }

                if ($amount > $union->getCreateUnionCostAmount()) {
                    return $this->renderJSON(ERROR_CODE_FAIL, '返还金额大于 创建家族花费金额');
                }

                if (AUTH_SUCCESS == $union->auth_status) {
                    $res = \AccountHistories::changeBalance($union->user_id, ACCOUNT_TYPE_CREATE_UNION_REFUND, $amount, ['remark' => '创建家族返还钻石' . $amount]);

                    if ($res) {
                        \Chats::sendTextSystemMessage($union->user_id, "恭喜您的家族已经通过考核期，创建家族使用的" . $amount . "钻已经返还到您的账户，请注意查收~");
                    }
                }
            }

            if ($union->update()) {
                return $this->renderJSON(ERROR_CODE_SUCCESS, '', ['redirect_url' => '/admin/unions/family?status=1&auth_status=3']);
            }

            return $this->renderJSON(ERROR_CODE_FAIL, '');
        }

        $this->view->id = $id;
        $this->view->auth_status = [AUTH_SUCCESS => '审核成功', AUTH_WAIT => '等待审核'];
    }

    function roomsAction()
    {
        $id = $this->params('id');
        $union = \Unions::findFirstById($id);

        $start_at_time = $this->params('start_at_time', '');
        $end_at_time = $this->params('end_at_time', '');

        $start_at = date("Ymd", beginOfDay(strtotime($start_at_time)));
        $end_at = date("Ymd", beginOfDay(strtotime($end_at_time)));

        $user_db = \Users::getUserDb();

        if (!$start_at_time && !$end_at_time) {
            $key = 'union_room_total_amount_union_id_' . $union->id;
        } elseif ($start_at == $end_at) {
            $key = 'union_room_day_amount_' . $start_at . '_union_id_' . $union->id;
        } else {
            $month_start = date('Ymd', beginOfMonth(strtotime($start_at_time)));
            $month_end = date('Ymd', endOfMonth(strtotime($start_at_time)));
            $key = 'union_room_month_amount_start_' . $month_start . '_end_' . $month_end . '_union_id_' . $union->id;
        }

        $room_ids = $user_db->zrange($key, 0, -1);
        $data = [];
        $rooms = [];
        if ($room_ids) {

            $cond = [
                'conditions' => 'id in (' . implode(',', $room_ids) . ')',
            ];

            $rooms = \Rooms::find($cond);
        }

        $total_amount = 0;

        foreach ($rooms as $room) {
            $room->amount = $user_db->zscore($key, $room->id);
            $data[] = $room;
            $total_amount += $room->amount;
        }


        usort($data, function ($a, $b) {

            if ($a->amount == $b->amount) {
                return 0;
            }

            return $a->amount > $b->amount ? -1 : 1;
        });

        $this->view->rooms = $data;
        $this->view->start_at_time = $start_at_time;
        $this->view->end_at_time = $end_at_time;
        $this->view->total_amount = $total_amount;
        $this->view->id = $id;
    }

    function usersRankAction()
    {
        $id = $this->params('id');
        $union = \Unions::findFirstById($id);
        $start_at_time = $this->params('start_at_time', '');
        $end_at_time = $this->params('end_at_time', '');
        $start_at = date("Ymd", beginOfDay(strtotime($start_at_time)));
        $end_at = date("Ymd", beginOfDay(strtotime($end_at_time)));


        $page = $this->params('page');
        $per_page = 10;
        $user_db = \Users::getUserDb();

        if (!$start_at_time && !$end_at_time) {
            $key = 'union_user_total_wealth_rank_list_union_id_' . $union->id;
            $charm_key = 'union_user_total_charm_rank_list_union_id_' . $union->id;
            $hi_coin_key = 'union_user_total_hi_coins_rank_list_union_id_' . $union->id;
        } elseif ($start_at == $end_at) {
            $key = 'union_user_day_wealth_rank_list_' . $start_at . '_union_id_' . $union->id;
            $charm_key = 'union_user_day_charm_rank_list_' . $start_at . '_union_id_' . $union->id;
            $hi_coin_key = 'union_user_day_hi_coins_rank_list_' . $start_at . '_union_id_' . $union->id;
        } else {
            $month_start = date('Ymd', beginOfMonth(strtotime($start_at_time)));
            $month_end = date('Ymd', endOfMonth(strtotime($start_at_time)));
            $key = 'union_user_month_wealth_rank_list_start_' . $month_start . '_end_' . $month_end . '_union_id_' . $union->id;
            $charm_key = 'union_user_month_charm_rank_list_start_' . $month_start . '_end_' . $month_end . '_union_id_' . $union->id;
            $hi_coin_key = 'union_user_month_hi_coins_rank_list_start_' . $month_start . '_end_' . $month_end . '_union_id_' . $union->id;
        }

        $users = \Users::findFieldRankListByKey($charm_key, 'charm', $page, $per_page);

        info("union_stat", $key, $charm_key, $hi_coin_key);
        foreach ($users as $user) {
            $user->wealth = $user_db->zscore($key, $user->id);
            $hi_coins = $user_db->zscore($hi_coin_key, $user->id);
            $hi_coins = sprintf("%0.2f", $hi_coins / 1000);
            $user->hi_coins = $hi_coins;
        }

        $cond = [
            'conditions' => 'union_id = :union_id: and fee_type = :fee_type:',
            'bind' => ['union_id' => $union->id, 'fee_type' => HI_COIN_FEE_TYPE_RECEIVE_GIFT],
            'column' => 'hi_coins'
        ];

        if ($start_at_time) {
            $cond['conditions'] .= " and created_at >= :start:";
            $cond['bind']['start'] = beginOfDay(strtotime($start_at_time));
        }

        if ($end_at_time) {
            $cond['conditions'] .= " and created_at <= :end:";
            $cond['bind']['end'] = endOfDay(strtotime($end_at_time));
        }

        $total_hi_coins = \HiCoinHistories::sum($cond);
        $this->view->users = $users;
        $this->view->start_at_time = $start_at_time;
        $this->view->end_at_time = $end_at_time;
        $this->view->total_hi_coins = sprintf("%0.2f", $total_hi_coins);
        $this->view->id = $id;
    }

    function updatePermissionsAction()
    {
        $id = $this->params('id');
        $union = \Unions::findFirstById($id);
        $all_select_permissions = [];

        if ($union->permissions) {
            $all_select_permissions = explode(",", $union->permissions);
        }

        if ($this->request->isPost()) {

            $permissions = $this->params('permissions', []);

            if ($permissions) {
                $permissions = implode(',', $permissions);
            } else {
                $permissions = '';
            }

            $union->permissions = $permissions;

            if ($union->update()) {
                return $this->renderJSON(ERROR_CODE_SUCCESS, '');
            }

            return $this->renderJSON(ERROR_CODE_FAIL, '报存失败');
        }

        $this->view->union = $union;
        $this->view->all_select_permissions = $all_select_permissions;
        $this->view->permissions = \Unions::$PERMISSIONS;
    }

    function updateRoomIdsAction()
    {

    }
}