<?php
/**
 * Created by PhpStorm.
 * User: apple
 * Date: 2018/1/5
 * Time: 下午6:01
 */

namespace admin;

class AlbumsController extends BaseController
{
    function detailAction()
    {
        $page = $this->params('page');
        $per_page = $this->params('per_page', 30);
        $user_id = $this->params('user_id');
        $auth_status = $this->params('auth_status');
        $cond = ['conditions' => 'user_id =' . $user_id, 'order' => 'id asc'];

        if ($auth_status) {
            $cond['conditions'] .= " and auth_status = $auth_status";
        }

        $albums = \Albums::findPagination($cond, $page, $per_page);

        $this->view->albums = $albums;
        $this->view->user_id = $user_id;
        $this->view->auth_status = $auth_status;
    }


    function updateAction()
    {
        $album_id = $this->params('id');
        $auth_status = $this->params('auth_status');
        $album = \Albums::findFirstById($album_id);
        $album->auth_status = $auth_status;

        //选择头像使用
        if (1 == $album->user_id) {
            $hot_cache = \Albums::getHotWriteCache();
            $auth_type = $this->params('auth_type');

            if ($auth_type) {

                if ($auth_type == 1) {
                    $hot_cache->zrem("albums_auth_type_2_list_user_id_" . $album->user_id, $album->id);
                    $hot_cache->zrem("albums_auth_type_3_list_user_id_" . $album->user_id, $album->id);
                } elseif ($auth_type == 2) {
                    $hot_cache->zrem("albums_auth_type_1_list_user_id_" . $album->user_id, $album->id);
                    $hot_cache->zrem("albums_auth_type_3_list_user_id_" . $album->user_id, $album->id);
                } else {
                    $hot_cache->zrem("albums_auth_type_1_list_user_id_" . $album->user_id, $album->id);
                    $hot_cache->zrem("albums_auth_type_2_list_user_id_" . $album->user_id, $album->id);
                }

                $hot_cache->zadd("albums_auth_type_{$auth_type}_list_user_id_" . $album->user_id, time(), $album->id);
                $hot_cache->zadd("albums_auth_type_total_list_user_id_" . $album->user_id, time(), $album->id);
                return $this->renderJSON(ERROR_CODE_SUCCESS, '');
            }

            if (AUTH_SUCCESS == $album->auth_status) {
                $hot_cache->zadd("albums_auth_success_list_user_id" . $album->user_id, time(), $album->id);
            } else {
                $hot_cache->zrem("albums_auth_success_list_user_id" . $album->user_id, $album->id);
            }
        }

        $album->update();

        return $this->renderJSON(ERROR_CODE_SUCCESS, '');
    }

    function batchUpdateAction()
    {
        $album_ids = $this->params('ids');
        $auth_status = $this->params('auth_status');
        $hot_cache = \Albums::getHotWriteCache();
        $auth_type = $this->params('auth_type');

        foreach ($album_ids as $album_id) {
            $album = \Albums::findFirstById($album_id);
            $album->auth_status = $auth_status;

            //选择头像使用
            if (1 == $album->user_id) {
                if ($auth_type) {

                    if ($auth_type == 1) {
                        $hot_cache->zrem("albums_auth_type_2_list_user_id_" . $album->user_id, $album->id);
                        $hot_cache->zrem("albums_auth_type_3_list_user_id_" . $album->user_id, $album->id);
                    } elseif ($auth_type == 2) {
                        $hot_cache->zrem("albums_auth_type_1_list_user_id_" . $album->user_id, $album->id);
                        $hot_cache->zrem("albums_auth_type_3_list_user_id_" . $album->user_id, $album->id);
                    } else {
                        $hot_cache->zrem("albums_auth_type_1_list_user_id_" . $album->user_id, $album->id);
                        $hot_cache->zrem("albums_auth_type_2_list_user_id_" . $album->user_id, $album->id);
                    }

                    $hot_cache->zadd("albums_auth_type_total_list_user_id_" . $album->user_id, time(), $album->id);
                    $hot_cache->zadd("albums_auth_type_{$auth_type}_list_user_id_" . $album->user_id, time(), $album->id);
                    continue;
                }

                if (AUTH_SUCCESS == $album->auth_status) {
                    $hot_cache->zadd("albums_auth_success_list_user_id" . $album->user_id, time(), $album->id);
                } else {
                    $hot_cache->zrem("albums_auth_success_list_user_id" . $album->user_id, $album->id);
                }
            }

            $album->update();
        }

        return $this->renderJSON(ERROR_CODE_SUCCESS, '');
    }
}