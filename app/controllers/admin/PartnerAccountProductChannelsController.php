<?php
namespace admin;

class PartnerAccountProductChannelsController extends BaseController
{
    function deleteAction()
    {
        $partner_account_product_channel = \PartnerAccountProductChannels::findFirstById($this->params('id'));
        if ($partner_account_product_channel) {
            $partner_account_product_channel->delete();
            return $this->renderJSON(ERROR_CODE_SUCCESS, '删除成功');

        }

        return $this->renderJSON(ERROR_CODE_FAIL, '删除失败');
    }
}