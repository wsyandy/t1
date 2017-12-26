<?php

class WapVisitHistories extends BaseModel
{

    static function updateVisitNumByWapVisit($wap_visit_id, $ip)
    {
        $hot_cache = self::getHotWriteCache();
        $key = "wap_visit_history_visit_{$wap_visit_id}_{$ip}";
        $visit_num = $hot_cache->incr($key);
        $hot_cache->expire($key, 60 * 60 * 24 * 2);

        $wap_visit_history = self::findFirst([
            'conditions' => 'wap_visit_id = :wap_visit_id: and ip = :ip:',
            'bind' => ['wap_visit_id' => $wap_visit_id, 'ip' => $ip],
            'order' => 'id desc'
        ]);

        if (!$wap_visit_history) {
            $wap_visit_history = new WapVisitHistories();
            $wap_visit_history->ip = $ip;
            $wap_visit_history->wap_visit_id = $wap_visit_id;
            $wap_visit_history->save();
        }

        debug($wap_visit_id, $ip);

        if ($wap_visit_history->visit_num <= $visit_num) {
            $wap_visit_history->visit_num = $visit_num;
        }

        $wap_visit_history->save();
    }

    static function updateDownNumByWapVisit($wap_visit_id, $ip)
    {
        $hot_cache = self::getHotWriteCache();
        $key = "wap_visit_history_down_{$wap_visit_id}_{$ip}";
        $down_num = $hot_cache->incr($key);
        $hot_cache->expire($key, 60 * 60 * 24 * 2);

        $wap_visit_history = self::findFirst([
            'conditions' => 'wap_visit_id = :wap_visit_id: and ip = :ip:',
            'bind' => ['wap_visit_id' => $wap_visit_id, 'ip' => $ip],
            'order' => 'id desc'
        ]);

        if (isBlank($wap_visit_history)) {
            return;
        }

        if ($wap_visit_history->down_num <= $down_num) {
            $wap_visit_history->down_num = $down_num;
        }

        debug($wap_visit_id, $ip);

        $wap_visit_history->save();
    }

    public static function clearExpire()
    {
    }

}