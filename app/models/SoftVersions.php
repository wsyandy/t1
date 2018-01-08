<?php


class SoftVersions extends BaseModel
{

    static $STATUS = [SOFT_VERSION_STATUS_ON => '可用', SOFT_VERSION_STATUS_OFF => '不可用'];

    static $PLATFORM = ['android' => '安卓', 'ios' => "苹果"];
    static $FORCE_UPDATE = [SOFT_VERSION_FORCE_UPDATE_ON => '强制升级', SOFT_VERSION_FORCE_UPDATE_OFF => '不强制升级'];

    static $STABLE = [SOFT_VERSION_STABLE_ON => '稳定', SOFT_VERSION_STABLE_OFF => '不稳定'];

    static $files = ['file' => APP_NAME . '/soft_version/%s'];

    static $CHANNEL_PACKAGE = [0 => '非渠道包', 1 => '渠道安装包'];
    static $IS_SHOW_NAME = [0 => '否', 1 => '是'];

    /**
     * @type Operators
     */
    private $_operator;
    /**
     * @type ProductChannels
     */
    private $_product_channel;


    function mergeJson()
    {
        return ['file_url' => $this->file_url, 'product_channel_name' => $this->product_channel_name, 'operator_username' => $this->operator_username];
    }

    function getFileUrl()
    {
        if (isBlank($this->file)) {
            return null;
        }
        $url = StoreFile::getUrl($this->file);
        return $url;
    }

    function toListJson()
    {
        return ['id' => $this->id, 'file_url' => $this->file_url, 'version_name' => $this->version_name,
            'version_code' => $this->version_code, 'platform' => $this->platform, 'feature' => $this->feature,
            'force_update' => $this->force_update, 'ios_down_url' => $this->ios_down_url, 'weixin_url' => $this->weixin_url];
    }

    function afterCreate()
    {
        $this->findStableVersion();
    }

    function afterUpdate()
    {
        $this->findStableVersion();
    }

    function findStableVersion()
    {
        $conds['conditions'] = 'product_channel_id=:product_channel_id: and stable =:stable: and status=:status: and platform=:platform:';
        $conds['bind'] = ['product_channel_id' => $this->product_channel_id, 'stable' => SOFT_VERSION_STABLE_ON,
            'status' => SOFT_VERSION_STATUS_ON, 'platform' => $this->platform];
        $conds['order'] = 'version_code desc';
        $stable_version = self::findFirst($conds);

        if ($stable_version) {
            $product_channel = \ProductChannels::findFirstById($this->product_channel_id);
            if ($this->platform == 'ios') {
                $product_channel->apple_stable_version = $stable_version->version_code;
                $product_channel->update();
            }
            if ($this->platform == 'android') {
                $product_channel->android_stable_version = $stable_version->version_code;
                $product_channel->update();
            }
        }
    }

}