<?php

class HomeController extends \ApplicationController
{
    function indexAction()
    {
        $user_agent = $this->request->getUserAgent();
        $is_pc = true;

        if (preg_match('/Mobile/i', $user_agent)) {
            $is_pc = false;
        }

        $host = $this->getHost();
        $params = $this->params();
        unset($params['_url']);

        if ($is_pc) {
            $forward = [
                "namespace" => "web",
                "controller" => "home",
                "action" => "index",
                "params" => $this->params()
            ];
            $this->dispatcher->forward($forward);
        }
    }
}