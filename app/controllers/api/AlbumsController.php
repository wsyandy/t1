<?php
/**
 * Created by PhpStorm.
 * User: apple
 * Date: 18/1/2
 * Time: 下午10:21
 */

namespace api;


class AlbumsController extends BaseController
{

    function createAction()
    {
        $user = $this->currentUser();
        // 更新头像
        $image_file = $this->file('image_file');
        $album = \Albums::uploadImage($user, $image_file);
        if ($album) {
            return $this->renderJSON(ERROR_CODE_SUCCESS, '成功', ['album' => $album->toSimpleJson()]);
        }

        return $this->renderJSON(ERROR_CODE_FAIL, '上传失败');
    }

    function destroyAction()
    {

        $album = \Albums::findFirstById($this->params('id', 0));
        if ($album) {
            $album->delete();
            return $this->renderJSON(ERROR_CODE_SUCCESS, '删除成功');
        }

        return $this->renderJSON(ERROR_CODE_FAIL, '删除失败');
    }

}