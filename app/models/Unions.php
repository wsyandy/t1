<?php

/**
 * Created by PhpStorm.
 * User: meixinghao
 * Date: 2018/3/11
 * Time: 下午4:05
 */
class Unions extends BaseModel
{
    /**
     * @type ProductChannels
     */
    private $_product_channel;

    /**
     * @type Users
     */
    private $_user;
    /**
     * @type UnionLevelConfigs
     */
    private $_union_level;

    static $STATUS = [STATUS_ON => '正常', STATUS_BLOCKED => '被封', STATUS_OFF => '解散', STATUS_PROGRESS => '创建中'];
    static $TYPE = [UNION_TYPE_PUBLIC => '公会', UNION_TYPE_PRIVATE => '家族'];
    static $AUTH_STATUS = [AUTH_SUCCESS => '审核成功', AUTH_FAIL => '审核失败', AUTH_WAIT => '等待审核'];
    static $RECOMMEND = [STATUS_ON => '是', STATUS_OFF => '否'];
    static $NEED_APPLY = [STATUS_ON => '申请能加入', STATUS_OFF => '所有人可加入'];
    static $PERMISSIONS = ['login_backstage' => '登录后台'];

    static $UNION_LEVEL_IMAGE = ['/m/images/dengji_zero.png', '/m/images/dengji_one.png', '/m/images/dengji_two.png'
        , '/m/images/dengji_three.png', '/m/images/dengji_four.png', '/m/images/dengji_five.png', '/m/images/dengji_six.png'];

    static $UNION_LEVEL_TEXT = ['无等级', '一星级', '二星级', '三星级', '四星级', '五星级', '六星级'];

    function afterCreate()
    {
        if (!$this->uid) {
            $this->uid = $this->generateUid();
            $this->update();
        }
    }

    function afterUpdate()
    {
        if ($this->hasChanged('auth_status') && AUTH_FAIL == $this->auth_status && UNION_TYPE_PUBLIC == $this->type) {
            $this->user->union_id = 0;
            $this->user->union_type = 0;
            $this->user->update();
        }
    }

    /**
     * 产生 UID
     */
    function generateUid()
    {
        if (isDevelopmentEnv()) {
            return $this->id + 100000;
        }

        return $this->id;
    }

    //创建家族
    static function createPrivateUnion($user, $opts = [])
    {
        if ($user->union) {

            if (UNION_TYPE_PUBLIC == $user->union->type) {
                return [ERROR_CODE_FAIL, '您已加入公会,不能创建家族'];
            } elseif (STATUS_ON == $user->union->status) {
                return [ERROR_CODE_FAIL, '您已加入家族,不能创建家族'];
            }
        }

        $status = implode(',', [STATUS_ON, STATUS_PROGRESS]);

        $user_union = self::findFirst([
            'conditions' => "user_id = :user_id: and status in ({$status})",
            'bind' => ['user_id' => $user->id, 'status' => $status]
        ]);


        if ($user_union) {
            if ($user_union->type = UNION_TYPE_PUBLIC) {
                return [ERROR_CODE_FAIL, '您已创建公会'];
            } else if ($user_union->type = UNION_TYPE_PRIVATE) {
                return [ERROR_CODE_FAIL, '您已创建家族'];
            }
        }

        $amount = CREATE_UNION_AMOUNT;

        $name = trim(fetch($opts, 'name', '')); //家族名称
        $notice = trim(fetch($opts, 'notice', '')); //家族公告
        $need_apply = fetch($opts, 'need_apply', 0); //是否需要申请
        $avatar_file = fetch($opts, 'avatar_file'); //家族头像

        if (!file_exists($avatar_file)) {
            return [ERROR_CODE_FAIL, '头像不能为空'];
        }

        if ($user->diamond < $amount) {
            return [ERROR_CODE_FORM, '钻石余额不足'];
        }

        if (isBlank($name) || mb_strlen($name) > 10) {
            return [ERROR_CODE_FAIL, '家族名称不能为空或字数超过限制'];
        }

        if (isPresent($notice) && mb_strlen($notice) > 50) {
            return [ERROR_CODE_FAIL, '家族公告字数超过限制'];
        }

        $exist_union = Unions::findFirstByName($name);

        if ($exist_union) {
            return [ERROR_CODE_FAIL, '家族名称已存在'];
        }

        $union = new Unions();
        $union->name = $name;
        $union->notice = $notice;
        $union->need_apply = $need_apply;
        $union->product_channel_id = $user->product_channel_id;
        $union->user_id = $user->id;
        $union->status = STATUS_ON;
        $union->auth_status = AUTH_WAIT;
        $union->mobile = $user->mobile;
        $union->type = UNION_TYPE_PRIVATE;
        $union->avatar_status = AUTH_SUCCESS;

        $dest_filename = APP_NAME . '/unions/avatar/' . uniqid() . '.jpg';
        $res = \StoreFile::upload($avatar_file, $dest_filename);

        if (!$res) {
            return [ERROR_CODE_FAIL, '上传头像失败'];
        }

        $union->avatar = $dest_filename;
        $union->save();

        $opts = ['remark' => '创建家族,花费钻石' . $amount . "个", 'mobile' => $user->mobile];
        $res = AccountHistories::changeBalance($user, ACCOUNT_TYPE_CREATE_UNION, $amount, $opts);

        if ($res) {

            $user->union_id = $union->id;
            $user->union_type = $union->type;
            $user->update();

            $db = Users::getUserDb();
            $key = $union->generateUsersKey();
            $db->zadd($key, time(), $user->id);


            return [ERROR_CODE_SUCCESS, '创建成功'];
        }

        $union->status = STATUS_OFF;
        $union->error_reason = "扣除钻石失败,创建失败";
        $union->update();

        return [ERROR_CODE_FAIL, '创建失败'];
    }

