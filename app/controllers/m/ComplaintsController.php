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
    function indexAction()
    {
        $this->view->sid = $this->params('sid');
        $this->view->code = $this->params('code');
        $this->view->room_id = $this->params('room_id', 0);
        $this->view->user_id = $this->params('user_id', null);
        $this->view->complaint_types = \Complaints::$COMPLAINT_TYPE;
    }

    function createAction()
    {
        if($this->request->isAjax()) {
            $room_id = $this->params('room_id', 0);
            $user_id = $this->params('user_id', null);
            $complaint_type = $this->params('complaint_type');
            if (!$complaint_type) {
                return $this->renderJSON(ERROR_CODE_FAIL, '举报类型错误');
            }

            if (!$user_id && !$room_id) {
                return $this->renderJSON(ERROR_CODE_FAIL, '举报对象不能为空');
            }

            $opts = ['room_id' => $room_id, 'respondent_id' => $user_id, 'complaint_type' => $complaint_type];
            \Complaints::createComplaint($this->currentUser(), $opts);

            $url = '';

            if($user_id)
            {
                $url = "app://users/other_datail?user_id=" . $user_id;
            }

            if($room_id)
            {
                $url = "app://rooms/datail?id=" . $room_id;
            }
            
            $this->renderJSON(ERROR_CODE_SUCCESS,'举报成功',['error_url'=>$url]);
        }
    }
}