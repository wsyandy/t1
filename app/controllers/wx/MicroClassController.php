<?php

namespace wx;

class MicroClassController extends BaseController
{
    public function indexAction()
    {
        $this->view->title="考拉微课";

    }

    function weipeiAction()
    {
        $this->view->title="微培";
    }

    function mineAction()
    {
        $this->view->title="我的";
    }

    function mineCourseAction()
    {
        $this->view->title="我的提问";
    }

    function buyCourseAction()
    {
        $this->view->title="购买记录";
    }



}