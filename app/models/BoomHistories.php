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
     * 生成爆礼物数据
     * @param $user
     * @param $opt
     * @return bool
     */
    public function createBoom($user, $opt)
    {
        if (isDevelopmentEnv()) {
            $user = (object)['id' => 1];
        }

        $this->user_id = $user->id;
        $this->target_id = $opt['target_id'];
        $this->type = $opt['type'];
        $this->number = $opt['number'];
        $this->created_at = time();
        $this->save();
        return true;
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