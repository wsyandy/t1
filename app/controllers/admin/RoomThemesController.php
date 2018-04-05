<?php
/**
 * Created by PhpStorm.
 * User: apple
 * Date: 2018/2/24
 * Time: 上午10:54
 */

namespace admin;

class RoomThemesController extends BaseController
{
    function indexAction()
    {
        $page = $this->params('page');
        $per_page = 30;
        $room_themes = \RoomThemes::findPagination(['order' => 'id desc'], $page, $per_page);
        $this->view->room_themes = $room_themes;
    }

    function newAction()
    {
        $room_theme = new \RoomThemes();
        $this->view->room_theme = $room_theme;
    }

    function createAction()
    {
        $room_theme = new \RoomThemes();
        $this->assign($room_theme, 'room_theme');
        if ($room_theme->save()) {
            \OperatingRecords::logAfterCreate($this->currentOperator(), $room_theme);
            return $this->renderJSON(ERROR_CODE_SUCCESS, '', array('room_theme' => $room_theme->toJson()));
        } else {
            return $this->renderJSON(ERROR_CODE_FAIL, '', '创建失败');
        }
    }

    function editAction()
    {
        $room_theme = \RoomThemes::findById($this->params('id'));
        $this->view->room_theme = $room_theme;
    }

    function updateAction()
    {
        $room_theme = \RoomThemes::findById($this->params('id'));
        $this->assign($room_theme, 'room_theme');
        \OperatingRecords::logBeforeUpdate($this->currentOperator(), $room_theme);
        if ($room_theme->update()) {
            return $this->renderJSON(ERROR_CODE_SUCCESS, '', array('room_theme' => $room_theme->toJson()));
        } else {
            return $this->renderJSON(ERROR_CODE_FAIL, '更新失败');
        }
    }

    function platformsAction()
    {
        $id = $this->params('id');
        $room_theme = \RoomThemes::findFirstById($id);
        $platforms = \Products::$PLATFORMS;
        $all_select_platforms = explode(',', $room_theme->platforms);
        $this->view->id = $id;
        $this->view->platforms = $platforms;
        $this->view->all_select_platforms = $all_select_platforms;
    }

    function updatePlatformsAction()
    {
        $id = $this->params('id');
        $room_theme = \RoomThemes::findFirstById($id);
        $platforms = $this->params('platforms', ['*']);
        if (in_array('*', $platforms)) {
            $platforms = ['*'];
        }

        $room_theme->platforms = implode(',', $platforms);
        \OperatingRecords::logBeforeUpdate($this->currentOperator(), $room_theme);
        if ($room_theme->update()) {
            return $this->renderJSON(ERROR_CODE_SUCCESS, '', ['error_url' => '/admin/room_themes']);
        } else {
            return $this->renderJSON(ERROR_CODE_FAIL, '配置失败');
        }
    }

    function productChannelIdsAction()
    {
        $id = $this->params('id');
        $room_theme = \RoomThemes::findFirstById($id);

        $product_channels = \ProductChannels::find(['id' => 'desc']);

        $select_product_channel_ids = [];
        $product_channel_ids = $room_theme->product_channel_ids;
        if (isPresent($product_channel_ids)) {
            $select_product_channel_ids = explode(',', $product_channel_ids);
            $select_product_channel_ids = array_filter($select_product_channel_ids);
        }

        $this->view->select_product_channel_ids = $select_product_channel_ids;

        $this->view->product_channels = $product_channels;
        $this->view->id = $id;
    }

    function updateProductChannelIdsAction()
    {
        $id = $this->params('id');
        $room_theme = \RoomThemes::findFirstById($id);
        if (isBlank($room_theme)) {
            return $this->renderJSON(ERROR_CODE_FAIL, '礼物不存在');
        }

        $product_channel_ids = $this->params('product_channel_ids');
        if ($product_channel_ids) {
            $product_channel_ids = implode(',', $product_channel_ids);
            $room_theme->product_channel_ids = ',' . $product_channel_ids . ',';
        } else {
            $room_theme->product_channel_ids = '';
        }

        \OperatingRecords::logBeforeUpdate($this->currentOperator(), $room_theme);
        if ($room_theme->update()) {
            return $this->renderJSON(ERROR_CODE_SUCCESS, '', ['room_theme' => $room_theme->toJson]);
        } else {
            return $this->renderJSON(ERROR_CODE_FAIL, '配置失败');
        }
    }
}