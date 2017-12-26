<?php

namespace admin;


class MonitorController extends BaseController
{

    public function redisAction()
    {
        $this->view->setRenderLevel(\Phalcon\Mvc\View::LEVEL_ACTION_VIEW);
        $this->view->pick('/admin/monitor/redis');
    }

    function getCache()
    {
        $endpoint = $this->config->job_queue->endpoint;
        $x_redis = \XRedis::getInstance($endpoint);
        return $x_redis;
    }

    public function asyncStatusAction()
    {
        $domain = $this->request->getHttpHost();
        $name = $this->params('name');
        $cache = $this->getCache();

        $task_result = array();

        info($domain, $name, $this->config->job_queue->endpoint, $this->config->job_queue->tubes);

        foreach ($this->config->job_queue->tubes as $tube_name => $tube_count) {

            $key = 'tube_' . $tube_name;
            // 总共有多少任务: zcard
            $total_task = $cache->zcard($key);

            // 就绪任务个数
            $ready_task = $cache->zcount($key, time() - 2, time());

            // 超时任务个数
            $overtime_task = $cache->zcount($key, '-inf', time() - 2);

            //延时任务
            $delay_task = $cache->zcount($key, time() + 1, '+inf');

            $task_result[] = array('name' => $name, 'total_task' => $total_task, 'delay_task' => $delay_task,
                'ready_task' => $ready_task, 'overtime_task' => $overtime_task, 'tube_name' => $tube_name);
        }

        renderJSON(ERROR_CODE_SUCCESS, '', array('result' => $task_result));
        return;
    }

    public function getTaskStateAction()
    {

        if (isProduction()) {
            $names = ['php0' => '172.16.178.70'];
        } else {
            $names = ['php0' => 'localhost'];
        }

        info($names);

        $task_result = [];
        foreach ($names as $name => $ip) {

            $url = 'http://' . $ip . '/admin/monitor/async_status?name=' . $name;

            $res = httpGet($url);
            $result = json_decode($res->raw_body, true);
            info($url, $result);

            if (is_array($result['result'])) {
                $task_result = array_merge($task_result, $result['result']);
            }
        }

        $count = 0;
        $back_ground_color = 'red';
        $html = '';

        if (count($task_result) > 0) {
            foreach ($task_result as $key => $task) {

                $overtime_task_bg_color = '';

                if (3 == $count) {
                    if ('red' == $back_ground_color) {
                        $back_ground_color = 'green';
                    } else {
                        $back_ground_color = 'red';
                    }
                    $count = 0;
                }

                $count++;
                if ($task['overtime_task'] > 0) {
                    $overtime_task_bg_color = 'danger';
                }

                $html .= "<tr class='{$back_ground_color}'>
                    <td style='white-space: nowrap;'>{$task['name']}</td>
                    <td class='' name='current-tube'>{$task['tube_name']}</td>
                     <td class='' name='current-jobs'>{$task['total_task']}</td>
                     <td class='' name='current-jobs-delayed'>{$task['delay_task']}</td>
                     <td class='' name='current-jobs-ready'>{$task['ready_task']}</td>
                     <td class='{$overtime_task_bg_color}' name='current-jobs-overtime'>{$task['overtime_task']}</td>

                    </tr>";
            }
        }

        echo $html;
    }

}