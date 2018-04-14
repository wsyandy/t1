<?php
/**
 * Created by PhpStorm.
 * User: maoluanjuan
 * Date: 17/01/2018
 * Time: 17:11
 */

namespace api;

class ChatsController extends BaseController
{
    function indexAction()
    {
        if (isBlank($this->params('user_id')) || intval($this->params('user_id')) != SYSTEM_ID) {
            return $this->renderJSON(ERROR_CODE_FAIL, '参数错误');
        }

        $page = $this->params('page');
        $per_page = $this->params('per_page', 30);

        $chats = \Chats::findChatsList($this->currentUser(), $page, $per_page, $this->params('user_id'));
        $user = \Users::findById($this->params('user_id'));

        $this->currentUser()->delUnreadMessages();

        return $this->renderJSON(
            ERROR_CODE_SUCCESS,
            '',
            array_merge(
                $user->toChatJson(),
                $chats->toJson('chats', 'toJson'))
        );
    }

    function unreadNumAction()
    {
        return $this->renderJSON(ERROR_CODE_SUCCESS, '', ['unread_num' => $this->currentUser()->unreadMessagesNum()]);
    }

    function createAction()
    {
        $user_id = $this->params('user_id');
        $content = $this->params('content');
        $content_type = $this->params('content_type'); // text image voice
        $file = $this->params('file');

        if (!$this->currentUser()->isFriend($this->otherUser())) {
            return $this->renderJSON(ERROR_CODE_FAIL, '对方不是您的好友');
        }

        return $this->renderJSON(ERROR_CODE_SUCCESS, '发送成功');
    }
}