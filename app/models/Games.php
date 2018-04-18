<?php

class Games extends BaseModel
{

    static $STATUS = [STATUS_OFF => '无效', STATUS_ON => '有效'];
    static $files = ['icon' => APP_NAME . '/games/icon/%s'];

    static function getGatewayClasses()
    {
        return \gamegateway\Base::getGatewayNames();
    }

    function gateway()
    {
        $clazz = '\gamegateway\\' . $this->clazz;
        $gateway = new $clazz($this);

        return $gateway;
    }

    function getIconUrl($size = null)
    {
        if (isBlank($this->icon)) {
            return null;
        }
        $url = StoreFile::getUrl($this->icon);
        if ($size) {
            $url .= "@!" . $size;
        }
        return $url;
    }

    function getIconSmallUrl()
    {
        return $this->getIconUrl('small');
    }

    function mergeJson(){
        return [
            'icon_small_url' => $this->icon_small_url,
        ];
    }

    function toSimpleJson()
    {
        return [
            'name' => $this->name,
            'icon_url' => $this->icon_url,
            'icon_small_url' => $this->icon_small_url,
            'url' => $this->url
        ];
    }

}