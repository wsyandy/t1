<?php

namespace admin;

class DevicesController extends BaseController
{

    function indexAction()
    {
        $cond = $this->getConditions('device');

        $page = 1;
        $per_page = 30;
        $total_page = 1;
        $total_entries = $per_page * $total_page;

        $cond['order'] = 'id desc';

        $export_columns = ['imei_md5' => 'imei_md5', 'idfa_md5' => 'idfa_md5'];
        $this->view->export_columns = $export_columns;

        $devices = \Devices::findPagination($cond, $page, $per_page, $total_entries);
        $this->view->devices = $devices;
        $this->view->product_channels = \ProductChannels::find(['order' => 'id desc']);
    }

    function editAction()
    {
        $device = \Devices::findFirstById($this->params('id'));
        $this->view->device = $device;
    }

    function updateAction()
    {
        $device = \Devices::findFirstById($this->params('id'));
        $this->assign($device, 'device');
        \OperatingRecords::logBeforeUpdate($this->currentOperator(), $device);
        $device->update();
        $this->renderJSON(ERROR_CODE_SUCCESS, '操作成功', array('device' => $device->toJson()));
    }

    function exportAction()
    {
        $export_column = $this->params('export_column');
        if (!$export_column) {
            $this->renderJSON(ERROR_CODE_FAIL, '选择导出标识');
            return;
        }

        $start_at = $this->params('start_at', date('Y-m-d'));
        $start_at = beginOfDay(strtotime($start_at));

        $end_at = $this->params('end_at', date('Y-m-d'));
        $end_at = beginOfDay(strtotime($end_at));
        if ($end_at - $start_at < 0 || $end_at - $start_at > 3 * 60 * 60 * 24) {
            $this->renderJSON(ERROR_CODE_FAIL, '一次最多导出3天数据');
            return;
        }

        $temp_file = APP_ROOT . 'temp/export_' . $export_column . '_' . date('Ymd', $start_at) . '_' . date('Ymd', $end_at) . '.txt';
        file_put_contents($temp_file, '');

        $data_file = \Devices::exportColumn($export_column, beginOfDay($start_at), endOfDay($end_at), $temp_file);
        if (!file_exists($data_file)) {
            $this->renderJSON(ERROR_CODE_FAIL, '没有数据');
            return;
        }

        $export_history = new \ExportHistories();
        $export_history->operator_id = $this->currentOperator()->id;
        $export_history->name = '设备标识';
        $export_history->table_name = 'Devices';
        $export_history->conditions = json_encode([$export_column, date('Ymd', $start_at), date('Ymd', $end_at)], JSON_UNESCAPED_UNICODE);
        $export_history->download_num = 1;
        $export_history->save();

        header("Content-type: application/octet-stream;charset=utf-8");
        header("Accept-Ranges: bytes");
        header("Accept-Length: " . filesize($data_file));
        header("Content-Disposition: attachment; filename=" . basename($data_file));
        echo file_get_contents($data_file);
    }

    function whiteListAction()
    {
        $hot_cache = \Devices::getHotWriteCache();
        $key = "white_device_no__list";
        $dno_list = $hot_cache->zrange($key, 0, -1);
        $this->view->dno_list = $dno_list;
    }

    function addWhiteListAction()
    {
        debug("111");
        if ($this->request->isPost()) {
            debug("2222");
            $dno = $this->params('dno');
            if (!$dno) {
                return $this->renderJSON(ERROR_CODE_FAIL, '参数错误');
            }
            $hot_cache = \Devices::getHotWriteCache();
            $key = "white_device_no__list";
            $hot_cache->zadd($key, time(), $dno);
            $this->response->redirect('/admin/devices/white_list');
            return;
        }
    }

    function deleteWhiteListAction()
    {
        $dno = $this->params('dno');
        $hot_cache = \Devices::getHotWriteCache();
        $key = "white_device_no__list";
        $hot_cache->zrem($key, $dno);
        $this->renderJSON(ERROR_CODE_SUCCESS, '', ['error_url' => '/admin/devices/white_list']);
    }
}