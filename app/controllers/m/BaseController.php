<?php
/**
 * Created by PhpStorm.
 * User: apple
 * Date: 2017/12/26
 * Time: 下午5:18
 */

namespace m;

class BaseController extends \ApplicationController
{
    function currentUserId()
    {
        $sid = $this->context('sid');

        if (isBlank($sid) || !preg_match('/^\d+s/', $sid)) {
            return null;
        }

        $user_id = intval(explode('s', $sid, 2)[0]);
        debug('user_id', $user_id);

        return $user_id;
    }

    /**
     * @return \Users
     */
    function currentUser()
    {
        $user_id = $this->currentUserId();
        if (!isset($this->_current_user) && $user_id) {
            $user = \Users::findFirstById($user_id);
            if ($user && $this->params('sid') == $user->sid) {
                $this->_current_user = $user;
            }
        }

        return $this->_current_user;
    }

    function beforeAction($dispatcher)
    {
        if (!$this->authorize()) {
            return $this->renderJSON(ERROR_CODE_NEED_LOGIN, '请登录');
        }
        if ($this->currentUser()->isBlocked()) {
            return $this->renderJSON(ERROR_CODE_FAIL, '账户状态不可用');
        }
    }

    private function authorize()
    {
        return $this->currentUser() && $this->params('sid') == $this->currentUser()->sid &&
            $this->currentUser()->mobile;
    }
}
