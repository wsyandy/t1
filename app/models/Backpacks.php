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

        // page set
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
        return array(
            'id' => $this->id,
            'target_id' => $this->target_id,
            'number' => $this->number,
            'image' => $this->image
        );
    }
}