<?php

namespace api;

class DevicesController extends BaseController
{
    function activeAction()
    {
        if ($this->request->isPost()) {

            $attributes = $this->context();
            debug('context',$attributes);

            if (!checkSum($attributes['device_no']) && !isDevelopmentEnv()) {
                $this->renderJSON(ERROR_CODE_FAIL);
                return;
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

            info($this->params(), $this->headers(), $attributes);
            $device = \Devices::active($this->currentProductChannel(), $attributes);

            if ($device) {
                $this->renderJSON(ERROR_CODE_SUCCESS, '激活成功', array('sid' => $device->sid));
            } else {
                $this->renderJSON(ERROR_CODE_FAIL, '激活失败', array('sid' => ""));
            }
        } else {
            $this->renderJSON(ERROR_CODE_FAIL, '非法调用', array('sid' => ""));
        }
    }
}