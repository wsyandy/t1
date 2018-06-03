<?php
/**
 * Created by PhpStorm.
 * User: meixinghao
 * Date: 2018/6/3
 * Time: 下午8:36
 */

namespace api;

class FeedCommentsController extends BaseController
{
    function createAction()
    {
        $feed_id = $this->params('feed_id');
        $content = $this->params('content');
        $feed = \Feeds::findFirstById($feed_id);

        if (!$feed) {
            return $this->renderJSON(ERROR_CODE_FAIL, '动态不存在');
        }

        if (isBlank($content)) {
            return $this->renderJSON(ERROR_CODE_FAIL, '内容不能为空');
        }

        if (mb_strlen($content) > 1500) {
            return $this->renderJSON(ERROR_CODE_FAIL, '内容不能大于1500字');
        }

        $feed_comment = \FeedComments::createFeedComment($this->currentUser(), $feed, ['content' => $content]);

        if ($feed_comment) {
            return $this->renderJSON(ERROR_CODE_SUCCESS, '', $feed_comment->toJson());
        }

        return $this->renderJSON(ERROR_CODE_FAIL, '评论失败');
    }
}