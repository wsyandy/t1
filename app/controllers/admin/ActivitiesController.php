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

        list($error_code, $error_reason) = $activity->checkFields();
        if ($error_code == ERROR_CODE_FAIL) {
            return $this->renderJSON($error_code, $error_reason);
        }

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

        list($error_code, $error_reason) = $activity->checkFields();
        if ($error_code == ERROR_CODE_FAIL) {
            return $this->renderJSON($error_code, $error_reason);
        }

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
            return $this->renderJSON(ERROR_CODE_FAIL, '');
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
        $platforms = \Activities::$PLATFORMS;
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

    function statAction()
    {
        $cond = $this->getConditions('activity');
        $cond['order'] = 'rank desc, id desc';
        $page = $this->params('page');
        $activities = \Activities::findPagination($cond, $page);
        $this->view->activities = $activities;
    }

    function luckyDrawActivityStatAction()
    {
        $activity_id = $this->params('id');
        $activity = \Activities::findFirstById($activity_id);

        //每月天数数组array('d'=>'Y-m-d')
        $year = $this->params('year', date('Y'));
        $month = $this->params('month', date('m'));
        $stat_date = strtotime($year . "-" . $month . "-01");
        $end_at = endOfMonth($stat_date);
        $month_max_day = date('d', $end_at);//获取当前月份最大的天数

        $month = intval($month);

        if ($month < 10) {
            $month = "0" . $month;
        }

        $year_array = [];

        for ($i = date('Y'); $i >= 2018; $i--) {
            $year_array[$i] = $i;
        }

        for ($i = 1; $i <= $month_max_day; $i++) {

            if ($i < 10) {
                $day = "0" . $i;
            } else {
                $day = $i;
            }

            $day = $year . "-" . $month . "-" . $day;

            $results[$day]['obtain_day_user'] = $activity->getObtainLuckyDrawActivityUser($day);
            $results[$day]['obtain_day_num'] = $activity->getObtainLuckyDrawActivityNum($day);
            $results[$day]['day_user'] = $activity->getLuckyDrawActivityUser($day);
            $results[$day]['day_num'] = $activity->getLuckyDrawActivityNum($day);
        }


        $this->view->activity_id = $activity_id;
        $this->view->results = $results;
        $this->view->year_array = $year_array;
        $this->view->month = intval($month);
        $this->view->year = intval($year);

        $cache = \Users::getHotReadCache();
        $this->view->lucky_draw_prize_2_num = intval($cache->get('lucky_draw_prize_2'));
        $this->view->lucky_draw_prize_4_num = intval($cache->get('lucky_draw_prize_4'));
        $this->view->lucky_draw_prize_6_num = intval($cache->get('lucky_draw_prize_6'));
        $this->view->lucky_draw_prize_7_num = intval($cache->get('lucky_draw_prize_7'));
        $this->view->lucky_draw_prize_8_num = intval($cache->get('lucky_draw_prize_8'));
    }

    function luckyDrawActivityRecordsAction()
    {
        $stat_at = $this->params('stat_at', date('Y-m-d'));
        $activity_id = $this->params('id');
        $activity = \Activities::findFirstById($activity_id);
        $ond = [];
        $activity_histories = \ActivityHistories::findPagination();
    }
}