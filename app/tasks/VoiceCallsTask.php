<?php
/**
 * Created by PhpStorm.
 * User: maoluanjuan
 * Date: 11/01/2018
 * Time: 13:54
 */

require 'CommonParam.php';

class VoiceCallsTask extends \Phalcon\Cli\Task
{
    use CommonParam;

    function testCreateAction($params = array())
    {
        $url = 'http://ctest.yueyuewo.cn/api/voice_calls';
        $user_id = fetch($params, 'user_id', 75);
        $receiver_id = fetch($params, 'receiver_id', 79);
        $user = \Users::findById($user_id);
        $receiver = \Users::findById($receiver_id);
        $body = array_merge($this->commonBody(), array('sid' => $user->sid, 'user_id' => $receiver->id));
        $res = httpPost($url, $body);
        var_dump($res);
        $res = json_decode($res, true);
        $call_no = $res['call_no'];
        $call_status = fetch($params, 'call_status', CALL_STATUS_BUSY);
        $this->updateStatus($call_no, $call_status);
    }

    function updateStatus($call_no, $call_status)
    {
        $voice_call = \VoiceCalls::findFirstByCallNo($call_no);
        if ($voice_call) {
            $voice_call->changeStatus($call_status);
        }
    }

    function testUpdateAction()
    {
        $url = 'http://www.chance_php.com/api/voice_calls/update';
        $user = \Users::findLast();

        $body = array_merge($this->commonBody(), array(
            'sid' => $user->sid,
            'call_no' => 'CN211515657626',
            'call_status' => CALL_STATUS_BUSY
        ));

        $res = httpPost($url, $body);
        var_dump($res);
    }

    function testDurationTextAction()
    {
        $voice_call = \VoiceCalls::findLast();
        echo $voice_call->duration_text;
    }

    function testCallStatusTextAction()
    {
        $voice_call = \VoiceCalls::findLast();
        $voice_call->is_send = false;
        //echo $voice_call->is_send;
        var_dump($voice_call->is_send);
        echo $voice_call->call_status_text;
    }

    function testIsHangUpAction()
    {
        $voice_call = \VoiceCalls::findLast();
        var_dump($voice_call->isHangUp());
    }
}