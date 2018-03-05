<?php

/**
 * Created by PhpStorm.
 * User: apple
 * Date: 2018/2/26
 * Time: 下午9:37
 */
class ShareHistories extends BaseModel
{
    /**
     * @type Users
     */
    private $_user;

    static $STATUS = [SHARE_STATUS_WAIT => '未知', SHARE_STATUS_SUCCESS => '分享成功',
        SHARE_STATUS_FAIL => '分享失败', SHARE_STATUS_CANCEL => '分享取消'];

    static $TYPE = [SHARE_TYPE_WEIXIN => '微信', SHARE_TYPE_WEIXIN_CIRCLE => '朋友圈', SHARE_TYPE_SINA => '新浪微博',
        SHARE_TYPE_QQ => 'QQ', SHARE_TYPE_QZONE => 'QQ空间', SHARE_TYPE_URL => '链接', SHARE_TYPE_CARD => '邀请卡',];

    static function createShareHistory($opts)
    {
        $user_id = fetch($opts, 'user_id');
        $product_channel_id = fetch($opts, 'product_channel_id');
        $share_source = fetch($opts, 'share_source');
        $data = fetch($opts, 'data');

        $share_history = new ShareHistories();
        $share_history->user_id = $user_id;
        $share_history->product_channel_id = $product_channel_id;
        $share_history->share_source = $share_source;
        $share_history->data = json_encode($data, JSON_UNESCAPED_UNICODE);
        $share_history->status = SHARE_STATUS_WAIT;

        $share_history->save();
        return $share_history;
    }

    function getShareUrl($root)
    {
        return $root . 'shares?share_history_id=' . $this->id;
    }
}