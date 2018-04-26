<?php

class PartnerDatas extends BaseModel
{
    /**
     * @type Partners
     */
    private $_partner;

    /**
     * @type ProductChannels
     */
    private $_product_channel;

    static $STAT_FIELDS = ['activated_num' => '激活人数', 'settlement_num' => '注册人数','register_ratio'=>'注册率%'];

    function mergeJson()
    {
        $opts = json_decode($this->data,true);
        $settlement_num = fetch($opts,'settlement_num');
        $activated_num = fetch($opts,'activated_num');
        $register_ratio = sprintf('%0.2f', ($settlement_num / $activated_num) * 100);
        return ['register_ratio' => $register_ratio];
    }

}
