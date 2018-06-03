<?php
/**
 * Created by PhpStorm.
 * User: meixinghao
 * Date: 2018/6/3
 * Time: 下午8:36
 */

namespace api;

class FeedsController extends BaseController
{
    //type new 最新 follow 关注 essence 精华
    function indexAction()
    {
        $type = $this->params('type');
        $feed_topic_id = $this->params('feed_topic_id');
        $page = $this->params('page');
        $per_page = $this->params('per_page', 10);

        if ('follow' == $type) {
            $feeds = \Feeds::findFollowFeeds($this->currentUser(), $page, $per_page);
        } else {
            $feeds = \Feeds::findTotalFeeds($page, $per_page);
        }

        \Users::findBatch($feeds);

        foreach ($feeds as $feed) {
            $feed->is_follow = $feed->isFollow($this->currentUserId());
            $feed->is_disliked = $feed->isDisliked($this->currentUserId());
            $feed->is_liked = $feed->isLiked($this->currentUserId());
        }
        return $this->renderJSON(ERROR_CODE_SUCCESS, '', $feeds->toJson('feeds', 'toSimpleJson'));
    }

    function createAction()
    {
        $content = $this->params('content');
        $feed_topic_id = $this->params('feed_topic_id');
        $content = preg_replace("/\n+/", "\n", $content);
        $content = preg_replace("/( |\t)+/", " ", $content);

        if (isBlank($content)) {
            return $this->renderJSON(ERROR_CODE_FAIL, '内容不能为空');
        }

        if (mb_strlen($content) > 1500) {
            return $this->renderJSON(ERROR_CODE_FAIL, '动态内容过长');
        }

        if ($feed_topic_id) {
            $feed_topic = \FeedTopics::findFirstById($feed_topic_id);
            if (!$feed_topic) {
                return $this->renderJSON(ERROR_CODE_FAIL, '话题不存在');
            }
        }

        $location = trim($this->params('location', ''));

        $feed = \Feeds::createdFeed($this->currentUser(), [
                'content' => $content,
                'location' => $location,
                'feed_topic_id' => $feed_topic_id,
                'feed_topic' => $feed_topic,
                'duration' => $this->params('duration', 0),
                'files' => $_FILES
            ]
        );

        if ($feed) {
            return $this->renderJSON(ERROR_CODE_SUCCESS, '');
        } else {
            return $this->renderJSON(ERROR_CODE_FAIL, '创建失败');
        }
    }

    function deleteAction()
    {

    }

    //点赞
    function likeAction()
    {
        $id = $this->params('id');
        $feed = \Feeds::findFirstById($id);
        if (!$feed) {
            return $this->renderJSON(ERROR_CODE_FAIL, '动态不存在');
        }
        $feed->like($this->currentUser());
        return $this->renderJSON(ERROR_CODE_SUCCESS, '');
    }

    //踩
    function dislikeAction()
    {
        $id = $this->params('id');
        $feed = \Feeds::findFirstById($id);
        if (!$feed) {
            return $this->renderJSON(ERROR_CODE_FORM, '动态不存在');
        }
        $feed->dislike($this->currentUser());
        return $this->renderJSON(ERROR_CODE_SUCCESS, '');
    }

    //关注
    function followAction()
    {
        $id = $this->params('id');
        $feed = \Feeds::findFirstById($id);
        if (!$feed) {
            return $this->renderJSON(ERROR_CODE_FORM, '动态不存在');
        }
        $feed->follow($this->currentUser());
        return $this->renderJSON(ERROR_CODE_SUCCESS, '');
    }

    //动态详情
    function detailAction()
    {
        $id = $this->params('id');
        $feed = \Feeds::findFirstById($id);
        if (!$feed) {
            return $this->renderJSON(ERROR_CODE_FORM, '动态不存在');
        }

        return $this->renderJSON(ERROR_CODE_SUCCESS, '', $feed->toDetailJson());
    }
}