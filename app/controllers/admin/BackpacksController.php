<?php
/**
 * Created by PhpStorm.
 * User: admin
 * Date: 2018/5/9
 * Time: 09:37
 */

namespace admin;


class BackpacksController extends BaseController
{
    function indexAction()
    {
        $user_id = $this->params('user_id');
        $page = $this->params('page');
        $per_page = $this->params('per_page');


        $conditions = [
            'conditions' => 'user_id = :user_id:',
            'bind' => ['user_id' => $user_id],
            'order' => 'id desc'
        ];

        $backpacks = \Backpacks::findPagination($conditions, $page, $per_page);
        $this->view->backpacks = $backpacks;
        $this->view->user_id = $user_id;


    }

    function giveBackpacksAction()
    {
        $user_id = $this->params('user_id');

        if ($this->request->isPost()) {

            $target_id = $this->params('target_id');
            $number = intval($this->params('number'));

            $exist = \Backpacks::findByConditions(array("target_id" => $target_id,"user_id" => $user_id));
            $exist = $exist->toJson('exist');
            $exist = $exist['exist'][0];
            if ( 0 < $exist['number']) {

                $backpacks = \Backpacks::findFirstById($exist['id']);
                $backpacks->number = $exist['number'] + $number;
                $backpacks->update();

            } else {

                $backpacks = new \Backpacks();

                $backpacks->user_id = $user_id;
                $backpacks->number = $number;
                $backpacks->target_id = $target_id;
                $backpacks->type = 1;
                $backpacks->status = 1;
                $backpacks->save();
            }

            return $this->renderJSON(ERROR_CODE_SUCCESS, '', ['error_url' => '/admin/backpacks/index?user_id=' . $user_id]);
        }

        $this->view->user_id = $user_id;
    }
}