<?php

class WishHistories extends BaseModel
{
    /**
     * @type Users
     */
    private $_user;
    /**
     * @type ProductChannels
     */
    private $_product_channel;


    function toSimpleJson()
    {
        $guarded_number = \WishHistories::getGuardedNumber($this->product_channel_id, $this->id);
        return [
            'id' => $this->id,
            'user_id' => $this->user_id,
            'wish_text' => $this->wish_text,
            'user_nickname' => $this->user_nickname,
            'user_uid' => $this->user->uid,
            'user_avatar_url' => $this->user->avatar_url,
            'guarded_number' => $guarded_number
        ];
    }

    static function createWishHistories($opts)
    {
        $amount = fetch($opts, 'amount');
        $user_id = fetch($opts, 'user_id');
        $wish_text = fetch($opts, 'wish_text');
        $product_channel_id = fetch($opts, 'product_channel_id');

        $opts = ['remark' => '发布愿望消耗' . $amount . '钻石'];
        $result = \AccountHistories::changeBalance($user_id, ACCOUNT_TYPE_RELEASE_WISH_EXPENSES, $amount, $opts);
        if (!$result) {
            return null;
        }

        $wish_histories = new \WishHistories();
        $wish_histories->user_id = $user_id;
        $wish_histories->wish_text = $wish_text;
        $wish_histories->product_channel_id = $product_channel_id;
        if ($wish_histories->save()) {
            $user_db = \Users::getUserDb();
            $guard_wish_key = \WishHistories::getGuardWishKey($product_channel_id);
            $user_db->zadd($guard_wish_key, 0, $wish_histories->id);
            return $wish_histories->id;
        }
    }

    static function getGuardWishKey($product_channel_id)
    {
        return 'guarded_wish_for_product_channel_' . $product_channel_id;
    }

    //获取愿望分页列表
    static function findByRelationsForWish($relations_key, $page, $per_page)
    {
        $user_db = \Users::getUserDb();

        $offset = $per_page * ($page - 1);
        $res = $user_db->zrevrange($relations_key, $offset, $offset + $per_page - 1, 'withscores');
        $wish_history_ids = [];
        info('全部数据', $res);
        foreach ($res as $wish_history_id => $guarded_number) {
            $wish_history_ids[] = $wish_history_id;
        }
        if (!$wish_history_ids) {
            return null;
        }

        $wish_histories = self::findByIds($wish_history_ids);

        $total_entries = $user_db->zcard($relations_key);
        $pagination = new PaginationModel($wish_histories, $total_entries, $page, $per_page);
        $pagination->clazz = 'WishHistories';

        return $pagination;
    }

    static function getGuardedNumber($product_channel_id, $id)
    {
        $user_db = \Users::getUserDb();
        $key = self::getGuardWishKey($product_channel_id);
        $guarded_number = $user_db->zscore($key, $id);
        info('当前愿望的守护数', $guarded_number);
        return $guarded_number;
    }
}