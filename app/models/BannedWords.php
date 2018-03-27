<?php

/**
 * Created by PhpStorm.
 * User: apple
 * Date: 18/3/24
 * Time: 下午10:17
 */
class BannedWords extends BaseModel
{

    /**
     * @param $word
     * @return array
     */
    static function checkWord($word)
    {
        $conditions = [
            'conditions' => "word like :word:",
            'bind' => ['word' => '%' . $word . '%'],
            'order' => 'id desc'
        ];

        $banned_word = self::findFirst($conditions);

        if (!$banned_word) {
            return [false, $word];
        }

        $search_word = mbStrSplit($banned_word->word);

        //临时解决
        if (mb_strlen($word) == 1) {
            return [false, $word];
        }

        $new_word = str_replace($search_word, '*', $word);

        return [true, $new_word];
    }

}