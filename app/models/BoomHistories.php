<?php
/**
 * Created by PhpStorm.
 * User: admin
 * Date: 2018/5/9
 * Time: 19:45
 */

class BoomHistories extends BaseModel
{

    static $TYPE = [1 => '礼物', 2 => '钻石', 3 => '金币'];

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


    public function getGift()
    {
        if ($this->target_id == 0) {
            return null;
        }
        $gifts = Gifts::findFirstById($this->target_id);
        return $gifts;
    }


    /**
     * 爆礼物id 倒叙日志排行
     * @param int $per_page
     * @return PaginationModel
     */
    static public function historiesTopList($per_page = 10)
    {
        $conditions = array(
            'order' => 'id desc',
        );
        $list = BoomHistories::findPagination($conditions, 1, $per_page);
        return $list;
    }


    /**
     * @return array
     */
    public function toSimpleJson()
    {

        if ($this->type != BACKPACK_GIFT_TYPE) {

            if ($this->type == BACKPACK_DIAMOND_TYPE) {
                $name = '钻石';
                $image = Backpacks::getDiamondImage();
            } else {
                $name = '金币';
                $image = Backpacks::getGoldImage();
            }

            $target = (object)array(
                'name' => $name,
                'image_url' => $image
            );

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