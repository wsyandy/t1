<?php

class PartnerDatasTask extends \Phalcon\Cli\Task
{
    function dayAction()
    {
        $partner_accounts = \PartnerAccounts::find(['conditions' => 'status=' . STATUS_ON]);
        foreach ($partner_accounts as $partner_account) {
            $partner_account_product_channels = \PartnerAccountProductChannels::findByPartnerAccountId($partner_account->id);
            foreach ($partner_account_product_channels as $partner_account_product_channel) {
                $stat_at = beginOfDay();
                $time_type = STAT_DAY;
                $partner_data = \PartnerDatas::findFirst([
                    'conditions' => 'stat_at=:stat_at: and time_type=:time_type: and partner_id = :partner_id: and product_channel_id=:product_channel_id:',
                    'bind' => ['stat_at' => $stat_at, 'time_type' => $time_type, 'partner_id' => $partner_account_product_channel->partner_id,
                        'product_channel_id' => $partner_account_product_channel->product_channel_id]
                ]);
                if(!$partner_data){
                    $partner_data = new PartnerDatas();
                    $partner_data->partner_id = $partner_account_product_channel->partner_id;
                    $partner_data->product_channel_id = $partner_account_product_channel->product_channel_id;
                    $partner_data->time_type = $time_type;
                    $partner_data->stat_at = beginOfDay();
                    $partner_data->save();
                }
            }
        }
    }
}