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

    function getSvgaImageSmallUrl()
    {
        if (isBlank($this->svga_image)) {
            return '';
        }

        return StoreFile::getUrl($this->svga_image) . '@!small';
    }

    static function getBoomConfigByCache($boom_config_id)
    {
        if (!$boom_config_id) {
            return [];
        }

        $db = \Rooms::getRoomDb();

        $data = $db->hget('boom_config_cache_id' . $boom_config_id);

        return $data;
    }
}