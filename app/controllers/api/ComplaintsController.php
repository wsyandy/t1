<?php
/**
 * Created by PhpStorm.
 * User: apple
 * Date: 2018/1/6
 * Time: 下午6:43
 */

namespace api;

class ComplaintsController extends BaseController
{
    function getComplaintTypesAction()
    {
        return $this->renderJSON(ERROR_CODE_SUCCESS, '', ['types' => \Complaints::$TYPE]);
    }

    function createAction()
    {
        $room_id = $this->params('room_id', 0);
        $type = $this->params('type');

        if (!$this->otherUserId() && !$room_id) {
            return $this->renderJSON(ERROR_CODE_FAIL, '举报对象不能为空');
        }

        if (!$type) {
            return $this->renderJSON(ERROR_CODE_FAIL, '举报类型错误');
        }

        $opts = ['room_id' => $room_id, 'respondent_id' => $this->otherUserId(), 'type' => $type];

        \Complaints::createComplaint($this->currentUser(), $opts);

        return $this->renderJSON(ERROR_CODE_SUCCESS, '');
    }
}