    //创建公会
    static function createPublicUnion($user, $opts = [])
    {
        $union = new Unions();
        $union->type = UNION_TYPE_PUBLIC;
        $union->status = STATUS_PROGRESS; //创建中
        $union->user_id = $user->id;
        $union->product_channel_id = $user->product_channel_id;
        $union->save();

        if ($union->save()) {

            $user->union_id = $union->id;
            $user->union_type = $union->type;
            $user->update();

            $db = Users::getUserDb();
            $key = $union->generateUsersKey();
            $db->zadd($key, time(), $user->id);

            return [ERROR_CODE_SUCCESS, '创建成功', $union];
        }

        return [ERROR_CODE_FAIL, '创建失败', null];
    }

    static function recommend($page, $per_page)
    {
        $key = "total_union_fame_value_day_" . date("Ymd", strtotime("last day", time()));

        return self::findFameValueRankListByKey($key, $page, $per_page);
    }

    //搜索公会
    static function search($user, $page, $per_page, $opts = [])
    {
        $uid = fetch($opts, 'uid', 0);
        $type = fetch($opts, 'type', 0);
        $id = fetch($opts, 'id', 0);
        $name = fetch($opts, 'name', 0);
        $order = fetch($opts, 'order', '');

        if (!$type) {
            return null;
        }

        $cond = [
            'conditions' => 'type = :type: and status = :status: and auth_status != :auth_status:',
            'bind' => ['type' => $type, 'status' => STATUS_ON, 'auth_status' => AUTH_FAIL],
        ];

        //根据id name搜索是否需要recommend
        if ($name && $uid) {
            $cond['conditions'] .= " and (name = :name: or uid = :uid:)";
            $cond['bind']['name'] = "%" . $name . "%";
            $cond['bind']['uid'] = $uid;
        } else {
            if ($name) {
                $cond['conditions'] .= " and name like :name:";
                $cond['bind']['name'] = "%" . $name . "%";
            } else if ($uid) {
                $cond['conditions'] .= " and uid = :uid:";
                $cond['bind']['uid'] = $uid;
            }
        }

        if ($id) {
            $cond['conditions'] .= " and id = :id:";
            $cond['bind']['id'] = $id;
        }

        if ($order) {
            $cond['order'] = $order;
        }

        if (isset($cond['order'])) {
            $cond['order'] .= ",id desc";
        } else {
            $cond['order'] = "id desc";
        }

        $unions = Unions::findPagination($cond, $page, $per_page);

        return $unions;
    }

    //公会成员
    function users($page, $per_page, $opts = [])
    {
        $cond = ['conditions' => 'union_id = :union_id:', 'bind' => ['union_id' => $this->id]];
        $order = fetch($opts, 'order', '');
        $filter_id = fetch($opts, 'filter_id', '');

        if ($filter_id) {
            $cond['conditions'] .= " and id != $filter_id";
        }

        if ($order) {
            $cond['order'] = $order;
        }

        if (isset($cond['order'])) {
            $cond['order'] .= ",id desc";
        } else {
            $cond['order'] = "id desc";
        }

        debug($cond);
        $users = Users::findPagination($cond, $page, $per_page);

        return $users;
    }

    //用户申请状态
    function applicationStatus($user_id)
    {
        $user_db = Users::getUserDb();
        $agreed_key = $this->generateUsersKey();
        $refused_key = $this->generateRefusedUsersKey();

        //申请退出
        $all_apply_exit_key = $this->generateAllApplyExitUsersKey();
        $apply_exit_key = $this->generateApplyExitUsersKey();
        if ($user_db->zscore($apply_exit_key, $user_id)) {
            return 0;
        } else if ($user_db->zscore($all_apply_exit_key, $user_id)) {
            return 1;
        }

        if ($user_db->zscore($agreed_key, $user_id)) {
            return 1;
        } else if ($user_db->zscore($refused_key, $user_id)) {
            return -1;
        }

        return 0;
    }

    function isExitUnion($user_id)
    {
        $user_db = Users::getUserDb();
        $all_apply_exit_key = $this->generateAllApplyExitUsersKey();

        if ($user_db->zscore($all_apply_exit_key, $user_id)) {
            return true;
        }

        return false;
    }

