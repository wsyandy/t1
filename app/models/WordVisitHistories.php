<?php

class WordVisitHistories extends BaseModel
{
    public static function updateVisitNumByWordVisit($word_visit_id, $ip)
    {
        $hot_cache = self::getHotWriteCache();
        $key = "word_visit_history_visit_{$word_visit_id}_{$ip}";
        $visit_num = $hot_cache->incr($key);
        $hot_cache->expire($key, 60 * 60 * 24 * 2);

        $word_visit_history = self::findFirst([
            'conditions' => 'word_visit_id = :word_visit_id: and ip = :ip:',
            'bind' => ['word_visit_id' => $word_visit_id, 'ip' => $ip],
            'order' => 'id desc'
        ]);

        if (!$word_visit_history) {
            $word_visit_history = new WordVisitHistories();
            $word_visit_history->ip = $ip;
            $word_visit_history->word_visit_id = $word_visit_id;
            $word_visit_history->save();
        }

        if ($word_visit_history->visit_num <= $visit_num) {
            $word_visit_history->visit_num = $visit_num;
        }

        info("updateVisitNumByWordVisit", $word_visit_id, $ip);
        $word_visit_history->save();
    }

    public static function updateDownNumByWordVisit($word_visit_id, $ip)
    {
        $hot_cache = self::getHotWriteCache();
        $key = "word_visit_history_down_{$word_visit_id}_{$ip}";
        $down_num = $hot_cache->incr($key);
        $hot_cache->expire($key, 60 * 60 * 24 * 2);

        $word_visit_history = self::findFirst([
            'conditions' => 'word_visit_id = :word_visit_id: and ip = :ip:',
            'bind' => ['word_visit_id' => $word_visit_id, 'ip' => $ip],
            'order' => 'id desc'
        ]);

        if (isBlank($word_visit_history)) {
            return;
        }

        if ($word_visit_history->down_num <= $down_num) {
            $word_visit_history->down_num = $down_num;
        }

        info("updateDownNumByWordVisit", $word_visit_id, $ip);
        $word_visit_history->save();
    }

    public static function clearExpire()
    {
    }
}