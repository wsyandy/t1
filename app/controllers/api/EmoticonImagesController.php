<?php
/**
 * Created by PhpStorm.
 * User: apple
 * Date: 2018/1/30
 * Time: 下午7:42
 */
namespace api;

class EmoticonImagesController extends BaseController
{
    function indexAction()
    {
        $page = $this->params('page', 1);
        $per_page = $this->params('per_page', 100);
        $emoticon_images = \EmoticonImages::findValidList($this->currentUser(), $page, $per_page);
        return $this->renderJSON(ERROR_CODE_SUCCESS, '', $emoticon_images->toJson('emoticon_images', 'toSimpleJson'));
    }
}