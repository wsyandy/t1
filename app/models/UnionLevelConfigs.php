<?php

class UnionLevelConfigs extends BaseModel
{

    //图片文件
    static $files = ['icon' => APP_NAME . '/union_level_configs/image/%s'];

    static $UNION_USER_AWARD_TYPE = [AWARD_DIAMOND => '钻石', AWARD_GOLD => '金币'];

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

    function getIconBigUrl()
    {
        return $this->getIconUrl('big');
    }
}