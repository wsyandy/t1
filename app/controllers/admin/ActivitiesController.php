<?php
/**
 * Created by PhpStorm.
 * User: apple
 * Date: 2018/4/3
 * Time: 下午8:20
 */
namespace admin;
class ActivitiesController extends BaseController
{
    function indexAction()
    {
        $cond = $this->getConditions('activity');
        $cond['order'] = 'rank desc, id desc';
        $page = $this->params('page');
        $activities = \Activities::findPagination($cond, $page);
        $this->view->activities = $activities;
    }

    function newAction()
    {
        $activity = new \Activities();
        $activity->status = STATUS_ON;
        $this->view->activity = $activity;
    }

    function createAction()
    {
        $activity = new \Activities();
        $this->assign($activity, 'activity');
        $activity->operator_id = $this->currentOperator()->id;
        if ($activity->save()) {
            \OperatingRecords::logAfterCreate($this->currentOperator(), $activity);
            return $this->renderJSON(ERROR_CODE_SUCCESS, '创建成功', ['activity' => $activity->toJson()]);
        } else {
            return $this->renderJSON(ERROR_CODE_FAIL, '创建失败');
        }
    }

    function editAction()
    {
        $activity = \Activities::findFirstById($this->params('id'));
        $this->view->activity = $activity;
    }

    function updateAction()
    {
        $activity = \Activities::findFirstById($this->params('id'));
        $this->assign($activity, 'activity');
        $activity->operator_id = $this->currentOperator()->id;
        \OperatingRecords::logBeforeUpdate($this->currentOperator(), $activity);
        if ($activity->update()) {
            return $this->renderJSON(ERROR_CODE_SUCCESS, '修改成功', ['activity' => $activity->toJson()]);
        } else {
            return $this->renderJSON(ERROR_CODE_FAIL, '创建失败');
        }
    }

    function productChannelIdsAction()
    {
        $id = $this->params('id');
        $activity = \Activities::findFirstById($id);

        $product_channels = \ProductChannels::find(['id' => 'desc']);

        $select_product_channel_ids = [];
        $product_channel_ids = $activity->product_channel_ids;
        if (isPresent($product_channel_ids)) {
            $select_product_channel_ids = explode(',', $product_channel_ids);
            $select_product_channel_ids = array_filter($select_product_channel_ids);
        }

        $this->view->select_product_channel_ids = $select_product_channel_ids;

        $this->view->product_channels = $product_channels;
        $this->view->id = $id;
    }

    function updateProductChannelIdsAction()
    {
        $id = $this->params('id');
        $activity = \Activities::findFirstById($id);
        if (isBlank($activity)) {
            return $this->renderJSON(ERROR_CODE_FAIL, '礼物不存在');
        }

        $product_channel_ids = $this->params('product_channel_ids');
        if ($product_channel_ids) {
            $product_channel_ids = implode(',', $product_channel_ids);
            $activity->product_channel_ids = ',' . $product_channel_ids . ',';
        } else {
            $activity->product_channel_ids = '';
        }

        \OperatingRecords::logBeforeUpdate($this->currentOperator(), $activity);
        if ($activity->update()) {
            return $this->renderJSON(ERROR_CODE_SUCCESS, '', ['activity' => $activity->toJson]);
        } else {
            return $this->renderJSON(ERROR_CODE_FAIL, '配置失败');
        }
    }


    function platformsAction()
    {
        $activity = \Activities::findFirstById($this->params('id'));
        $platforms = \Products::$PLATFORMS;
        $all_select_platforms = explode(',', $activity->platforms);
        $this->view->activity = $activity;
        $this->view->platforms = $platforms;
        $this->view->all_select_platforms = $all_select_platforms;
    }

    function updatePlatformsAction()
    {
        $activity = \Activities::findFirstById($this->params('id'));
        $platforms = $this->params('platforms', ['*']);
        if (in_array('*', $platforms)) {
            $platforms = ['*'];
        }

        $activity->operator_id = $this->currentOperator()->id;
        $activity->platforms = implode(',', $platforms);
        \OperatingRecords::logBeforeUpdate($this->currentOperator(), $activity);
        $activity->update();
        return $this->renderJSON(ERROR_CODE_SUCCESS, '', ['error_url' => '/admin/activities']);
    }
}