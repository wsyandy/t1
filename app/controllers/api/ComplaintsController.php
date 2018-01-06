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
        return $this->renderJSON(ERROR_CODE_SUCCESS, '', ['complaint_types' => \Complaints::$COMPLAINT_TYPE]);
    }

    function createAction()
    {
        $room_id = $this->params('room_id', 0);
        $complaint_type = $this->params('complaint_type');

        if (!$this->otherUserId() && !$room_id) {
            return $this->renderJSON(ERROR_CODE_FAIL, '举报对象不能为空');
        }

        if (!$complaint_type) {
            return $this->renderJSON(ERROR_CODE_FAIL, '举报类型错误');
        }

        $opts = ['room_id' => $room_id, 'respondent_id' => $this->otherUserId(), 'complaint_type' => $complaint_type];

        \Complaints::createComplaint($this->currentUser(), $opts);

        return $this->renderJSON(ERROR_CODE_SUCCESS, '');
    }
}