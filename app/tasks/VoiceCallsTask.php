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

    function testCreateAction()
    {
        $url = 'http://www.chance_php.com/api/voice_calls';
        $user = \Users::findLast();
        $receiver = \Users::findFirst();
        $body = array_merge($this->commonBody(), array('sid' => $user->sid, 'user_id' => $receiver->id));
        $res = httpPost($url, $body);
        var_dump($res);
    }

    function testUpdateAction()
    {
        $url = 'http://www.chance_php.com/api/voice_calls/update';
        $user = \Users::findLast();

        $body = array_merge($this->commonBody(), array(
            'sid' => $user->sid,
            'call_no' => 'CN211515650522',
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