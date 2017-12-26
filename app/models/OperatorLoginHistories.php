<?php

class OperatorLoginHistories extends BaseModel
{
    static $OPERATOR_ROLE = [
        'admin' => '管理员', 'customer' => '客服', 'tester' => '测试', 'editor' => '编辑',
        'producter' => '产品经理',
        'product_operator' => '产品运营', 'product_operator_assistant' => '产品运营助理',
        'operat_manager' => '推广运营经理', 'operator' => '推广运营'
    ];

    /**
     * @type Operators
     */
    private $_operator;


    function afterCreate()
    {
        if ($this->ip) {
            self::delay(1)->asyncUpdateIpLocation($this->id);
        }
    }

    static function record($operator, $ip)
    {
        info($operator->id, $ip);
        $operator_login_history = new OperatorLoginHistories();
        $operator_login_history->operator_id = $operator->id;
        $operator_login_history->ip = $ip;
        $operator_login_history->login_at = time();
        $operator_login_history->save();
    }

    static function asyncUpdateIpLocation($id)
    {
        $operator = self::findFirstById($id);
        if ($operator && $operator->ip) {
            $location = \Provinces::findIpPosition($operator->ip);
            if ($location) {
                $operator->location = $location;
                $operator->update();
            }
        }
    }

}