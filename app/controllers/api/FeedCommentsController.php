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

        $feed_comment = \FeedComments::createFeedComment($this->currentUser(), $feed, ['content' => $content]);

        if ($feed_comment) {
            return $this->renderJSON(ERROR_CODE_SUCCESS, '');
        }

        return $this->renderJSON(ERROR_CODE_FAIL, '评论失败');
    }

    function indexAction()
    {
        $feed = \Feeds::findFirstById($this->params('feed_id'));

        if (!$feed) {
            return $this->renderJSON(ERROR_CODE_FORM, '动态不存在');
        }

        $page = $this->params('page');
        $per_page = $this->params('per_page', 10);
        $feed_comments = $feed->findFeedCommentList($page, $per_page);
        $this->renderJSON(ERROR_CODE_SUCCESS, '', $feed_comments->toJson('feed_comments', 'toSimpleJson'));
    }
}