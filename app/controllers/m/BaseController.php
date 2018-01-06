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
    private $_current_user;
    private $_current_device;
    private $_current_product_channel;
    public $remote_ip;


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

    function currentDeviceId()
    {

        $sid = $this->context('sid');
        if (isBlank($sid) || !preg_match('/^\d+d/', $sid)) {
            return null;
        }

        $device_id = intval(explode('d', $sid, 2)[0]);
        debug('device_id', $device_id);

        return $device_id;
    }

    /**
     * @return \Devices
     */
    function currentDevice()
    {
        if (!isset($this->_current_device) && $this->currentDeviceId()) {
            $this->_current_device = \Devices::findFirstById($this->currentDeviceId());
        }

        if (!isset($this->_current_device)) {

            $device_no = $this->context('device_no');
            $this->_current_device = \Devices::findFirst([
                'conditions' => 'device_no=:device_no: and product_channel_id=:product_channel_id:',
                'bind' => ['device_no' => $device_no, 'product_channel_id' => $this->currentProductChannelId()],
                'order' => 'id desc']);
        }

        if ($this->_current_device) {
            debug('device_id', $this->_current_device->id, $this->_current_device->device_no);
        }

        return $this->_current_device;
    }

    /**
     * @return \ProductChannels
     */
    function currentProductChannel()
    {
        $code = $this->context('code');

        if (!isset($this->_current_product_channel) && $code) {
            $this->_current_product_channel = \ProductChannels::findFirstByCodeHotCache($code);
        }

        return $this->_current_product_channel;
    }

    function currentProductChannelId()
    {
        if ($this->currentProductChannel()) {
            return $this->_current_product_channel->id;
        }
        return 0;
    }

}
