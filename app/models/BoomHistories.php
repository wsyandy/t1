<?php
/**
 * Created by PhpStorm.
 * User: admin
 * Date: 2018/5/9
 * Time: 19:45
 */
class BoomHistories extends BaseModel
{
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
        $gift = Gifts::findFirstById($this->target_id);
        $user = Users::findFirstById($this->user_id);
        return array(
            'user' => $user->nickname,
            'name' => $gift->name,
            'number' => $this->number,
            'image_url' => $gift->getImageUrl(),
        );
    }
}