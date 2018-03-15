<?php

namespace admin;

class OperatorsController extends BaseController
{
    function indexAction()
    {
        $page = $this->params('page');
        if ($this->currentOperator()->isOperatManager()) {
            $operators = \Operators::findPagination(['conditions' => 'role=:role1: or role=:role2:',
                'bind' => ['role1' => 'operator', 'role2' => 'operat_manager'], 'order' => 'id desc'], $page);
        } else {
            $operators = \Operators::findPagination(['order' => 'id desc'], $page);
        }

        $this->view->operators = $operators;
    }

    function editAction()
    {
        $operator = \Operators::findFirstById($this->params('id'));
        $this->view->operator = $operator;
    }

    function updateAction()
    {
        $operator = \Operators::findFirstById($this->params('id'));
        $old_password = $operator->password;
        $this->assign($operator, 'operator');

        if ($operator->hasChanged('role') && $operator->isAdmin() && !$this->currentOperator()->isSuperOperator()) {
            return $this->renderJSON(ERROR_CODE_FAIL, '您无此权限创建admin账户,请联系技术人员');
        }

        if (isBlank($operator->password)) {
            $operator->password = $old_password;
        } else {
            $operator->password = md5($operator->password);
        }
        \OperatingRecords::logBeforeUpdate($this->currentOperator(), $operator);

        if ($operator->update()) {
            return $this->renderJSON(ERROR_CODE_SUCCESS, '更新成功', array('operator' => $operator->toSimpleJson()));
        } else {
            return $this->renderJSON(ERROR_CODE_FAIL, '更新失败');
        }
    }

    function newAction()
    {
        $operator = new \Operators();
        $this->view->operator = $operator;

    }

    function createAction()
    {
        $operator = new \Operators();
        $this->assign($operator, 'operator');

        if ($operator->isAdmin() && !$this->currentOperator()->isSuperOperator()) {
            return $this->renderJSON(ERROR_CODE_FAIL, '您无此权限创建admin账户,请联系技术人员');
        }

        //去重
        $res = \Operators::findFirstByUsername($operator->username);

        if (isBlank($res)) {
            $operator->password = md5($operator->password);
            $operator->save();
            \OperatingRecords::logAfterCreate($this->currentOperator(), $operator);
            return $this->renderJSON(ERROR_CODE_SUCCESS, '新增成功', array('operator' => $operator->toSimpleJson()));
        } else {
            return $this->renderJSON(ERROR_CODE_FAIL, '该用户名已经存在');
        }

    }

    function partnersAction()
    {
        $operator = \Operators::findFirstById($this->params('id'));
        $partner_operators = \PartnerOperators::findByOperatorId($operator->id);
        $partner_ids = [];
        foreach ($partner_operators as $partner_operator) {
            $partner_ids[] = $partner_operator->partner_id;
        }

        $this->view->operator = $operator;
        $this->view->partner_ids = $partner_ids;
        $this->view->partners = \Partners::find(['order' => 'id desc']);
    }

    function updatePartnersAction()
    {
        $operator = \Operators::findFirstById($this->params('id'));
        $partner_ids = $this->params('partner_ids');
        $partner_operators = \PartnerOperators::findByOperatorId($operator->id);

        // 删除原有的
        foreach ($partner_operators as $partner_operator) {
            $partner_operator->delete();
        }

        foreach ($partner_ids as $partner_id) {
            $partner_operator = new \PartnerOperators();
            $partner_operator->partner_id = $partner_id;
            $partner_operator->operator_id = $operator->id;
            \OperatingRecords::logBeforeUpdate($this->currentOperator(), $partner_operator);
            $partner_operator->save();
        }

        $this->renderJSON(ERROR_CODE_SUCCESS, '更新成功');
    }


}
