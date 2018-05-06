<?php
/**
 * Created by PhpStorm.
 * User: meixinghao
 * Date: 2018/5/6
 * Time: 下午10:00
 */

class SearchHistories extends BaseModel
{
    static $TYPE = ['room' => '房间', 'union' => '家族', 'user' => '用户'];

    static function createHistory($word, $type)
    {
        $word = trim($word);

        if (mb_strlen($word) > 200) {
            return;
        }

        $lock = tryLock("search_history_word_{$word}_type{$type}");

        $cond = [
            'conditions' => 'word = :word: and type = :type:',
            'bind' => ['word' => $word, 'type' => $type]
        ];

        $history = SearchHistories::findFirst($cond);

        if ($history) {
            $history->num += 1;
            $history->save();
        } else {
            $new_history = new SearchHistories();
            $new_history->word = $word;
            $new_history->type = $type;
            $new_history->num = 1;
            $new_history->save();
        }

        unlock($lock);
    }
}