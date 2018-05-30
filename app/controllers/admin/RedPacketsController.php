<?php

namespace admin;

class RedPacketsController extends BaseController
{
    function indexAction()
    {
        $conds = $this->getConditions('red_packet');
        $conds['order'] = 'id desc';
        $page = $this->params('page');
        $per_page = $this->params('per_page');
        $red_packet_histories = \RedPackets::findPagination($conds, $page, $per_page);
        $this->view->red_packet_histories = $red_packet_histories;
    }

    function userDiamondAction()
    {
        $red_packet = \RedPackets::findFirstById($this->params('id'));
        $key = $red_packet->generateRedPacketUserDiamondKey();

        $user_db = \Users::getUserDb();
        $data = $user_db->zrevrange($key, 0, -1, true);

        $this->view->result_data = $data;
    }

}