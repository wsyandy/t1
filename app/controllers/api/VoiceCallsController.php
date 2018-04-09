<?php
/**
 * Created by PhpStorm.
 * User: maoluanjuan
 * Date: 11/01/2018
 * Time: 11:24
 */

namespace api;

class VoiceCallsController extends BaseController
{
    function indexAction()
    {
        $page = $this->params('page');
        $per_page = $this->params('per_page');
        $voice_calls = \VoiceCalls::findListByUser($this->currentUser(), $page, $per_page);
        return $this->renderJSON(ERROR_CODE_SUCCESS, '', $voice_calls->toJson('voice_calls', 'toSimpleJson'));
    }

    function createAction()
    {
        if (isBlank($this->params('user_id')) || !preg_match('/\d+/', $this->params('user_id'))) {
            return $this->renderJSON(ERROR_CODE_FAIL, '用户不存在');
        }
        $receiver = \Users::findById($this->params('user_id'));
        if ($this->currentUserId() == $receiver->id) {
            return $this->renderJSON(ERROR_CODE_FAIL, '不能打给自己');
        }
        if (!$this->isDebug() && !$this->currentUser()->isFriend($receiver)) {
            return $this->renderJSON(ERROR_CODE_FAIL, '只能给好友拨打');
        }
        if ($receiver->isInAnyRoom()) {
            return $this->renderJSON(ERROR_CODE_FAIL, '对方正在直播间内，请稍后再拨');
        }

        //对方在房间不能打电话
        if ($this->otherUser()->isInRoom()) {
            return $this->renderJSON(ERROR_CODE_FAIL, '对方在房间内不能打电话');
        }

        $voice_call = \VoiceCalls::createVoiceCall($this->currentUser(), $receiver);
        $call_no = $voice_call->call_no;

        if ($voice_call) {
            if ($voice_call->isBusy()) {
                return $this->renderJSON(ERROR_CODE_FAIL, '对方正忙');
            }
            return $this->renderJSON(ERROR_CODE_SUCCESS, '拨打成功',
                [
                    'call_no' => $call_no,
                    'channel_name' => $call_no,
                    'channel_key' => $this->currentUser()->generateVoiceChannelKey($call_no),
                    'receiver_channel_key' => $receiver->generateVoiceChannelKey($call_no)
                ]
            );
        } else {
            return $this->renderJSON(ERROR_CODE_FAIL, '拨打失败');
        }

    }

    function updateAction()
    {
        if (isBlank($this->params('call_no')) || isBlank($this->params('call_status'))) {
            return $this->renderJSON(ERROR_CODE_FAIL, '系统错误,请重新拨打');
        }
        $call_status = intval($this->params('call_status'));
        if (!array_key_exists($call_status, \VoiceCalls::$call_status)) {
            return $this->renderJSON(ERROR_CODE_FAIL, '通话状态异常');
        }
        $voice_call = \VoiceCalls::findFirstByCallNo($this->params('call_no'));
        if (isBlank($voice_call)) {
            return $this->renderJSON(ERROR_CODE_FAIL, '用户不存在, 请重新拨打');
        }
        $voice_call->changeStatus($call_status);
        return $this->renderJSON(ERROR_CODE_SUCCESS, '更新成功');
    }

    function clearAction()
    {
        $cache = \Users::getUserDb();
        $key = 'clear_voice_calls_user_' . $this->currentUserId();
        $cache->set($key, time());

        return $this->renderJSON(ERROR_CODE_SUCCESS, '清除成功');
    }
}