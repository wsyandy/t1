<?php

namespace api;

class DevicesController extends BaseController
{
    function activeAction()
    {
        if ($this->request->isPost()) {

            $attributes = $this->context();

            if (!checkSum($attributes['device_no']) && !isDevelopmentEnv()) {
                return $this->renderJSON(ERROR_CODE_FAIL);
            }

            $attributes['ua'] = $this->params('ua');
            $attributes['imsi'] = $this->params('imsi');

            $device = \Devices::active($this->currentProductChannel(), $attributes);

            if ($device) {

                $user = \Users::registerForClientByDevice($device);
                if (!$user) {
                    return $this->renderJSON(ERROR_CODE_FAIL, '激活失败,设备被封', ['sid' => ""]);
                }

                // 防止写入失败
                if (!$user->sid || !$user->device_id) {
                    $user->sid = $user->generateSid('d.');
                    $user->device_id = $device->id;
                    $user->save();

                    $device->user_id = $user->id;
                    $device->update();
                }

                return $this->renderJSON(ERROR_CODE_SUCCESS, '激活成功', ['sid' => $user->sid]);
            } else {
                return $this->renderJSON(ERROR_CODE_FAIL, '激活失败!', ['sid' => ""]);
            }
        } else {
            return $this->renderJSON(ERROR_CODE_FAIL, '非法调用', ['sid' => ""]);
        }
    }
}