<?php

/**
 * Created by PhpStorm.
 * User: apple
 * Date: 18/3/24
 * Time: 下午10:17
 */
class BannedWords extends BaseModel
{

    static function checkWord($word)
    {
        $conditions = ['conditions' => "word like :word:", 'bind' => ['word' => '%' . $word . '%']];
        $banned_word = self::findFirst($conditions);
        if (!$banned_word) {
            return [false, $word];
        }

        $new_word = str_replace($banned_word->word, '***', $word);

        return [true, $new_word];
    }
    
}