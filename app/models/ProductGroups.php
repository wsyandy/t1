<?php
/**
 * Created by PhpStorm.
 * User: maoluanjuan
 * Date: 05/01/2018
 * Time: 21:27
 */

class ProductGroups extends BaseModel
{
    /**
     * @type ProductChannels
     */
    private $_product_channel;

    static $FEE_TYPE = [PRODUCT_GROUP_FEE_TYPE_DIAMOND => '钻石'];

    static $STATUS = [STATUS_ON => '有效', STATUS_OFF => '无效'];

    static $files = ['icon' => 'product_groups/icon/%s'];

    static function getCacheEndPoint()
    {
        $config = self::di('config');
        $endpoints = explode(',', $config->cache_endpoint);
        return $endpoints[0];
    }

    static function findByProductChannelId($product_channel_id)
    {
        debug("product_channel_id: " . $product_channel_id);
        return \ProductGroups::find(
            array(
                'conditions' => 'product_channel_id = :product_channel_id:',
                'bind' => array('product_channel_id' => $product_channel_id)
            )

        );
    }

    function toJson()
    {
        return array(
            'id' => $this->id,
            'name' => $this->name,
            'icon_url' => $this->icon_url,
            'remark' => $this->remark,
            'status_text' => $this->status_text,
            'fee_type_text' => $this->fee_type_text,
            'product_channel_name' => $this->product_channel->name
        );
    }

    function getIconUrl()
    {
        if (isBlank($this->icon)) {
            return '';
        }
        return \StoreFile::getUrl($this->icon);
    }
}