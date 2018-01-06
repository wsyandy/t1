<?php

class Products extends BaseModel
{
    static $STATUS = [STATUS_ON => '上架', STATUS_OFF => '下架', STATUS_FORBIDDEN => '禁用'];

    static $files = ['icon' => APP_NAME . '/products/icon/%s'];

    static $PLATFORMS = ['client_ios' => '客户端ios', 'client_android' => '客户端安卓', 'weixin_ios' => '微信ios',
        'weixin_android' => '微信安卓', 'touch_ios' => 'H5ios', 'touch_android' => 'H5安卓'];

    /**
     * @type ProductGroups
     */
    private $_product_group;

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

    static function findByProductGroupId($product_group_id)
    {
        return \Products::find(
            array(
                'conditions' => 'product_group_id = :product_group_id:',
                'bind' => array('product_group_id' => $product_group_id),
                'order' => 'rank desc'
            )
        );
    }

    function toJson()
    {
        return array(
            'id' => $this->id,
            'name' => $this->name,
            'icon_url' => $this->icon_url,
            'rank' => $this->rank,
            'status_text' => $this->status_text,
            'amount' => $this->amount,
            'diamond' => $this->diamond,
            'product_group_name' => $this->product_group_name
        );
    }

    function getProductGroupName()
    {
        return $this->product_group->name;
    }
}