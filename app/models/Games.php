<?php

class Games extends BaseModel
{

    static $STATUS = [STATUS_OFF => '关闭', STATUS_ON => '正常'];
    static $files = ['icon' => APP_NAME . '/games/icon/%s'];

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

    function toSimpleJson()
    {
        return [
            'name' => $this->name,
            'icon_url' => $this->icon_url,
            'icon_small_url' => $this->icon_small_url,
            'skip_url' => $this->url
        ];
    }
}