<?php

namespace iapi;

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

                if (!$user) {
                    return $this->renderJSON(ERROR_CODE_FAIL, t('激活失败',$this->currentUser()->lang), ['sid' => ""]);
                }

                $db = \Users::getUserDb();
                $good_num_list_key = 'good_num_list';

                if ($db->zscore($good_num_list_key, $user->id)) {
                    info("good_num", $user->id);
                    $user->user_type = USER_TYPE_SILENT;
                    $user->user_status = USER_STATUS_OFF;
                    $user->device_id = 0;
                    $device->user_id = 0;
                    $device->update();
                    $user->update();

                    $user = \Users::registerForClientByDevice($device);
                }

                // 防止写入失败
                if (!$user->sid || !$user->device_id) {
                    $user->sid = $user->generateSid('d.');
                    $user->device_id = $device->id;
                    $user->save();

                    $device->user_id = $user->id;
                    $device->update();
                }

                return $this->renderJSON(ERROR_CODE_SUCCESS,t('激活成功',$this->currentUser()->lang), ['sid' => $user->sid]);
            } else {
                return $this->renderJSON(ERROR_CODE_FAIL, t('激活失败',$this->currentUser()->lang), ['sid' => ""]);
            }
        } else {
            return $this->renderJSON(ERROR_CODE_FAIL, t('非法调用',$this->currentUser()->lang), ['sid' => ""]);
        }
    }
}