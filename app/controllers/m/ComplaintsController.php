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

        $music_id = $this->params('music_id', 0);
        $room_id = $this->params('room_id', 0);
        $user_id = $this->params('user_id', null);

        $type = '';
        $opt_id = 0;
        if ($room_id) {
            $opt_id = $room_id;
            $type = COMPLAINT_ROOM;
        } else if ($user_id) {
            $opt_id = $user_id;
            $type = COMPLAINT_USER;
        } else if ($music_id) {
            $opt_id = $music_id;
            $type = COMPLAINT_MUSIC;
        }

        $this->view->opt_id = $opt_id;
        $this->view->type = $type;

        $complaint_types = \Complaints::generateComplaintType($type);
        $this->view->complaint_types = $complaint_types;
    }

    function createAction()
    {
        if ($this->request->isAjax()) {
            $opt_id = $this->params('opt_id', 0);
            $type = $this->params('type');
            $complaint_type = $this->params('complaint_type');
            if (!$complaint_type) {
                return $this->renderJSON(ERROR_CODE_FAIL, '举报类型错误');
            }

            if (!$opt_id) {
                return $this->renderJSON(ERROR_CODE_FAIL, '举报对象不能为空');
            }

            $opts = ['opt_id' => $opt_id, 'type' => $type, 'complaint_type' => $complaint_type];
            \Complaints::createComplaint($this->currentUser(), $opts);

            $this->renderJSON(ERROR_CODE_SUCCESS, '举报成功', ['error_url' => "app://back"]);
        }
    }
}