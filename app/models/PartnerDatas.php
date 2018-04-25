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

    static $STAT_FIELDS = ['activated_num' => '激活人数', 'settlement_num' => '注册人数'];

}
