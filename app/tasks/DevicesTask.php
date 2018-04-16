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

        $date = ['2018-04-09', '2018-04-10', '2018-04-11', '2018-04-12', '2018-04-13', '2018-04-14', '2018-04-15'];

        foreach ($date as $stat_at) {

            $devices = Devices::find(
                [
                    'conditions' => 'partner_id = 27 and created_at >= :start: and created_at <= :end:',
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

            $f = fopen(APP_ROOT . "public/" . $stat_at . "mobile_type_device_active.txt", 'w');
            fwrite($f, $stat_at . '激活总数量: ' . $total_num . "\r\n");
            echoLine($total_num);
            foreach ($res as $type => $num) {
                $text = "手机型号:" . $type . "激活数量:" . $num;
                echoLine($text);
                fwrite($f, $text . "\r\n");
            }

            fclose($f);
        }
    }

    function mobileTypeUserRegisterAction()
    {
        $date = ['2018-04-09', '2018-04-10', '2018-04-11', '2018-04-12', '2018-04-13', '2018-04-14', '2018-04-15'];

        foreach ($date as $stat_at) {

            $users = Users::find(
                [
                    'conditions' => 'partner_id = :partner_id: and register_at >= :start: and register_at <= :end:',
                    'bind' => ['partner_id' => 27, 'start' => beginOfDay(strtotime($stat_at)), 'end' => endOfDay(strtotime($stat_at))]
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

            $f = fopen(APP_ROOT . "public/" . $stat_at . "mobile_type_vivo_user_register.txt", 'w');

            fwrite($f, $stat_at . '注册总数量: ' . $total_num . "\r\n");
            echoLine($total_num);

            foreach ($res as $type => $num) {
                $text = "手机型号:" . $type . "注册数量:" . $num;
                echoLine($text);
                fwrite($f, $text . "\r\n");
            }

            fclose($f);
        }

        $stat_at = '2018-04-09';

        $users = Users::find(
            [
                'conditions' => 'partner_id = :partner_id: and register_at >= :start: and register_at <= :end:',
                'bind' => ['partner_id' => 27, 'start' => beginOfDay(strtotime($stat_at)), 'end' => endOfDay(strtotime($stat_at))]
            ]);

        $qq_num = 0;
        $auth_num = 0;
        $num = 0;


        foreach ($users as $user) {

            if ($user->created_at == $user->register_at) {
                echoLine($user->id);
                $num++;
            }

            if (USER_LOGIN_TYPE_QQ == $user->login_type) {
                $qq_num++;
            }

            if ($user->isIdCardAuth()) {
                $auth_num++;
            }
        }

        echoLine($qq_num, $auth_num, $num);
    }

    function registerActiveAction($params)
    {
        $partner_id = $params[0];
        $stat_at = strtotime($params[1]);
        $stat_at = beginOfDay($stat_at);

        echoLine($partner_id, date('Y-m-d', $stat_at));
        //'columns' => 'id,device_id,created_at,register_at,login_type'


        $partner_id = 27;
        for($i = 1; $i < 30; $i++){
            $stat_at = time() - $i * 24 *3600;
            $a_num = Devices::count(['conditions' => 'partner_id=:partner_id: and created_at>=:sc_at: and created_at<=:ec_at:',
                'bind' => ['partner_id' => $partner_id, 'sc_at' => beginOfDay($stat_at), 'ec_at' => endOfDay($stat_at)]
            ]);

            $r_num = Users::count(['conditions' => 'partner_id=:partner_id: and (login_type!="" or login_type is not null) and register_at>=:s_at: and register_at<=:e_at: and created_at>=:sc_at: and created_at<=:ec_at:',
                'bind' => ['partner_id' => $partner_id, 's_at' => beginOfDay($stat_at), 'e_at' => endOfDay($stat_at), 'sc_at' => beginOfDay($stat_at), 'ec_at' => endOfDay($stat_at)],
            ]);

            echoLine(date('Y-m-d', $stat_at), '激活', $a_num, '注册', $r_num, '注册率', sprintf("%0.2f", $r_num/$a_num));
        }


        $partner_id = 27;
        for($i = 1; $i < 30; $i++){
            $stat_at = time() - $i * 24 *3600;
            $a_num = Devices::count(['conditions' => 'partner_id=:partner_id: and created_at>=:sc_at: and created_at<=:ec_at:',
                'bind' => ['partner_id' => $partner_id, 'sc_at' => beginOfDay($stat_at), 'ec_at' => endOfDay($stat_at)]
            ]);

            $users = Users::find(['conditions' => 'partner_id=:partner_id: and register_at>=:s_at: and register_at<=:e_at: and created_at>=:sc_at: and created_at<=:ec_at:',
                'bind' => ['partner_id' => $partner_id, 's_at' => beginOfDay($stat_at), 'e_at' => endOfDay($stat_at),
                    'sc_at' => beginOfDay($stat_at), 'ec_at' => endOfDay($stat_at)],
                'columns' => 'distinct device_id',
            ]);

            $r_num = count($users);

            echoLine(date('Y-m-d', $stat_at), '激活', $a_num, '注册', $r_num, '注册率', sprintf("%0.2f", $r_num/$a_num));
        }


        $partner_id = 27;
        for($i = 1; $i < 30; $i++){
            $stat_at = time() - $i * 24 *3600;

            $users = Users::find(['conditions' => 'partner_id=:partner_id: and device_id > 1 and (login_type="" or login_type is null) and created_at>=:sc_at: and created_at<=:ec_at:',
                'bind' => ['partner_id' => $partner_id, 'sc_at' => beginOfDay($stat_at), 'ec_at' => endOfDay($stat_at)],
                'columns' => 'id,manufacturer,device_id',
            ]);

            $data = [];
            foreach($users as $user){
                if(isset($data[$user->manufacturer])){
                    $data[$user->manufacturer] += 1;
                }else{
                    $data[$user->manufacturer] = 1;
                }
            }

            arsort($data);
            echoLine(date('Y-m-d', $stat_at));
            print_r($data);
        }


    }
}