    //新的公会成员
    function newUsers($page, $per_page)
    {
        $user_db = Users::getUserDb();

        $offset = $per_page * ($page - 1);

        $new_user_key = $this->generateNewUsersKey();

        $user_ids = $user_db->zrevrange($new_user_key, $offset, $offset + $per_page - 1);

        $users = Users::findByIds($user_ids);

        foreach ($users as $user) {
            $user->apply_status = $this->applicationStatus($user->id);
            $user->is_exit_union = $this->isExitUnion($user->id);
        }

        $total_entries = $user_db->zcard($new_user_key);
        $pagination = new PaginationModel($users, $total_entries, $page, $per_page);
        $pagination->clazz = 'Users';

        return $pagination;
    }

    static function findUsersByCache($key, $page, $per_page)
    {
        $user_db = Users::getUserDb();

        $offset = $per_page * ($page - 1);
        $res = $user_db->zrevrange($key, $offset, $offset + $per_page - 1, 'withscores');
        $union_ids = [];
        $times = [];

        foreach ($res as $union_id => $time) {
            $union_ids[] = $union_id;
            $times[$union_id] = $time;
        }

        $unions = Unions::findByIds($union_ids);

        foreach ($unions as $union) {
            $union->created_at = fetch($times, $union->id);
        }

        $total_entries = $user_db->zcard($key);
        $pagination = new PaginationModel($unions, $total_entries, $page, $per_page);
        $pagination->clazz = 'Unions';

        return $pagination;
    }

    //显示是否有新申请
    function generateNewApplyNumKey()
    {
        return "new_apply_num_union_id_" . $this->id;
    }

    //所有的申请用户(解散 删除)(申请退出不删除)
    function generateNewUsersKey()
    {
        return "union_total_users_list" . $this->id;
    }

    //监测用户是否已经申请加入家族 (通过,拒绝,解散 删除)
    function generateCheckUsersKey()
    {
        return "union_check_users_list" . $this->id;
    }

    //已拒绝加入的用户
    function generateRefusedUsersKey()
    {
        return "union_refused_users_list" . $this->id;
    }

    //申请退出用户(all)
    function generateAllApplyExitUsersKey()
    {
        return "union_all_apply_exit_users_list" . $this->id;
    }

    //申请退出用户(申请状态的用户)
    function generateApplyExitUsersKey()
    {
        return "union_apply_exit_users_list" . $this->id;
    }

    //在家族中所有用户
    function generateUsersKey()
    {
        return "union_users_list" . $this->id;
    }

    //成员人数
    function userNum()
    {
        $user_db = Users::getUserDb();
        return $user_db->zcard($this->generateUsersKey());
    }

    function applyJoinUnion($user)
    {
        if ($user->union_id) {
            return [ERROR_CODE_FAIL, '你已经加入其它家族'];
        }

        if ($this->status != STATUS_ON) {
            return [ERROR_CODE_FAIL, '家族错误'];
        }

        $db = Users::getUserDb();
        $key = $this->generateNewUsersKey();

        $check_key = $this->generateCheckUsersKey();

        if ($db->zscore($check_key, $user->id)) {
            return [ERROR_CODE_FAIL, '你已经申请该家族'];
        }

        //删除退出的
        $db->zrem($this->generateApplyExitUsersKey(), $user->id);
        $db->zrem($this->generateAllApplyExitUsersKey(), $user->id);

        $db->zrem($this->generateRefusedUsersKey(), $user->id);

        if ($this->need_apply == 0) {
            list($error_code, $err_reason) = $this->agreeJoinUnion($this->user, $user);
            return [$error_code, $err_reason];
        }

        $expire = 3600 * 24 * 7;

        if (isDevelopmentEnv()) {
            $expire = 60;
        }

        $db->setex($this->generateNewApplyNumKey(), $expire, 1);

        $db->zadd($key, time(), $user->id);
        $db->zadd($check_key, time(), $user->id);

        return [ERROR_CODE_SUCCESS, '您的家族申请已提交，请耐心等待'];
    }

    function agreeJoinUnion($union_host, $user)
    {
        if (!$union_host->isUnionHost($this)) {
            return [ERROR_CODE_FAIL, '您无此权限'];
        }

        if ($user->union_id && $user->union_id != $this->id) {
            return [ERROR_CODE_FAIL, '该用户已经加入其它家族'];
        }


        $db = Users::getUserDb();
        $key = $this->generateUsersKey();

        if ($db->zscore($key, $user->id)) {
            return [ERROR_CODE_FAIL, '该用户已经加入您的家族'];
        }

        if ($db->zadd($key, time(), $user->id)) {

            $db->zrem($this->generateRefusedUsersKey(), $user->id);
            $db->zrem($this->generateCheckUsersKey(), $user->id);

            $user->union_id = $this->id;
            $user->union_type = $this->type;
            $user->update();

            if ($this->type == UNION_TYPE_PRIVATE && $this->need_apply == STATUS_ON) {
                $content = "恭喜您，" . "$union_host->nickname" . "已同意您的申请，您现在已经是家族中的一员了。";
                Chats::sendTextSystemMessage($user, $content);
            }

            return [ERROR_CODE_SUCCESS, '成功加入家族'];
        }

        return [ERROR_CODE_FAIL, '系统异常'];
    }

