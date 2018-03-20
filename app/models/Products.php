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
            [
                'conditions' => 'product_group_id = :product_group_id:',
                'bind' => ['product_group_id' => $product_group_id],
                'order' => 'rank desc'
            ]
        );
    }

    function toJson()
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'icon_url' => $this->icon_url,
            'rank' => $this->rank,
            'status_text' => $this->status_text,
            'amount' => $this->amount,
            'diamond' => $this->diamond,
            'product_group_name' => $this->product_group_name,
            'apple_product_no' => $this->apple_product_no
        ];
    }

    function toApiJson()
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'amount' => $this->amount,
            'diamond' => $this->diamond,
            'apple_product_no' => $this->apple_product_no
        ];
    }

    function getProductGroupName()
    {
        return $this->product_group->name;
    }

    static function findDiamondListByUser($user, $format = null)
    {
        $fee_type = 'diamond';
        $product_groups = \ProductGroups::findByConditions(
            [
                'product_channel_id' => $user->product_channel_id,
                'fee_type' => $fee_type,
                'status' => STATUS_ON
            ]
        );
        if (isBlank($product_groups)) {
            return false;
        }
        $product_group = $product_groups[0];
        debug("product_group: " . strval($product_group->id));

        $products = \Products::find(array(
            'conditions' => 'product_group_id = :product_group_id: and status = :status:',
            'bind' => array(
                'product_group_id' => $product_group->id,
                'status' => STATUS_ON
            ),
            'order' => 'amount asc'
        ));

        $selected_products = [];

        foreach ($products as $product) {
            debug("product: " . strval($product->id));
            if ($product->match($user)) {
                debug("match_product: " . strval($product->id));
                if (isPresent($format) && $product->isResponseTo($format)) {
                    $selected_products[] = $product->$format();
                } else {
                    $selected_products[] = $product;
                }
            }
        }
        return $selected_products;
    }

    function supportApplePay()
    {
        if (isPresent($this->apple_product_no)) {
            return true;
        }

        return false;
    }

    function match($user)
    {
        debug("apple_product_no: " . $this->apple_product_no);
        if (isPresent($this->apple_product_no)) {
            return $user->isIos();
        }
        return !$user->isIos();
    }

    function getShowDiamond($user)
    {
        //&& $user->canShowProductFullName()
        if (isPresent($this->full_name)) {
            return $this->full_name;
        }
        return $this->diamond;
    }

    static function search($user, $format = null)
    {
        $fee_type = 'diamond';
        $product_groups = \ProductGroups::findByConditions(
            [
                'product_channel_id' => $user->product_channel_id,
                'fee_type' => $fee_type,
                'status' => STATUS_ON
            ]
        );

        if (isBlank($product_groups)) {
            return false;
        }

        $product_group = $product_groups[0];
        debug("product_group: " . strval($product_group->id));

        $apple_product = \Products::findFirst([
            'conditions' => 'product_group_id = :product_group_id: and status = :status: and apple_product_no != ""',
            'bind' => [
                'product_group_id' => $product_group->id,
                'status' => STATUS_ON
            ],
            'order' => 'amount asc'
        ]);

        $selected_products[] = $apple_product;

        $products = \Products::find([
            'conditions' => 'product_group_id = :product_group_id: and status = :status: and apple_product_no = ""',
            'bind' => [
                'product_group_id' => $product_group->id,
                'status' => STATUS_ON
            ],
            'order' => 'amount asc'
        ]);

        foreach ($products as $product) {

            if ($product->amount == $apple_product->amount) {
                continue;
            }

            debug("product: " . strval($product->id));
            $selected_products[] = $product;
        }
        return $selected_products;
    }

}