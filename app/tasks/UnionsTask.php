<?php
/**
 * Created by PhpStorm.
 * User: apple
 * Date: 2018/3/15
 * Time: 下午5:42
 */

class UnionsTask extends \Phalcon\Cli\Task
{
    //推荐家族task任务
    function recommendAction()
    {
        $db = Users::getUserDb();
        $key = "total_union_fame_value_day_" . date("Ymd", strtotime('-1 day'));

        $unions = Unions::findBy(['recommend' => STATUS_ON]);

        foreach ($unions as $union) {
            $union->recommend = STATUS_OFF;
            $union->update();
        }

        $union_recommend_key = "union_recommend_list";
        $db->zclear($union_recommend_key);

        $union_ids = $db->zrevrange($key, 0, 4, true);

        info($union_ids);

        foreach ($union_ids as $union_id => $value) {
            $db->zadd($union_recommend_key, $value, $union_id);
        }
    }
}