    function refuseJoinUnion($union_host, $user)
    {
        if (!$union_host->isUnionHost($this)) {
            return [ERROR_CODE_FAIL, '您无此权限'];
        }

        $db = Users::getUserDb();

        if ($db->zscore($this->generateUsersKey(), $user->id)) {
            return [ERROR_CODE_FAIL, '您已同意'];
        }

        if ($db->zscore($this->generateRefusedUsersKey(), $user->id)) {
            return [ERROR_CODE_FAIL, '您已拒绝'];
        }

        $db->zadd($this->generateRefusedUsersKey(), time(), $user->id);
        $db->zrem($this->generateCheckUsersKey(), $user->id);

        $content = "$union_host->nickname" . "拒绝了您的申请，别灰心，试试其他的家族吧！";
        Chats::sendTextSystemMessage($user, $content);

        return [ERROR_CODE_SUCCESS, '拒绝成功'];
    }

    function applyExitUnion($user)
    {
        if (isBlank($user->union_id) || $user->union_id != $this->id) {
            return [ERROR_CODE_FAIL, '您已不再此家族'];
        }

        if ($user->isUnionHost($this)) {
            return [ERROR_CODE_FAIL, '家族长不能单独退出家族'];
        }

        $db = Users::getUserDb();

        if ($db->zscore($this->generateApplyExitUsersKey(), $user->id)) {
            return [ERROR_CODE_FAIL, '你已申请退出,请耐心等待！'];
        }

        $union_history = UnionHistories::findFirstBy(['user_id' => $user->id, 'union_id' => $this->id, 'status' => STATUS_ON],
            'id desc');

        if ($union_history) {
            $union_history->status = STATUS_PROGRESS;
            $union_history->apply_exit_at = time();
            $union_history->save();
        }

        //申请退出记录
        if (!$db->zscore($this->generateNewUsersKey(), $user->id)) {
            $db->zadd($this->generateNewUsersKey(), time(), $user->id);
        }

        $db->zadd($this->generateAllApplyExitUsersKey(), time(), $user->id);
        $db->zadd($this->generateApplyExitUsersKey(), time(), $user->id);


        $user_system_content = "如果家族会长同意可立即退出家族，如果家族长未审批，7天后自动退出家族";
        //Chats::sendTextSystemMessage($user, $user_system_content);

        $union_system_content = "{$user->nickname}申请退出家族";
        Chats::sendTextSystemMessage($this->user_id, $union_system_content);

        return [ERROR_CODE_SUCCESS, '退出家族，需要会长同意，请耐心等待。如会长不同意，7天之后自动退出!'];

    }

    function confirmExitUnion($user, $from)
    {
        if (isBlank($user->union_id) || $user->union_id != $this->id) {
            return [ERROR_CODE_FAIL, '此用户已不再此家族'];
        }

        $union_history = UnionHistories::findFirstBy(['user_id' => $user->id, 'union_id' => $this->id, 'status' => STATUS_PROGRESS],
            'id desc');

        if ($union_history) {
            $union_history->status = STATUS_OFF;
            $union_history->exit_at = time();
            $union_history->save();
        }

        $user->union_id = 0;
        $user->union_type = 0;
        $user->union_charm_value = 0;
        $user->union_wealth_value = 0;


        $db = Users::getUserDb();
        $db->zrem($this->generateUsersKey(), $user->id);
        $db->zrem($this->generateApplyExitUsersKey(), $user->id);
        $db->zrem($this->generateRefusedUsersKey(), $user->id);
        $db->zrem($this->generateNewUsersKey(), $user->id);
        $db->zrem($this->generateCheckUsersKey(), $user->id);
        $db->zrem($this->generateAllApplyExitUsersKey(), $user->id);

        $content = ['agree' => "您的家族会长已同意您的退出家族申请", 'auto' => "您已经退出了{$this->name}家族"];
        Chats::sendTextSystemMessage($user, $content[$from]);

        $user->update();
        return [ERROR_CODE_SUCCESS, '操作成功'];

    }

