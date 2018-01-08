<?php

class ClientThemes extends BaseModel
{

    static $STATUS = [STATUS_ON => '可用', STATUS_OFF => '不可用'];

    static $files = ['file' => APP_NAME . '/client_theme/%s'];

    static $PLATFORM = ['android' => '安卓', 'ios' => "苹果"];

    /**
     * @type ProductChannels
     */
    private $_product_channel;

    function getFileUrl()
    {
        return $url = StoreFile::getUrl($this->file);
    }

    function toListJson()
    {
        return ['id' => $this->id, 'version_name' => $this->version_name, 'version_code' => $this->version_code,
            'file_url' => $this->file_url];
    }

    function mergeJson()
    {
        return ['product_channel_name' => $this->product_channel_name];
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

        $conds['conditions'] = 'product_channel_id=:product_channel_id: and  status=:status: 
        and ios_version_code!=:ios_version_code:';
        $conds['bind'] = ['product_channel_id' => $this->product_channel_id, 'status' => STATUS_ON,
            'ios_version_code' => VERSION_CODE_FORBIDDEN];
        $conds['order'] = 'version_code desc';
        $ios_stable_version = self::findFirst($conds);

        $conds['conditions'] = 'product_channel_id=:product_channel_id: and  status=:status: 
        and android_version_code!=:android_version_code:';
        $conds['bind'] = ['product_channel_id' => $this->product_channel_id, 'status' => STATUS_ON,
            'android_version_code' => VERSION_CODE_FORBIDDEN];
        $conds['order'] = 'version_code desc';
        $android_stable_version = self::findFirst($conds);

        $product_channel = \ProductChannels::findFirstById($this->product_channel_id);
        if ($ios_stable_version) {
            $product_channel->ios_client_theme_stable_version = $ios_stable_version->version_code;
        }
        if ($android_stable_version) {
            $product_channel->android_client_theme_stable_version = $android_stable_version->version_code;
        }
        $product_channel->update();
    }
}