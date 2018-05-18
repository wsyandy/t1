<?php

/**
 * Created by PhpStorm.
 * User: apple
 * Date: 18/3/24
 * Time: 下午10:17
 */
class BannedWords extends BaseModel
{

    static $WORD = ["骚", "嫖", "嫖娼", "黄片", "毛片", "聊骚", "涉黄", "阴毛", "性爱", "做爱", "交配", "阴道", "口交", "鸡巴", "性交", "性高潮", "SM",
        "多P", "群交", "月经", "成人", "色情", "犯罪", "诈骗", "传销", "棋牌", "彩票", "假钞", "政治", "艹你妈", "干你娘", "办理", "跪舔", "小婊砸",
        "我日", "超赚", "领导人", "作弊", "毒品", "淫秽", "异性", "私交", "涉嫌", "欺诈", "抢购", "招人", "跪求嫖", "艹", "操B", "艹B", "男模",
        "女模", "淫荡", "嫩模", "娇喘", "毒", "赌厅", "调情", '介绍所', '囚禁', '虐待', '包邮', '出售', '官方', '服务', '屁股', '搞基', '约炮', 'sao',
        '磕炮', '偷情', '赌', '捕鱼', '牛牛', '打地鼠', '金花', '系统小助手', '系统', '嫖'];

    /**
     * @param $word
     * @return array
     */
    static function checkWord($word)
    {
        $word = str_replace(self::$WORD, '**', $word);
        return [false, $word];

        //临时解决
        if (mb_strlen($word) == 1) {
            return [false, $word];
        }

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
        $new_word = str_replace($search_word, '*', $word);

        return [true, $new_word];
    }

}