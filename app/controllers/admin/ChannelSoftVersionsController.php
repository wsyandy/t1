<?php

namespace admin;

class ChannelSoftVersionsController extends BaseController
{
    function indexAction()
    {
        $cond = $this->getConditions('soft_version');
        if (isset($cond['conditions'])) {
            $cond['conditions'] .= ' and channel_package=:channel_package:';
            $cond['bind']['channel_package'] = 1;
        } else {
            $cond['conditions'] = 'channel_package=:channel_package:';
            $cond['bind']['channel_package'] = 1;
        }
        $cond['order'] = 'id desc';

        $page = $this->params('page');

        $soft_versions = \SoftVersions::findPagination($cond, $page);
        $this->view->soft_versions = $soft_versions;
        $this->view->product_channels = \ProductChannels::find(['order' => 'id desc']);
        $this->view->partners = \Partners::find(['order' => 'id desc']);
    }

    function newAction()
    {
        $soft_version = new \SoftVersions();
        $this->view->soft_version = $soft_version;
        $this->view->product_channels = \ProductChannels::find(['order' => 'id desc']);
    }

    function createAction()
    {
        $soft_version = new \SoftVersions();
        $this->assign($soft_version, 'soft_version');

        if (!$soft_version->built_in_fr) {
            return $this->renderJSON(ERROR_CODE_FAIL, '内置fr不能为空');
        }

        if ($soft_version->built_in_fr) {
            $partner = \Partners::findFirstByFr($soft_version->built_in_fr);
            if (!$partner) {
                return $this->renderJSON(ERROR_CODE_FAIL, 'fr非法');
            }
        }

        $soft_version->channel_package = 1;
        $soft_version->status = SOFT_VERSION_STATUS_OFF;
        $soft_version->stable = SOFT_VERSION_STABLE_ON;
        $soft_version->operator_id = $this->currentOperator()->id;
        $soft_version->save();
        \OperatingRecords::logAfterCreate($this->currentOperator(), $soft_version);
        return $this->renderJSON(ERROR_CODE_SUCCESS, '保存成功', ['soft_version' => $soft_version->toJson()]);
    }

    function editAction()
    {
        $soft_version = \SoftVersions::findFirstById($this->params('id'));
        $this->view->soft_version = $soft_version;
        $this->view->product_channels = \ProductChannels::find(['order' => 'id desc']);
    }

    function updateAction()
    {
        $soft_version = \SoftVersions::findFirstById($this->params('id'));
        $this->assign($soft_version, 'soft_version');


        if (!$soft_version->built_in_fr) {
            return $this->renderJSON(ERROR_CODE_FAIL, '内置fr不能为空');
        }

        if ($soft_version->built_in_fr) {
            $partner = \Partners::findFirstByFr($soft_version->built_in_fr);
            if (!$partner) {
                return $this->renderJSON(ERROR_CODE_FAIL, 'fr非法');
            }
        }
        $soft_version->channel_package = 1;
        $soft_version->operator_id = $this->currentOperator()->id;
        \OperatingRecords::logBeforeUpdate($this->currentOperator(), $soft_version);
        $soft_version->update();
        return $this->renderJSON(ERROR_CODE_SUCCESS, '保存成功', ['soft_version' => $soft_version->toJson()]);
    }

    function deleteAction()
    {
        $soft_version = \SoftVersions::findFirstById($this->params('id'));
        \OperatingRecords::logBeforeDelete($this->currentOperator(), $soft_version);
        $soft_version->delete();
        return $this->renderJSON(ERROR_CODE_SUCCESS, '删除成功');
    }

    function generatePartnerUrlsAction()
    {
        $soft_version = \SoftVersions::findFirstById($this->params('id'));
        $soft_version->host = "t.momoyuedu.cn";

        if ($this->request->isPost()) {
            $host = $this->params('soft_version[host]', 't.momoyuedu.cn');
            $host = trim($host, '/');
            return $this->renderJSON(ERROR_CODE_FAIL, "https://" . $host . "/soft_versions/" . $soft_version->id . ".apk");
        }

        $this->view->soft_version = $soft_version;
    }
}