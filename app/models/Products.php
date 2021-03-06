<?php

class Products extends BaseModel
{
    /**
     * @type ProductGroups
     */
    private $_product_group;

    static $STATUS = [STATUS_ON => '上架', STATUS_OFF => '下架', STATUS_FORBIDDEN => '禁用'];

    static $files = ['icon' => APP_NAME . '/products/icon/%s'];

    static $PLATFORMS = ['client_ios' => '客户端ios', 'client_android' => '客户端安卓', 'weixin_ios' => '微信ios',
        'weixin_android' => '微信安卓', 'touch_ios' => 'H5ios', 'touch_android' => 'H5安卓'];


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
                'conditions' => 'product_group_id=:product_group_id:',
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
            'gold' => $this->gold,
            'product_group_name' => $this->product_group_name,
            'apple_product_no' => $this->apple_product_no,
            'draw_num' => $this->draw_num
        ];
    }

    function toApiJson()
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'amount' => $this->amount,
            'diamond' => $this->diamond,
            'gold' => $this->gold,
            'apple_product_no' => $this->apple_product_no
        ];
    }

    function getProductGroupName()
    {
        return $this->product_group->name;
    }

    static function findDiamondListByUser($user, $format = null)
    {

        $product_group = \ProductGroups::findFirst(
            [
                'conditions' => 'product_channel_id=:product_channel_id: and pay_type=:pay_type: and status=:status:',
                'bind' => ['product_channel_id' => $user->product_channel_id,
                    'pay_type' => PRODUCT_GROUP_PAY_TYPE_CASH, 'status' => STATUS_ON]
            ]
        );

        if (isBlank($product_group)) {
            return false;
        }

        debug("product_group: " . strval($product_group->id));

        $products = \Products::find([
            'conditions' => 'product_group_id = :product_group_id: and status = :status: and amount < 3000',
            'bind' => ['product_group_id' => $product_group->id, 'status' => STATUS_ON],
            'order' => 'amount asc'
        ]);

        if (isDevelopmentEnv()) {
            $products = \Products::find([
                'conditions' => 'product_group_id = :product_group_id: and status = :status:',
                'bind' => ['product_group_id' => $product_group->id, 'status' => STATUS_ON],
                'order' => 'amount asc'
            ]);
        }

        $selected_products = [];

        foreach ($products as $product) {

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

    //查询hi币兑换套餐
    static function findHiCoinDiamondListByUser($user)
    {
        $product_group = \ProductGroups::findFirst(
            [
                'conditions' => 'product_channel_id=:product_channel_id: and pay_type=:pay_type: and status=:status:',
                'bind' => ['product_channel_id' => $user->product_channel_id,
                    'pay_type' => PRODUCT_GROUP_PAY_TYPE_HI_COIN, 'status' => STATUS_ON]
            ]
        );

        if (isBlank($product_group)) {
            return false;
        }

        debug("product_group: " . strval($product_group->id));

        $products = \Products::find([
            'conditions' => 'product_group_id = :product_group_id: and status = :status:',
            'bind' => ['product_group_id' => $product_group->id, 'status' => STATUS_ON],
            'order' => 'amount asc'
        ]);

        return $products;
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
        if (isPresent($this->apple_product_no)) {
            debug($user->id, $this->id, "apple_product_no: ", $this->apple_product_no);
            return $user->isIos();
        }

        debug($user->id, $this->id, "not_apple_product_no");
        return !$user->isIos();
    }

    function getShowDiamond($user)
    {
        if (isPresent($this->full_name)) {
            return $this->full_name;
        }

        return $this->diamond;
    }


    function getShowSendDiamond($full_name)
    {
        if (strpos($full_name, '+') !== false) {
            $full_name = explode('+', $full_name);
            return $full_name[count($full_name) - 1];
        }
        return '';
    }


    function getParseFieldData($data, $value)
    {
        $data = json_decode($data, true);
        if (empty($data[$value])) return '';
        return $data[$value];
    }

    function getDrawNum()
    {
        $data = json_decode($this->data, true);
        $draw_num = fetch($data, 'draw_num', 0);
        return $draw_num;
    }

}