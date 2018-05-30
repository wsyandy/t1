<?php
/**
 * Created by PhpStorm.
 * User: admin
 * Date: 2018/5/9
 * Time: 19:45
 */

class BoomConfigs extends BaseModel
{
    static $STATUS = [STATUS_OFF => "无效", STATUS_ON => "有效"];
    static $files = ['svga_image' => APP_NAME . '/boom_configs/svga_image/%s'];

    function afterCreate()
    {
        $db = \Rooms::getRoomDb();
        $db->hmset('boom_config_cache_id' . $this->id, ['id' => $this->id, 'start_value' => $this->start_value,
            'total_value' => $this->total_value, 'svga_image_small_url' => $this->svga_image_small_url]);
    }

    function afterUpdate()
    {
        $db = \Rooms::getRoomDb();
        $db->hmset('boom_config_cache_id' . $this->id, ['id' => $this->id, 'start_value' => $this->start_value,
            'total_value' => $this->total_value, 'svga_image_small_url' => $this->svga_image_small_url]);
    }

    function mergeJson()
    {
        return [
            'status_text' => $this->status_text,
            'created_at_text' => $this->created_at_text,
            'svga_image_small_url' => $this->svga_image_small_url,
        ];
    }

    function getSvgaImageUrl()
    {
        if (isBlank($this->svga_image)) {
            return '';
        }

        return StoreFile::getUrl($this->svga_image);
    }

    static function getBoomConfig($room = null)
    {
        if ($room && $room->user->isCompanyUser()) {
            return self::getTestBoomConfig();
        }

        $boom_config = BoomConfigs::findFirst(
            [
                'conditions' => 'status = :status:',
                'bind' => ['status' => STATUS_ON],
                'order' => 'id desc'
            ]);

        return $boom_config;
    }

    static function getTestBoomConfig()
    {
        $boom_config = BoomConfigs::findFirstById(1);
        return $boom_config;
    }
}