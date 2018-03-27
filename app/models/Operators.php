<?php


class Operators extends BaseModel
{

    static $STATUS = [OPERATOR_STATUS_NORMAL => '正常', OPERATOR_STATUS_BLOCKED => '禁用'];
    static $ROLE = [
        'admin' => '管理员', 'customer' => '客服', 'tester' => '测试', 'editor' => '编辑',
        'producter' => '产品经理',
        'product_operator' => '产品运营专员', 'product_operator_assistant' => '产品运营助理',
        'operat_manager' => '推广运营经理', 'operator' => '推广运营专员'
    ];

    static function login($username, $password)
    {
        $operator = \Operators::findFirstByUsername($username);
        if (!$operator) {
            // 如果操作表没有数据，初始化
            $count = Operators::count();
            if ($count < 1) {
                $operator = new Operators();
                $operator->username = 'admin';
                $operator->password = md5($password);
                $operator->status = OPERATOR_STATUS_NORMAL;
                $operator->role = 'admin';
                $operator->save();
            }
        }

        if ($operator && md5($password) === $operator->password) {
            return $operator;
        }

        return null;
    }

    function getMd5()
    {
        return md5($this->id . $this->username . $this->password . $this->role . $this->status . date('Ymd'));
    }

    static function auth($id, $md5)
    {
        $operator = \Operators::findFirstById($id);
        if ($operator && $md5 === $operator->md5) {
            return $operator;
        }
        return null;
    }

    function checkStatus()
    {
        if ($this->active_at + 3600 * 6 < time()) {
            return false;
        }

        if ($this->status != OPERATOR_STATUS_NORMAL) {
            return false;
        }

        return true;
    }

    function toSimpleJson()
    {
        return [
            'id' => $this->id,
            'username' => $this->username,
            'status_text' => $this->status_text,
            'role_text' => $this->role_text,
            'role' => $this->role
        ];
    }

    function isBlocked()
    {
        return $this->status == OPERATOR_STATUS_BLOCKED;
    }

    function isAdmin()
    {
        return $this->role == 'admin';
    }

    function isOperatManager()
    {
        return $this->role == 'operat_manager';
    }

    function isProductOperator()
    {
        return $this->role == 'product_operator';
    }

    function isLimitPartners()
    {
        if ($this->isAdmin() || $this->isOperatManager() || $this->isProductOperator()) {
            return false;
        }

        return true;
    }

    function getPartners()
    {

        $partners = [];
        $partner_operators = \PartnerOperators::findByOperatorId($this->id);
        foreach ($partner_operators as $partner_operator) {
            $partners[] = $partner_operator->partner;
        }

        if (!$partners && !$this->isLimitPartners()) {
            return Partners::find(['order' => 'id desc']);
        }

        return $partners;
    }

    function isSuperOperator()
    {
        if (isDevelopmentEnv()) {
            return true;
        }

        return 10 == $this->id;
    }

    function canGiveHiCoins()
    {
        if (isDevelopmentEnv()) {
            return true;
        }

        return 11 == $this->id;
    }
}