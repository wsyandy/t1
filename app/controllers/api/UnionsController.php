<?php
/**
 * Created by PhpStorm.
 * User: apple
 * Date: 2018/4/20
 * Time: 下午8:45
 */
namespace api;

class UnionsController extends BaseController
{
    function searchAction()
    {
        $uid = intval($this->params('uid'));
        $sid = $this->params('sid');
        $user = $this->currentUser();

        $opts = [];

        $name = $this->params('name');

        if (isPresent($name)) {
            $opts['name'] = $name;
        }

        if ($uid) {
            $opts['uid'] = $uid;
        }

        if (isBlank($opts)) {
            return $this->renderJSON(ERROR_CODE_FAIL, '参数错误');
        }

        $opts['type'] = UNION_TYPE_PRIVATE;

        $page = 1;

        $per_page = 1;

        $unions = \Unions::search($user, $page, $per_page, $opts);

        if (count($unions)) {

            foreach ($unions as $union) {
                $union->url = "url://m/unions/my_union?sid={$sid}&union_id={$union->id}";
            }

            return $this->renderJSON(ERROR_CODE_SUCCESS, '', $unions->toJson('unions', 'toSimpleJson'));

        } else {

            return $this->renderJSON(ERROR_CODE_FAIL, '家族不存在');
        }
    }

    function recommendAction()
    {
        $unions = \Unions::recommend(1, 5);

        $sid = $this->params('sid');

        if (count($unions)) {

            foreach ($unions as $union) {
                $union->url = "url://m/unions/my_union?sid={$sid}&union_id={$union->id}";
            }

            return $this->renderJSON(ERROR_CODE_SUCCESS, '', $unions->toJson('unions', 'toSimpleJson'));
        }

        return $this->renderJSON(ERROR_CODE_FAIL, "暂无热搜");
    }
}