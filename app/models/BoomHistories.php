<?php
/**
 * Created by PhpStorm.
 * User: admin
 * Date: 2018/5/9
 * Time: 19:45
 */

class BoomHistories extends BaseModel
{


    /**
     * 新增爆礼物日志
     * @param $user_id
     * @param $target_id
     * @param $type
     * @param $number
     * @return bool|null
     */
    public function createBoomHistories($user_id, $target_id, $type, $number)
    {
        if (empty($type) || empty($number))
            return null;

        if (empty($target_id) && $type == BACKPACK_GIFT_TYPE)
            return null;

        $this->user_id = $user_id;
        $this->target_id = $target_id;
        $this->type = $type;
        $this->number = $number;
        $this->created_at = time();
        return $this->save();
    }


    /**
     * 爆礼物排行榜
     * @return PaginationModel
     */
    static public function topList()
    {
        $conditions = array(
            'order' => 'id desc',
        );
        $list = BoomHistories::findPagination($conditions, 1, 10);
        return $list;
    }


    public function toSimpleJson()
    {
        if ($this->type == BACKPACK_DIAMOND_TYPE) {

            $target = (object)[
                'name'=>'钻石',
                'image_url'=> Backpacks::getDiamondImage()
            ];
        } elseif ($this->type == BACKPACK_GOLD_TYPE) {

            $target = (object)[
                'name'=>'金币',
                'image_url'=> Backpacks::getGoldImage()
            ];
        } else
            $target = Gifts::findFirstById($this->target_id);

        // 获取用户信息
        $user = Users::findFirstById($this->user_id);

        // 返回的数据
        return array(
            // 'id' => $this->id,
            'user' => $user->nickname,
            'name' => $target->name,
            'number' => $this->number,
            'image_url' => $target->image_url,
        );
    }
}