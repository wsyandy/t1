<?php

namespace pt;

class PartnerDatasController extends BaseController
{
    function indexAction()
    {

        $start_at = $this->params('start_at', date('Y-m-d'));
        $start_at_time = beginOfDay(strtotime($start_at));
        $end_at_time = endOfDay(strtotime($start_at));

        $partner_account_product_channels = \PartnerAccountProductChannels::findByPartnerAccountId($this->currentPartnerAccount()->id);
        if (count($partner_account_product_channels) < 1) {
            echo '账户异常';
            return false;
        }

        $partner_datas = [];
        foreach ($partner_account_product_channels as $partner_account_product_channel) {
            $product_channel_id = $partner_account_product_channel->product_channel_id;
            $partner_id = $partner_account_product_channel->partner_id;
            $cond = ['conditions' => 'time_type = :time_type: and stat_at >= :start_at: and stat_at <= :end_at: and partner_id = :partner_id: and product_channel_id = :product_channel_id:',
                'bind' => ['time_type' => STAT_DAY, 'start_at' => $start_at_time, 'end_at' => $end_at_time, 'partner_id' => $partner_id,
                    'product_channel_id' => $product_channel_id],
                'order' => 'rank desc'
            ];
            $partner_data = \PartnerDatas::findFirst($cond);
            if($partner_data){
                $partner_datas[] = $partner_data;
            }
        }

        $this->view->partner_datas = $partner_datas;
        $this->view->start_at = $start_at;
        $this->view->stat_fields = \PartnerDatas::$STAT_FIELDS;
    }
}