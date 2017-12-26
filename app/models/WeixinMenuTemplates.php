<?php

class WeixinMenuTemplates extends BaseModel
{

    public function addProductChannelId($product_channel_id)
    {

        if ($this->product_channel_ids) {
            $product_channel_ids = $this->product_channel_ids;
            $product_channel_ids = explode(',', $product_channel_ids);
            if (!in_array($product_channel_id, $product_channel_ids)) {
                $product_channel_ids[] = $product_channel_id;
                $this->product_channel_ids = implode(',', $product_channel_ids);
            }
        } else {
            $this->product_channel_ids = $product_channel_id;
        }

        $this->save();
    }

    public function removeProductChannelId($product_channel_id)
    {

        if (!$this->product_channel_ids) {
            return;
        }

        $product_channel_ids = $this->product_channel_ids;
        $product_channel_ids = explode(',', $product_channel_ids);
        if (in_array($product_channel_id, $product_channel_ids)) {
            $new_product_channel_ids = [];
            foreach ($product_channel_ids as $id) {
                if ($id && $product_channel_id != $id) {
                    $new_product_channel_ids[] = $id;
                }
            }

            if ($new_product_channel_ids) {
                $this->product_channel_ids = implode(',', $new_product_channel_ids);
            } else {
                $this->product_channel_ids = null;
            }

            $this->save();
        }
    }

}