<?php
/**
 * Created by PhpStorm.
 * User: maoluanjuan
 * Date: 11/01/2018
 * Time: 15:39
 */

namespace admin;

class VoiceCallsController extends BaseController
{
    function indexAction()
    {
        $user = \Users::findById($this->params('user_id'));
        $page = $this->params('page');
        $per_page = $this->params('per_page');

        $voice_calls = $voice_calls = \VoiceCalls::findListByUser($user, $page, $per_page);
        $this->view->voice_calls = $voice_calls;
    }
}