<?php

namespace admin;


class PartnerDatasController extends BaseController
{
    function indexAction()
    {
        $start_at = $this->params('start_at', date('Y-m-d'));
        $start_at_time = beginOfDay(strtotime($start_at));
        $end_at_time = endOfDay(strtotime($start_at));

        $cond = ['conditions' => 'time_type = :time_type: and stat_at >= :start_at: and stat_at <= :end_at: and product_channel_id!=:product_channel_id:',
            'bind' => ['time_type' => STAT_DAY, 'start_at' => $start_at_time, 'end_at' => $end_at_time, 'product_channel_id' => 0],
            'order' => 'rank desc,partner_id desc'
        ];

        $page = $this->params('page', 1);
        $partner_datas = \PartnerDatas::findPagination($cond, $page, 100);

        $this->view->partner_datas = $partner_datas;
        $this->view->start_at = $start_at;
        $this->view->end_at = date('Y-m-d');
        $this->view->stat_fields = \PartnerDatas::$STAT_FIELDS;
    }

    function newAction()
    {
        $partner_data = new \PartnerDatas();
        $this->view->partner_data = $partner_data;

        $all_product_channels = \ProductChannels::find(array('order' => ' id desc'));
        $this->view->product_channels = $all_product_channels;
        $all_partners = \Partners::find(array('order' => ' id desc'));
        $this->view->partners = $all_partners;
        $this->view->start_at = date('Y-m-d');
    }

    function createAction()
    {
        $opts = $this->params('partner_data');
        $partner_id = fetch($opts, 'partner_id');
        $product_channel_id = fetch($opts, 'product_channel_id');
        $stat_at = fetch($opts, 'start_at');
        if (!$stat_at) {
            return $this->renderJSON(ERROR_CODE_FAIL, '未选择时间');
        }
        $stat_at = beginOfDay(strtotime($stat_at));
        $time_type = STAT_DAY;
        $partner_data = \PartnerDatas::findFirst([
            'conditions' => 'stat_at=:stat_at: and time_type=:time_type: and partner_id = :partner_id: and product_channel_id=:product_channel_id:',
            'bind' => ['stat_at' => $stat_at, 'time_type' => $time_type, 'partner_id' => $partner_id, 'product_channel_id' => $product_channel_id]
        ]);

        if (!$partner_data) {
            $partner_data = new \PartnerDatas();
            $partner_data->partner_id = $partner_id;
            $partner_data->product_channel_id = $product_channel_id;
            $partner_data->stat_at = $stat_at;
            $partner_data->time_type = $time_type;
        }

        $data = [];
        foreach (\PartnerDatas::$STAT_FIELDS as $k => $text) {
            $data[$k] = fetch($opts, $k);
        }

        $activated_num = fetch($opts, 'activated_num');

        $partner_data->rank = $activated_num;
        $partner_data->data = json_encode($data, JSON_UNESCAPED_UNICODE);
        $partner_data->save();

        return $this->renderJSON(ERROR_CODE_SUCCESS, '', ['error_url' => '/admin/partner_datas']);
    }

    function editAction()
    {
        $all_product_channels = \ProductChannels::find(array('order' => ' id desc'));
        $this->view->product_channels = $all_product_channels;
        $all_partners = \Partners::find(array('order' => ' id desc'));
        $this->view->partners = $all_partners;

        $partner_data = \PartnerDatas::findFirstById($this->params('id'));
        $data = json_decode($partner_data->data);

        $this->view->data = $data;
        $this->view->partner_data = $partner_data;
    }

    function updateAction()
    {
        $opts = $this->params('partner_data');
        $partner_id = fetch($opts, 'partner_id');
        $product_channel_id = fetch($opts, 'product_channel_id');
        $stat_at = fetch($opts, 'start_at');
        if (!$stat_at) {
            return $this->renderJSON(ERROR_CODE_FAIL, '未选择时间');
        }

        $stat_at = beginOfDay(strtotime($stat_at));
        $time_type = STAT_DAY;
        $partner_data = \PartnerDatas::findFirstById($this->params('id'));
        $partner_data->partner_id = $partner_id;
        $partner_data->product_channel_id = $product_channel_id;
        $partner_data->stat_at = $stat_at;
        $partner_data->time_type = $time_type;
        $data = [];
        foreach (\PartnerDatas::$STAT_FIELDS as $k => $text) {
            $data[$k] = fetch($opts, $k);
        }

        $activated_num = fetch($opts, 'activated_num');

        $partner_data->rank = $activated_num;
        $partner_data->data = json_encode($data, JSON_UNESCAPED_UNICODE);
        $partner_data->save();

        return $this->renderJSON(ERROR_CODE_SUCCESS, '', ['error_url' => '/admin/partner_datas']);
    }
}
