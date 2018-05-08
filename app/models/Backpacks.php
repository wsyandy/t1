<?php
/**
 * Created by PhpStorm.
 * User: admin
 * Date: 2018/5/8
 * Time: 13:46
 */
class Backpacks extends BaseModel
{

    /**
     * @var bool 开发时使用，默认false
     */
    static private $development = false;


    static public function findListByUserId($user, $opt)
    {
        // is or not in dev
        if (self::$development) {
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

        // page set
        $page = 1;
        $per_page = 100;

        $list = \Backpacks::findPagination($conditions, $page, $per_page);
        return $list;
    }


    /**
     * @desc 开发
     */
    static public function setDev()
    {
        self::$development = true;
    }


    /**
     * @todo 实际返回客户端的数据体
     * @return array
     */
    public function toSimpleJson()
    {
        return array(
            'id' => $this->id,
            'number' => $this->number,
            'image_url' => StoreFile::getUrl($this->image)
        );
    }
}