    function exitUnion($user, $opts = [], $union_host = null)
    {
        if (isBlank($user->union_id) || $user->union_id != $this->id) {
            return [ERROR_CODE_FAIL, '此用户已不再此家族'];
        }

        $db = Users::getUserDb();
        $exit = fetch($opts, 'exit');
        $kicking = fetch($opts, 'kicking');

        if ($user->isUnionHost($this)) {
            return [ERROR_CODE_FAIL, '家族长不能单独退出家族'];
        }

        if ($kicking && !$union_host->isUnionHost($this)) {
            return [ERROR_CODE_FAIL, '您无此权限'];
        }

        $status = implode(',', [STATUS_ON, STATUS_PROGRESS]);

        $cond = [
            'conditions' => "union_id = :union_id: and user_id = :user_id: and status in ({$status})",
            'bind' => ['union_id' => $this->id, 'user_id' => $user->id, 'status' => $status],
            'order' => 'id desc'
        ];

        $union_history = UnionHistories::findFirst($cond);


        $expire_at = time() - 86400 * 7;

        if (isDevelopmentEnv()) {
            $expire_at = time() - 60;
        }

        if (!$kicking && $union_history->join_at > $expire_at) {
            return [ERROR_CODE_FAIL, '加入家族后,需要一周后才能退出哦~'];
        }

        $db->zrem($this->generateUsersKey(), $user->id);
        $db->zrem($this->generateRefusedUsersKey(), $user->id);
        $db->zrem($this->generateNewUsersKey(), $user->id);
        $db->zrem($this->generateCheckUsersKey(), $user->id);
        $db->zrem($this->generateApplyExitUsersKey(), $user->id);
        $db->zrem($this->generateAllApplyExitUsersKey(), $user->id);

        if ($union_history) {

            if ($exit) {
                $status = STATUS_OFF;
            } else {
                $status = STATUS_BLOCKED;
            }

            $union_history->status = $status;
            $union_history->exit_at = time();
            $union_history->save();
        } else {
            info('no union_history', $this->id, $user->id);
        }

        $user->union_id = 0;
        $user->union_type = 0;
        $user->union_charm_value = 0;
        $user->union_wealth_value = 0;

        if ($this->type == UNION_TYPE_PRIVATE) {

            if ($kicking) {
                $content = "$union_host->nickname" . "已将您请出了" . "$this->name" . "家族";
                Chats::sendTextSystemMessage($user, $content);
            } else {
                $content = "$user->nickname" . "已经退出了家族";
                Chats::sendTextSystemMessage($this->user_id, $content);
            }
        }

        $user->update();

        return [ERROR_CODE_SUCCESS, '操作成功'];
    }

    //解散公会
    function dissolutionUnion($user)
    {
        if (!$user->isUnionHost($this)) {
            return [ERROR_CODE_FAIL, '您无此权限'];
        }

        $this->status = STATUS_OFF;

        if ($this->update()) {
            $db = Users::getUserDb();
            $db->zclear($this->generateNewUsersKey());
            $db->zclear($this->generateRefusedUsersKey());
            $db->zclear($this->generateUsersKey());
            $db->zclear($this->generateCheckUsersKey());
            $db->zclear($this->generateApplyExitUsersKey());
            $db->zclear($this->generateAllApplyExitUsersKey());

            //删排行榜中排名
            $opts = ['product_channel_id' => $this->product_channel_id];

            $week_key = self::generateFameValueRankListKey('week', $opts);
            $day_key = self::generateFameValueRankListKey('day', $opts);

            $opts['date'] = date('Ymd', strtotime("last day"));

            $last_day_key = self::generateFameValueRankListKey('day', $opts);

            $db->zrem($last_day_key, $this->id);
            $db->zrem($day_key, $this->id);
            $db->zrem($week_key, $this->id);

            Unions::delay()->asyncDissolutionUnion($this->id);
        }
        return [ERROR_CODE_SUCCESS, ''];
    }

    static function asyncDissolutionUnion($union_id)
    {
        if (!$union_id) {
            info("参数错误");
            return;
        }

        $users = Users::findBy(['union_id' => $union_id]);

        $union = self::findFirstById($union_id);

        foreach ($users as $user) {

            if ($union->type == UNION_TYPE_PRIVATE && !$user->isUnionHost($union)) {
                $content = "您的家族解散了，快去看看其它家族吧！";
                Chats::sendTextSystemMessage($user, $content);
            }

            $user->union_id = 0;
            $user->union_type = 0;
            //清空家族土豪值，声望值
            $user->union_charm_value = 0;
            $user->union_wealth_value = 0;
            $user->update();
        }

        $status = implode(',', [STATUS_ON, STATUS_PROGRESS]);

        $cond = [
            'conditions' => "union_id = :union_id: and status in ({$status})",
            'bind' => ['union_id' => $union_id, 'status' => $status]
        ];

        $union_histories = UnionHistories::find($cond);

        debug($union_id, count($users), count($union_histories));

        foreach ($union_histories as $union_history) {
            $union_history->status = STATUS_OFF;
            $union_history->exit_at = time();
            $union_history->update();
        }
    }

    static function updateFameValue($value, $id, $opts = [])
    {
        $lock_key = "update_union_fame_lock_" . $id;
        $lock = tryLock($lock_key);
        $union = self::findFirstById($id);
        $union->fame_value += $value;
        $union->update();
        $union->updateFameRankList($value, $opts);
        unlock($lock);
    }

    function updateFameRankList($value, $opts = [])
    {
        if ($value > 0) {

            $db = Users::getUserDb();
            $time = fetch($opts, 'time', time());
            $date = date("Ymd", $time);
            $start = date("Ymd", beginOfWeek($time));
            $end = date("Ymd", endOfWeek($time));

            $week_key = self::generateFameValueRankListKey('week', ['date' => $date]);
            $day_key = self::generateFameValueRankListKey('day', ['start' => $start, 'end' => $end]);

            $db->zincrby($day_key, $value, $this->id);
            $db->zincrby($day_key . "_" . $this->product_channel_id, $value, $this->id);
            $db->zincrby($week_key, $value, $this->id);
            $db->zincrby($week_key . "_" . $this->product_channel_id, $value, $this->id);
        }
    }

