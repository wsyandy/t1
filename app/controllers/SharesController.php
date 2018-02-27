<?php

/**
 * Created by PhpStorm.
 * User: apple
 * Date: 2018/2/27
 * Time: 上午10:19
 */
class SharesController extends ApplicationController
{
    function indexAction()
    {
        $share_history = \ShareHistories::findFirstById($this->params('share_history_id', 0));
        if (!$share_history) {
            return $this->renderJSON(ERROR_CODE_FAIL, '参数错误');
        }

        $share_history->view_num += 1;
        $share_history->save();
        
        $str = "第" . $share_history->view_num . "次访问";
        echo($str);
    }
}