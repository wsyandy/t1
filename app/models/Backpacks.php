<?php
/**
 * Created by PhpStorm.
 * User: admin
 * Date: 2018/5/8
 * Time: 13:46
 */
class Backpacks extends BaseModel
{

    static public function findListByUserId($user, $opt)
    {
        if (isDevelopmentEnv()) {
            $user = (object)['id' => 1];
        }

        // search for where
        $conditions = [
            'conditions' => 'user_id = :user_id:',
            'bind' => [
                'user_id' => $user->id
            ],
            'order' => 'id desc'
        ];

        if (!empty($opt)) {
            $conditions['conditions'] .= ' and type = :type:';
            $conditions['bind']['type'] = $opt['type'];
        }

        // no page
        $page = 1;
        $per_page = 100;

        $list = \Backpacks::findPagination($conditions, $page, $per_page);
        return $list;
    }


    /**
     * @todo 实际返回客户端的数据体
     * @return array
     */
    public function toSimpleJson()
    {
        if ($this->type == BACKPACK_GIFT_TYPE) {
            // 礼物背包
            $gift = $this->getGift();

            return array(
                'id' => $this->id,
                'number' => $this->number,
                'image_url' => $gift->getImageUrl(),
                'svga_image_name' => $gift->getSvgaImageName(),
                'render_type' => $gift->render_type,
                'svga_image_url' => $gift->getImageSmallUrl(),
                'expire_day' => $gift->expire_day,
                'show_rank' => $gift->show_rank
            );
        }
        return array(
            'id' => $this->id,
            'number' => $this->number
        );

    }


    /**
     * @return object
     */
    public function getGift()
    {
        $gift = Gifts::findFirstById($this->target_id);
        return $gift;
    }

}