    function unionFameRank($list_type)
    {
        $db = Users::getUserDb();

        $key = self::generateFameValueRankListKey($list_type);

        $rank = $db->zrrank($key, $this->id);

        if ($rank === null) {
            return 0;
        }

        return $rank + 1;
    }

    static function generateFameValueRankListKey($list_type, $opts = [])
    {
        switch ($list_type) {
            case 'day': {
                $date = fetch($opts, 'date', date('Ymd'));

                $key = "total_union_fame_value_day_" . $date;
                break;
            }
            case 'week': {
                $start = fetch($opts, 'start', date("Ymd", beginOfWeek()));
                $end = fetch($opts, 'end', date("Ymd", endOfWeek()));
                $key = "total_union_fame_value_" . $start . "_" . $end;
                break;
            }
            default:
                return '';
        }

        debug($key);

        return $key;
    }

    static function findFameValueRankList($list_type, $page, $per_page, $opts = [])
    {
        $key = self::generateFameValueRankListKey($list_type, $opts);

        return self::findFameValueRankListByKey($key, $page, $per_page);
    }

    static function findFameValueRankListByKey($key, $page, $per_page)
    {
        debug($key);

        $db = Users::getUserDb();

        $offset = ($page - 1) * $per_page;

        $result = $db->zrevrange($key, $offset, $offset + $per_page - 1, 'withscores');
        $total_entries = $db->zcard($key);

        $ids = [];
        $fame_values = [];
        foreach ($result as $union_id => $fame_value) {
            $ids[] = $union_id;
            $fame_values[$union_id] = $fame_value;
        }

        $unions = Unions::findByIds($ids);

        $rank = $offset + 1;
        foreach ($unions as $union) {
            $union->fame_value = $fame_values[$union->id];
            $union->rank = $rank;
            $rank += 1;
        }

        $pagination = new PaginationModel($unions, $total_entries, $page, $per_page);
        $pagination->clazz = 'Unions';

        return $pagination;
    }

    function getAvatarUrl()
    {
        if (isBlank($this->avatar)) {
            return null;
        }

        return StoreFile::getUrl($this->avatar);
    }

    function getAvatarSmallUrl()
    {
        if (isBlank($this->avatar)) {
            return null;
        }

        return StoreFile::getUrl($this->avatar) . '@!small';
    }

    function toSimpleJson()
    {
        $data = [
            'id' => $this->id,
            'uid' => $this->uid,
            'name' => $this->name,
            'fame_value' => $this->fame_value,
            'user_num' => $this->user_num,
            'avatar_url' => $this->avatar_url,
            'avatar_small_url' => $this->avatar_small_url,
            'union_level'=>$this->union_level
        ];

        if (isset($this->url)) {
            $data['url'] = $this->url;
        }

        return $data;
    }


    function updateProfile($opts)
    {
        if (count($opts) < 1) {
            return;
        }

        foreach ($opts as $filed => $value) {

            if ($filed == 'name' && $this->type == UNION_TYPE_PRIVATE && (isBlank($value) || mb_strlen($value) > 10)) {
                continue;
            } else if ($filed == 'notice' && (isPresent($value) && mb_strlen($value) > 50)) {
                continue;
            }

            $this->$filed = $value;
        }

        $this->update();
    }

    function updateAvatar($filename)
    {
        $old_avatar = $this->avatar;
        $dest_filename = APP_NAME . '/unions/avatar/' . uniqid() . '.jpg';
        $res = \StoreFile::upload($filename, $dest_filename);

        if ($res) {
            $this->avatar = $dest_filename;
            $this->avatar_status = AUTH_SUCCESS;
            if ($this->update()) {
                //  删除老头像
                if ($old_avatar) {
                    //\StoreFile::delete($old_avatar);
                }
            }
        }
    }

    function isNormal()
    {
        return $this->status == STATUS_ON;
    }

    function isAuthSuccess()
    {
        return $this->auth_status == AUTH_SUCCESS;
    }

    function needUpdateProfile()
    {
        if (isBlank($this->name) || isBlank($this->id_name) || isBlank($this->id_no) || isBlank($this->alipay_account)) {
            return true;
        }

        return false;
    }

    function isBlocked()
    {
        return $this->status == STATUS_BLOCKED;
    }


    function newApplyNum()
    {
        $db = Users::getUserDb();
        return intval($db->get($this->generateNewApplyNumKey()));
    }

    function clearNewApplyNum()
    {
        $user_db = Users::getUserDb();
        $user_db->del($this->generateNewApplyNumKey());
    }

    function getWaitWithdrawAmount()
    {
        return $this->amount - $this->frozen_amount;
    }

