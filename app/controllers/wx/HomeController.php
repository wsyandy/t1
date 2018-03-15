<?php

namespace wx;

class HomeController extends BaseController
{
    public function indexAction()
    {

        $url = $this->session->get('weixin_return_url');
        info('授权回来', $url);

        if ($url) {
            $this->response->redirect($url);
            return;
        }

        $this->response->redirect('/wx/payments/weixin?ts=' . time());
    }

    function errorAction()
    {

    }

}