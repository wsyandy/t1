<?php
/**
 * Created by PhpStorm.
 * User: admin
 * Date: 2018/5/9
 * Time: 19:45
 */

class BoomConfigs extends BaseModel
{
    static $STATUS = [ STATUS_OFF=>"无效", STATUS_ON=>"有效" ];
    static $files = ['svga_image' => APP_NAME . '/boom_configs/svga_image/%s'];



    function getSvgaImageSmallUrl()
    {
        if (isBlank($this->svga_image)) {
            return '';
        }

        return StoreFile::getUrl($this->svga_image) . '@!small';
    }

}