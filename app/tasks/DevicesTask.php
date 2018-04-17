<?php
/**
 * Created by PhpStorm.
 * User: apple
 * Date: 2018/4/12
 * Time: 下午4:04
 */

class DevicesTask extends \Phaclcon\Cli\Task
{
    function exportDataAction()
    {
        $export_columns = ['imei_md5', 'idfa_md5'];

        foreach ($export_columns as $export_column) {

            $temp_file = APP_ROOT . 'temp/export_' . $export_column . '.txt';

            file_put_contents($temp_file, '');

            $is_export_md5 = false;

            if (preg_match('/_md5/', $export_column)) {
                $column = preg_replace('/_md5/', '', $export_column);
                $is_export_md5 = true;
            }

            $devices = Devices::findForeach([
                'columns' => 'distinct ' . $column
            ]);

            foreach ($devices as $device) {

                $val = $device->$column;

                if ($val) {
                    if ($column == 'imei' && (strlen($val) >= 20 && strlen($val) <= 22) && strlen(base64_decode($val)) == 15) {
                        $val = base64_decode($val);
                    }

                    if ($is_export_md5) {
                        $val = Partners::generateMuid([$column => $val]);
                    }

                    echoLine($val);
                    file_put_contents($temp_file, $val . PHP_EOL, FILE_APPEND);
                }
            }

            //self::delay(120)->deleteExportFile($temp_file);

            echoLine($temp_file);
        }
    }

    function mobileTypeActiveAction()
    {
        $date = ['2018-04-17'];

        foreach ($date as $stat_at) {

            $devices = Devices::find(
                [
                    'conditions' => 'partner_id = 85 and created_at >= :start: and created_at <= :end:',
                    'bind' => ['start' => beginOfDay(strtotime($stat_at)), 'end' => endOfDay(strtotime($stat_at))]
                ]);

            $total_num = count($devices);

            $res = [];

            foreach ($devices as $device) {

                $model = $device->model;

                if (isset($res[$model])) {
                    $res[$model] += 1;
                } else {
                    $res[$model] = 1;
                }
            }


            arsort($res);

            $file = APP_ROOT . "public/" . $stat_at . "mobile_type_device_active.txt";
            $f = fopen($file, 'w');

            fwrite($f, $stat_at . '激活总数量: ' . $total_num . "\r\n");
            echoLine($total_num);
            foreach ($res as $type => $num) {
                $text = "手机型号:" . $type . "激活数量:" . $num;
                echoLine($text);
                fwrite($f, $text . "\r\n");
            }

            fclose($f);

            $res = StoreFile::upload($file, APP_NAME . "/devices/stat/" . uniqid() . ".txt");
            echoLine(StoreFile::getUrl($res));

            unlink($file);
        }
    }

    function mobileTypeUserRegisterAction()
    {
        $date = ['2018-04-17'];

        foreach ($date as $stat_at) {

            $users = Users::find(
                [
                    'conditions' => 'partner_id = :partner_id: and register_at >= :start: and register_at <= :end:',
                    'bind' => ['partner_id' => 85, 'start' => beginOfDay(strtotime($stat_at)), 'end' => endOfDay(strtotime($stat_at))]
                ]);

            $total_num = count($users);

            $res = [];

            foreach ($users as $user) {

                $device = $user->device;
                $model = $device->model;

                if (isset($res[$model])) {
                    $res[$model] += 1;
                } else {
                    $res[$model] = 1;
                }
            }


            arsort($res);

            $file = APP_ROOT . "public/" . $stat_at . "mobile_type_vivo_user_register.txt";

            $f = fopen($file, 'w');

            fwrite($f, $stat_at . '注册总数量: ' . $total_num . "\r\n");
            echoLine($total_num);

            foreach ($res as $type => $num) {
                $text = "手机型号:" . $type . "注册数量:" . $num;
                //echoLine($text);
                fwrite($f, $text . "\r\n");
            }

            fclose($f);

            $res = StoreFile::upload($file, APP_NAME . "/devices/stat/" . $stat_at . "注册.txt");
            echoLine(StoreFile::getUrl($res));

            unlink($file);
        }
    }

    function totalMobileTypeActiveAction()
    {
        $partner = Partners::findFirstById(85);

        $devices = Devices::find(
            [
                'conditions' => 'partner_id = 85',
            ]);

        $total_num = count($devices);

        echoLine($total_num);
        $res = [];

        foreach ($devices as $device) {

            $model = $device->model;

            if (isset($res[$model])) {
                $res[$model] += 1;
            } else {
                $res[$model] = 1;
            }
        }


        arsort($res);

        $file = APP_ROOT . "public/" . $partner->username . "_mobile_type_device_active.txt";

        $f = fopen($file, 'w');

        fwrite($f, '激活总数量: ' . $total_num . "\r\n");

        foreach ($res as $type => $num) {
            $text = "手机型号:" . $type . "激活数量:" . $num;
            echoLine($text);
            fwrite($f, $text . "\r\n");
        }

        fclose($f);

        $res = StoreFile::upload($file, APP_NAME . "/devices/" . $partner->username . "_total_mobile_type_device_active.txt");
        echoLine(StoreFile::getUrl($res));
    }

    function totalMobileTypeUserRegisterAction()
    {
        $partner = Partners::findFirstById(85);

        $users = Users::find(
            [
                'conditions' => 'partner_id = :partner_id: and register_at > 0',
                'bind' => ['partner_id' => $partner->id]
            ]);

        $total_num = count($users);
        echoLine($total_num);

        $res = [];

        foreach ($users as $user) {

            $device = $user->device;
            $model = $device->model;

            if (isset($res[$model])) {
                $res[$model] += 1;
            } else {
                $res[$model] = 1;
            }
        }


        arsort($res);

        $file = APP_ROOT . "public/" . $partner->username . "_mobile_type_total_user_register.txt";

        $f = fopen($file, 'w');

        fwrite($f, '注册总数量: ' . $total_num . "\r\n");

        foreach ($res as $type => $num) {
            $text = "手机型号:" . $type . "注册数量:" . $num;
            echoLine($text);
            fwrite($f, $text . "\r\n");
        }

        fclose($f);

        $res = StoreFile::upload($file, APP_NAME . "/devices/" . $partner->username . "_mobile_type_total_user_register.txt");
        echoLine(StoreFile::getUrl($res));
    }
}