    //创建家族花费钻石数额
    function getCreateUnionCostAmount()
    {
        $user_id = $this->user_id;

        $account_history = AccountHistories::findFirstBy(
            [
                'user_id' => $user_id, 'fee_type' => ACCOUNT_TYPE_CREATE_UNION
            ], 'id desc');

        if ($account_history) {
            return abs($account_history->amount);
        }

        return 0;
    }

    //解散时间
    function getDismissedAtText()
    {
        if ($this->status == STATUS_OFF) {

            $union_history = UnionHistories::findFirstBy(['union_id' => $this->id, 'user_id' => $this->user_id]);

            if ($union_history) {
                return $union_history->exit_at_text;
            }
        }

        return '';
    }

    function canLoginBackstage()
    {
        if (!$this->permissions) {
            return false;
        }

        if (in_array('login_backstage', explode(',', $this->permissions))) {
            return true;
        }

        return false;
    }

    function statSender($stat_at)
    {
        $cond = [
            'conditions' => 'sender_union_id = :union_id: and created_at >= :start: and created_at <= :end: and 
                    status = :status: and pay_type = :pay_type:',
            'bind' => ['union_id' => $this->id, 'status' => GIFT_ORDER_STATUS_SUCCESS, 'pay_type' => GIFT_PAY_TYPE_DIAMOND,
                'start' => beginOfDay($stat_at), 'end' => endOfDay($stat_at)],
            'columns' => 'distinct sender_id'
        ];

        $sender_gift_orders = GiftOrders::find($cond);

        if (count($sender_gift_orders) > 0) {

            $room_db = Rooms::getRoomDb();

            $month_start = date("Ymd", beginOfMonth($stat_at));
            $month_end = date("Ymd", endOfMonth($stat_at));
            $day_date = date("Ymd", $stat_at);

            $total_key = 'union_user_total_wealth_rank_list_union_id_' . $this->id;
            $month_key = 'union_user_month_wealth_rank_list_start_' . $month_start . '_end_' . $month_end . '_union_id_' . $this->id;
            $day_key = 'union_user_day_wealth_rank_list_' . $day_date . '_union_id_' . $this->id;

            foreach ($sender_gift_orders as $sender_gift_order) {

                $sender_id = $sender_gift_order->sender_id;

                $amount = GiftOrders::sum([
                    'conditions' => 'sender_union_id = :union_id: and sender_id = :sender_id: and created_at >= :start: and created_at <= :end: and 
                    status = :status: and pay_type = :pay_type:',
                    'bind' => ['union_id' => $this->id, 'status' => GIFT_ORDER_STATUS_SUCCESS, 'pay_type' => GIFT_PAY_TYPE_DIAMOND,
                        'start' => beginOfDay($stat_at), 'end' => endOfDay($stat_at), 'sender_id' => $sender_id],
                    'column' => 'amount'
                ]);

                if ($amount > 0) {
                    $room_db->zincrby($total_key, $amount, $sender_id);
                    $room_db->zincrby($month_key, $amount, $sender_id);
                    $room_db->zincrby($day_key, $amount, $sender_id);
                }

                info('sender_union_id', $this->id, date("Ymd", $stat_at), $total_key, $month_key, $day_key, $amount);
            }
        }
    }

    function statUser($stat_at)
    {
        $cond = [
            'conditions' => 'receiver_union_id = :union_id: and created_at >= :start: and created_at <= :end:
                    and status = :status: and pay_type = :pay_type:',
            'bind' => ['union_id' => $this->id, 'status' => GIFT_ORDER_STATUS_SUCCESS, 'pay_type' => GIFT_PAY_TYPE_DIAMOND,
                'start' => beginOfDay($stat_at), 'end' => endOfDay($stat_at)],
            'columns' => 'distinct user_id'
        ];

        $user_gift_orders = GiftOrders::find($cond);

        if (count($user_gift_orders) > 0) {

            $room_db = Rooms::getRoomDb();

            $month_start = date("Ymd", beginOfMonth($stat_at));
            $month_end = date("Ymd", endOfMonth($stat_at));
            $day_date = date("Ymd", $stat_at);

            $total_key = 'union_user_total_charm_rank_list_union_id_' . $stat_at->id;
            $month_key = 'union_user_month_charm_rank_list_start_' . $month_start . '_end_' . $month_end . '_union_id_' . $this->id;
            $day_key = 'union_user_day_charm_rank_list_' . $day_date . '_union_id_' . $this->id;

            foreach ($user_gift_orders as $user_gift_order) {

                $user_id = $user_gift_order->user_id;

                $amount = GiftOrders::sum([
                    'conditions' => 'receiver_union_id = :union_id: and user_id = :user_id: and created_at >= :start: and created_at <= :end: and 
                    status = :status: and pay_type = :pay_type:',
                    'bind' => ['union_id' => $this->id, 'status' => GIFT_ORDER_STATUS_SUCCESS, 'pay_type' => GIFT_PAY_TYPE_DIAMOND,
                        'start' => beginOfDay($stat_at), 'end' => endOfDay($stat_at), 'user_id' => $user_id],
                    'column' => 'amount'
                ]);

                if ($amount > 0) {
                    $room_db->zincrby($total_key, $amount, $user_id);
                    $room_db->zincrby($month_key, $amount, $user_id);
                    $room_db->zincrby($day_key, $amount, $user_id);
                }


                info('receiver_union_id', $this->id, $day_date, $total_key, $month_key, $day_key, $amount);
            }
        }
    }

