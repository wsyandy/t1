<?php
/**
 * Created by PhpStorm.
 * User: apple
 * Date: 2018/1/9
 * Time: 上午10:15
 */
namespace m;
class ComplaintsController extends BaseController
{
    function getComplaintTypesAction()
    {
        $this->view->sid = $this->params('sid');
        $this->view->code = $this->params('code');
        $this->view->room_id = $this->params('room_id', 0);
        $this->view->user_id = $this->params('user_id', null);
        $this->view->complaint_types = \Complaints::$COMPLAINT_TYPE;
        $this->view->error_reason = $this->params('error_reason', '');
    }

    function createAction()
    {
        $room_id = $this->params('room_id', 0);
        $user_id = $this->params('user_id', null);
        $complaint_type = $this->params('complaint_type');
        if (!$complaint_type) {
            $url = $_SERVER['HTTP_REFERER'];
            $url = explode('&error_reason', $url, 2)[0];
            return $this->response->redirect($url . '&error_reason=举报类型错误');
        }

        if (!$user_id && !$room_id) {
            $url = $_SERVER['HTTP_REFERER'];
            $url = explode('&error_reason', $url, 2)[0];
            return $this->response->redirect($url . '&error_reason=举报对象不能为空');
        }

        $opts = ['room_id' => $room_id, 'respondent_id' => $user_id, 'complaint_type' => $complaint_type];
        \Complaints::createComplaint($this->currentUser(), $opts);

//        echo "<script>alert('举报成功')</script>";
        return $this->renderJSON(ERROR_CODE_SUCCESS, '举报成功');
    }
}