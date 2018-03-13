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

    static $STATUS = [STATUS_ON => '正常', STATUS_BLOCKED => '被封', STATUS_OFF => '解散'];
    static $TYPE = [UNION_TYPE_PUBLIC => '工会', UNION_TYPE_PRIVATE => '家族'];
    static $AUTH_STATUS = [AUTH_SUCCESS => '审核成功', AUTH_FAIL => '审核失败', AUTH_WAIT => '等待审核'];
    static $RECOMMEND = [STATUS_ON => '是', STATUS_OFF => '否'];

    //创建家族
    static function createPrivateUnion($user, $opts = [])
    {
        if ($user->union) {

            if (UNION_TYPE_PUBLIC == $user->union->type) {
                return [ERROR_CODE_FAIL, '您已加入工会,不能创建家族'];
            } elseif (STATUS_ON == $user->union->status) {
                return [ERROR_CODE_FAIL, '您已加入家族,不能创建家族'];
            }
        }

        $amount = 100;

        if ($user->diamond < $amount) {
            return [ERROR_CODE_FORM, '钻石余额不足'];
        }

        $name = trim(fetch($opts, 'name', '')); //家族名称
        $notice = trim(fetch($opts, 'notice', '')); //家族公告
        $need_apply = fetch($opts, 'need_apply', 0); //是否需要申请
        $avatar_file = fetch($opts, 'avatar_file'); //家族头像

        if (!file_exists($avatar_file)) {
            return [ERROR_CODE_FAIL, '头像不能为空'];
        }

        if (isBlank($name) || mb_strlen($name) > 5) {
            return [ERROR_CODE_FAIL, '家族名称有误'];
        }

        if (isBlank($notice) || mb_strlen($notice) > 50) {
            return [ERROR_CODE_FAIL, '家族公告有误'];
        }

        if (isBlank($need_apply)) {
            return [ERROR_CODE_FAIL, '家族设置有误'];
        }

        $union = new Unions();
        $union->name = $name;
        $union->notice = $notice;
        $union->need_apply = $need_apply;
        $union->product_channel_id = $user->product_channel_id;
        $union->user_id = $user->id;
        $union->auth_status = AUTH_SUCCESS;
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

        $user->union_id = $union->id;
        $user->update();

        $opts = ['remark' => '创建家族,花费钻石' . $amount . "个", 'union_id' => $union->id, 'mobile' => $user->mobile];
        $res = AccountHistories::changeBalance($user->id, ACCOUNT_TYPE_CREATE_UNION, $amount, $opts);

        if ($res) {
            return [ERROR_CODE_SUCCESS, '创建成功'];
        }

        $union->status = STATUS_OFF;
        $union->error_reason = "扣除钻石失败,创建失败";
        $union->update();

        return [ERROR_CODE_FAIL, '创建失败'];
    }

    //创建公会
    static function createPublicUnion($opts = [])
    {
        $mobile = fetch($opts, 'mobile');
        $password = fetch($opts, 'password');

        if (!$mobile || !$password) {
            return [ERROR_CODE_FAIL, '手机号或密码不能为空'];
        }

        $union = new Unions();
        $union->mobile = $mobile;
        $union->auth_status = AUTH_WAIT;
        $union->type = UNION_TYPE_PUBLIC;
        $union->password = md5($password);
        $union->save();

        if ($union->save()) {
            return [ERROR_CODE_SUCCESS, '创建失败'];
        }

        return [ERROR_CODE_FAIL, '创建失败'];
    }

    //搜索公会
    static function search($user, $page, $per_page, $opts = [])
    {
        $recommend = fetch($opts, 'recommend', 0);
        $type = fetch($opts, 'type', 0);
        $id = fetch($opts, 'id', 0);
        $name = fetch($opts, 'name', 0);
        $order = fetch($opts, 'order', '');

        if (!$type) {
            return null;
        }

        $cond = [
            'conditions' => 'type = :type: and status = :status: and auth_status',
            'bind' => ['type' => $type, 'status' => STATUS_ON, 'auth_status' => AUTH_SUCCESS],
        ];

        if ($recommend) {
            $cond['conditions'] .= " and recommend = :recommend:";
            $cond['bind']['recommend'] = $recommend;
        }

        //根据id name搜索是否需要recommend
        if ($id) {
            $cond['conditions'] .= " and id = :id:";
            $cond['bind']['id'] = $id;
        }

        if ($name) {
            $cond['conditions'] .= " and name = :name:";
            $cond['bind']['name'] = "%" . $name . "%";
        }

        if ($order) {
            $cond['order'] = $order;
        }

        if (isset($cond['order'])) {
            $cond['order'] .= ",id desc";
        } else {
            $cond['order'] .= "id desc";
        }

        $unions = Unions::findPagination($cond, $page, $per_page);

        return $unions;
    }

    //公会成员
    function users($page, $per_page, $opts = [])
    {
        $cond = ['conditions' => 'union_id = :union_id:', 'bind' => ['union_id' => $this->id]];
        $users = Users::findPagination($cond, $page, $per_page);

        return $users;
    }

    //新的公会成员
    function newUsers($page, $per_page)
    {
        return self::findUsersByCache($this->generateNewUersKey(), $page, $per_page);
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

    function generateNewUersKey()
    {
        return "union_total_users_list" . $this->id;
    }

    function generateRefusedUersKey()
    {
        return "union_refused_users_list" . $this->id;
    }

    function generateUersKey()
    {
        return "union_users_list" . $this->id;
    }

    //成员人数
    function userNum()
    {
        $cond = ['conditions' => 'union_id = :union_id:', 'bind' => ['union_id' => $this->id]];
        return Users::count($cond);
    }

    function applyJoinUnion($user)
    {
        if ($user->union_id) {
            return [ERROR_CODE_FAIL, '你已经加入其它家族'];
        }

        $db = Users::getUserDb();
        $key = $this->generateNewUersKey();

        if ($db->zscore($key, $user->id)) {
            return [ERROR_CODE_FAIL, '你已经申请该家族'];
        }

        if ($db->zadd($this->generateNewUersKey(), time(), $user->id)) {
            return [ERROR_CODE_SUCCESS, '申请成功'];
        }

        return [ERROR_CODE_FAIL, '系统异常'];
    }

    function agreeJoinUnion($user)
    {
        if (!$user->isUnionHost($this)) {
            return [ERROR_CODE_FAIL, '您无此权限'];
        }

        if ($user->union_id && $user->union_id != $this->id) {
            return [ERROR_CODE_FAIL, '该用户已经加入其它家族'];
        }

        $db = Users::getUserDb();
        $key = $this->generateUersKey();

        if ($db->zscore($key, $user->id)) {
            return [ERROR_CODE_FAIL, '该用户已经加入您的家族'];
        }

        if ($db->zadd($key, time(), $user->id)) {
            $user->union_id = $this->id;
            $user->update();

            $union_history = new UnionHistories();
            $union_history->user_id = $user->id;
            $union_history->union_id = $this->id;
            $union_history->join_at = time();
            $union_history->save();

            return [ERROR_CODE_SUCCESS, '加入成功'];
        }

        return [ERROR_CODE_FAIL, '系统异常'];
    }

    function refusedJoinUnion($user)
    {
        if (!$user->isUnionHost($this)) {
            return [ERROR_CODE_FAIL, '您无此权限'];
        }

        $db = Users::getUserDb();
        $db->zadd($this->generateRefusedUersKey(), time(), $user->id);
        return [ERROR_CODE_SUCCESS, ''];
    }

    function exitUnion($user, $opts = [])
    {
        $db = Users::getUserDb();
        $key = $this->generateUersKey();
        $db->zrem($key, $user->id);
        $exit = fetch($opts, 'exit');
        $kicking = fetch($opts, 'kicking');

        if ($kicking && !$user->isUnionHost($this)) {
            return [ERROR_CODE_FAIL, '您无此权限'];
        }

        $union_history = UnionHistories::findFirstBy(
            ['user_id' => $user->id, 'union_id' => $this->id, 'status' => STATUS_ON]);

        if ($union_history) {

            if ($exit) {
                $status = STATUS_OFF;
            } else {
                $status = STATUS_BLOCKED;
            }

            $union_history->status = $status;
            $union_history->save();
        }

        return [ERROR_CODE_SUCCESS, '退出成功'];
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
            $db->zclear($this->generateNewUersKey());
            $db->zclear($this->generateRefusedUersKey());
            $db->zclear($this->generateUersKey());
            Unions::delay()->asyncDissolutionUnion($this->id);
        }
    }

    static function asyncDissolutionUnion($union_id)
    {
        if (!$union_id) {
            info("参数错误");
            return;
        }

        $users = Users::findBy(['union_id' => $union_id]);

        foreach ($users as $user) {
            $user->union_id = 0;
            $user->update();
        }

        $union_histories = UnionHistories::findBy(['union_id' => $union_id, 'status' => STATUS_ON]);

        debug($union_id, count($users), count($union_histories));

        foreach ($union_histories as $union_history) {
            $union_history->status = STATUS_OFF;
            $union_history->update();
        }
    }

    function updateFameValue($charm_value)
    {
        $this->fame_value += $charm_value;
        $this->update();
    }
}