    function statRoom($stat_at)
    {
        $room_cond = [
            'conditions' => 'room_union_id = :union_id: and created_at >= :start: and created_at <= :end: 
                    and status = :status: and pay_type = :pay_type: and room_id > 0 and gift_type = :gift_type:',
            'bind' => ['union_id' => $this->id, 'status' => GIFT_ORDER_STATUS_SUCCESS, 'pay_type' => GIFT_PAY_TYPE_DIAMOND,
                'start' => beginOfDay($stat_at), 'end' => endOfDay($stat_at), 'gift_type' => GIFT_TYPE_COMMON],
            'columns' => 'distinct room_id'
        ];

        $room_gift_orders = GiftOrders::find($room_cond);

        if (count($room_gift_orders) > 0) {

            $room_db = Rooms::getRoomDb();

            $month_start = date("Ymd", beginOfMonth($stat_at));
            $month_end = date("Ymd", endOfMonth($stat_at));
            $day_date = date("Ymd", $stat_at);

            $total_key = 'union_room_total_amount_union_id_' . $this->id;
            $month_key = 'union_room_month_amount_start_' . $month_start . '_end_' . $month_end . '_union_id_' . $this->id;
            $day_key = 'union_room_day_amount_' . $day_date . '_union_id_' . $this->id;

            foreach ($room_gift_orders as $room_gift_order) {

                $room_id = $room_gift_order->room_id;

                if ($room_id) {

                    $amount = GiftOrders::sum([
                        'conditions' => 'room_union_id = :union_id: and created_at >= :start: and created_at <= :end: 
                    and status = :status: and pay_type = :pay_type: and gift_type = :gift_type: and room_id = :room_id:',
                        'bind' => ['union_id' => $this->id, 'status' => GIFT_ORDER_STATUS_SUCCESS, 'pay_type' => GIFT_PAY_TYPE_DIAMOND,
                            'start' => beginOfDay($stat_at), 'end' => endOfDay($stat_at), 'gift_type' => GIFT_TYPE_COMMON, 'room_id' => $room_id],
                        'column' => 'amount'
                    ]);

                    if ($amount > 0) {
                        $room_db->zincrby($total_key, $amount, $room_id);
                        $room_db->zincrby($month_key, $amount, $room_id);
                        $room_db->zincrby($day_key, $amount, $room_id);
                    }

                    info("room_union_id", $this->id, $day_date, $total_key, $month_key, $day_key, $room_gift_order->room_id, $this->id, $amount);
                }
            }
        }
    }

    function statUserHiCoins($stat_at)
    {
        $hi_coin_histories = HiCoinHistories::find([
            'conditions' => 'created_at >= :start: and created_at <= :end: and union_id = :union_id: and fee_type = :fee_type:',
            'bind' => [
                'start' => beginOfDay($stat_at),
                'end' => endOfDay($stat_at),
                'fee_type' => HI_COIN_FEE_TYPE_RECEIVE_GIFT,
                'union_id' => $this->id
            ],
            'columns' => 'distinct user_id'
        ]);

        if (count($hi_coin_histories) > 0) {

            $room_db = Rooms::getRoomDb();

            $month_start = date("Ymd", beginOfMonth($stat_at));
            $month_end = date("Ymd", endOfMonth($stat_at));
            $day_date = date("Ymd", $stat_at);


            $total_key = 'union_user_total_hi_coins_rank_list_union_id_' . $this->id;
            $month_key = 'union_user_month_hi_coins_rank_list_start_' . $month_start . '_end_' . $month_end . '_union_id_' . $this->id;
            $day_key = 'union_user_day_hi_coins_rank_list_' . $day_date . '_union_id_' . $this->id;

            foreach ($hi_coin_histories as $hi_coin_history) {

                $user_id = $hi_coin_history->user_id;

                $hi_coins = HiCoinHistories::sum([
                    'conditions' => 'created_at >= :start: and created_at <= :end: and union_id = :union_id:
                        and fee_type = :fee_type: and user_id = :user_id:',
                    'bind' => [
                        'start' => beginOfDay($stat_at),
                        'end' => endOfDay($stat_at),
                        'fee_type' => HI_COIN_FEE_TYPE_RECEIVE_GIFT,
                        'union_id' => $this->id,
                        'user_id' => $user_id
                    ],
                    'column' => 'hi_coins'
                ]);

                $hi_coins = intval($hi_coins * 1000);

                if ($hi_coins > 0) {
                    $room_db->zincrby($total_key, $hi_coins, $user_id);
                    $room_db->zincrby($month_key, $hi_coins, $user_id);
                    $room_db->zincrby($day_key, $hi_coins, $user_id);
                }

                echoLine('hi_coins', $this->id, $day_date, $total_key, $month_key, $day_key, $hi_coins);
            }
        }
    }
}