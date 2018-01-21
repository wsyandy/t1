<?php

namespace api;

class DevicesController extends BaseController
{
    function activeAction()
    {
        if ($this->request->isPost()) {

            $attributes = $this->context();
            debug($this->params(), $this->headers(), 'context', $attributes);

            if (!checkSum($attributes['device_no']) && !isDevelopmentEnv()) {
                return $this->renderJSON(ERROR_CODE_FAIL);
            }

            $attributes['ua'] = $this->params('ua');
            $attributes['imsi'] = $this->params('imsi');

            // 1.2.1版本imei
            if (isset($attributes['imei']) && strlen($attributes['imei']) >= 20
                && strlen($attributes['imei']) <= 22 && strlen(base64_decode($attributes['imei'])) == 15
            ) {
                $attributes['imei'] = base64_decode($attributes['imei']);
                info('imei_convert', $attributes['imei'], $this->params());
            }

            $device = \Devices::active($this->currentProductChannel(), $attributes);
            if ($device) {

                $user = \Users::registerForClientByDevice($device);
                // 防止写入失败
                if (!$user->sid || !$user->device_id) {
                    $user->sid = $user->generateSid('d.');
                    $user->device_id = $device->id;
                    $user->save();

                    $device->user_id = $user->id;
                    $device->update();
                }

                return $this->renderJSON(ERROR_CODE_SUCCESS, '激活成功', array('sid' => $user->sid));
            } else {
                return $this->renderJSON(ERROR_CODE_FAIL, '激活失败', ['sid' => ""]);
            }
        } else {
            return $this->renderJSON(ERROR_CODE_FAIL, '非法调用', ['sid' => ""]);
        }
    }
}