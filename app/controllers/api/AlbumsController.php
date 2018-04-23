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

    function indexAction()
    {
        $page = $this->params('page', 1);
        $per_page = $this->params('per_page', 9);
        $user = $this->currentUser();
        //AUTH_SUCCESS
        $cond = [
            'conditions' => "user_id = :user_id: and auth_status != :auth_status:",
            'bind' => ['user_id' => $user->id, 'auth_status' => AUTH_FAIL],
            'order' => 'id desc'
        ];
        $albums = \Albums::findPagination($cond, $page, $per_page);

        return $this->renderJSON(ERROR_CODE_SUCCESS, '', $albums->toJson('albums', 'toSimpleJson'));
    }

    function createAction()
    {
        $user = $this->currentUser();

        $hot_cache = \Users::getHotWriteCache();
        $cache_key = 'albums_upload_cache_' . $this->currentUser()->id;

        $image_files = [];
        for ($i = 0; $i < 27; $i++) {
            $image_file = $this->file('image_file' . $i);
            if (!$image_file) {
                break;
            }
            $md5_val = md5_file($image_file);
            if ($hot_cache->get($cache_key . '_' . $md5_val)) {
                info('重复上传', $image_file);
                continue;
            }

            $hot_cache->setex($cache_key . '_' . $md5_val, 3600 * 12, time());
            $image_files[] = $image_file;
        }

        $res = \Albums::uploadImage($user, $image_files);
        if ($res) {
            return $this->renderJSON(ERROR_CODE_SUCCESS, '成功');
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