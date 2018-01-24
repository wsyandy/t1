<?php
/**
 * Created by PhpStorm.
 * User: sunzhimin
 * Date: 16/9/7
 * Time: 下午8:53
 */

namespace api;


class SoftVersionsController extends BaseController
{

    function upgradeAction()
    {
        $version_code = $this->context('version_code');
        $platform = $this->context('platform');
        $cond = ['conditions' => 'product_channel_id = :product_channel_id: and platform = :platform: and status = :status: and stable = :stable:'];
        $cond['bind'] = ['product_channel_id' => $this->currentProductChannel()->id, 'platform' => $platform,
            'status' => SOFT_VERSION_STATUS_ON, 'stable' => SOFT_VERSION_STABLE_ON];
        $cond['order'] = 'id desc';

        $soft_versions = \SoftVersions::find($cond);

        $result = ['has_new_version' => false];

        if (count($soft_versions) < 1) {
            debug('无版本');
            return $this->renderJSON(ERROR_CODE_SUCCESS, '没有升级', $result);
        }

        $fr = $this->context('fr');
        $ip = $this->context('ip');

        $select_soft_version = null;

        foreach ($soft_versions as $soft_version) {
            if ($soft_version->fr && $fr != $soft_version->fr || $soft_version->permit_ip && $ip != $soft_version->permit_ip) {
                debug($soft_version->permit_ip, $ip);
                continue;
            }
            $select_soft_version = $soft_version;
            break;
        }

        if (!$select_soft_version || version_compare($select_soft_version->version_code, $version_code, '<=')) {
            debug('没有升级');
            return $this->renderJSON(ERROR_CODE_SUCCESS, '没有升级', $result);
        }

        $select_soft_version->increase('updated_num');
        $result['has_new_version'] = true;
        $result = array_merge($result, $select_soft_version->toListJson());
        return $this->renderJSON(ERROR_CODE_SUCCESS, '', $result);
    }
}