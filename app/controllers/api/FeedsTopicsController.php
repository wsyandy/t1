<?php
/**
 * Created by PhpStorm.
 * User: meixinghao
 * Date: 2018/6/3
 * Time: 下午8:36
 */

namespace api;

class FeedsTopicsController extends BaseController
{
    function indexAction()
    {
        $name = $this->params('name');
        $page = $this->params('page');
        $per_page = $this->params('per_page', 10);
        $feed_topics = \FeedTopics::findTotalFeedTopics($page, $per_page);
        \Users::findBatch($feed_topics);
        return $this->renderJSON(ERROR_CODE_SUCCESS, '', $feed_topics->toJson('feed_topics', 'toSimpleJson'));
    }

    function createAction()
    {
        $name = $this->params('name');

        if (mb_strlen($name) < 6) {
            return $this->renderJSON(ERROR_CODE_FAIL, '字数太短了');
        }

        if (mb_strlen($name) > 15) {
            return $this->renderJSON(ERROR_CODE_FAIL, '字数太长了');
        }

        $feed_topic = \FeedTopics::createFeedTopic($this->currentUser(), ['name' => $name]);

        if ($feed_topic) {
            return $this->renderJSON(ERROR_CODE_SUCCESS, '', $feed_topic->toSimpleJson());
        }

        return $this->renderJSON(ERROR_CODE_FAIL, '创建失败');
    }
}