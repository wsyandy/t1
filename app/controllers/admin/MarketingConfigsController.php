<?php

namespace admin;


class MarketingConfigsController extends BaseController
{

    function indexAction()
    {
        $conds = $this->getConditions('marketing_config');
        $conds['order'] = 'id desc';
        $page = $this->params('page');
        $marketing_configs = \MarketingConfigs::findPagination($conds, $page);
        $this->view->marketing_configs = $marketing_configs;
    }

    function newAction()
    {
        $marketing_config = new \MarketingConfigs();
        $this->view->marketing_config = $marketing_config;
    }

    function createAction()
    {
        $marketing_config = new \MarketingConfigs();
        $this->assign($marketing_config, 'marketing_config');
        $old_config = \MarketingConfigs::findFirstByGdtAccountId($marketing_config->gdt_account_id);
        if ($old_config) {
            $this->renderJSON(ERROR_CODE_FAIL, '已存在广告组' . $marketing_config->gdt_account_id);
            return;
        }

        $marketing_config->operator_id = $this->currentOperator()->id;
        $marketing_config->save();
        \OperatingRecords::logAfterCreate($this->currentOperator(), $marketing_config);

        $this->renderJSON(ERROR_CODE_SUCCESS, '创建成功', ['marketing_config' => $marketing_config->toJson()]);
    }

    function editAction()
    {
        $id = $this->params('id');
        $marketing_config = \MarketingConfigs::findFirstById($id);
        $this->view->marketing_config = $marketing_config;
    }

    function updateAction()
    {
        $id = $this->params('id');
        $marketing_config = \MarketingConfigs::findFirstById($id);
        $this->assign($marketing_config, 'marketing_config');

        $marketing_config->operator_id = $this->currentOperator()->id;
        \OperatingRecords::logBeforeUpdate($this->currentOperator(), $marketing_config);
        $marketing_config->save();
        $this->renderJSON(ERROR_CODE_SUCCESS, '修改成功', ['marketing_config' => $marketing_config->toJson()]);
    }

    public function authorizeAction()
    {
        $id = $this->params('id');
        $marketing_config = \MarketingConfigs::findFirstById($id);
        $client_id = $marketing_config->client_id;
        $redirect_uri = $marketing_config->redirect_uri;
        $redirect_uri = urlencode($redirect_uri);

        $auth_url = 'https://developers.e.qq.com/oauth/authorize?client_id=' . $client_id . '&redirect_uri=' . $redirect_uri . '&scope=user_actions&state=' . $marketing_config->id;
        info($auth_url);

        $this->response->redirect($auth_url);
    }


    function userActionSetsAction()
    {
        $type = $this->params('type'); //$type = 'ANDROID';$type = 'IOS';
        $id = $this->params('id');
        $marketing_config = \MarketingConfigs::findFirstById($id);

        $account_id = $marketing_config->gdt_account_id;
        if ($type == 'IOS') {
            $mobile_app_id = $marketing_config->ios_app_id;
        } else {
            $mobile_app_id = $marketing_config->android_app_id;
        }

        $description = '上报用户web行为';
        $access_token = $marketing_config->getToken();
        $timestamp = time();
        $nonce = randStr(20);

        $url = "https://api.e.qq.com/v1.0/user_action_sets/add?access_token={$access_token}&timestamp={$timestamp}&nonce={$nonce}";
        $body = array(
            'account_id' => $account_id,
            'type' => $type,
            'mobile_app_id' => $mobile_app_id,
            'description' => $description
        );

        $response = httpPost($url, $body);
        $result = json_decode($response->raw_body, true);
        info($marketing_config->id, $body, $result);
        //{"code":0,"message":"ok","data":{"user_action_set_id":1106326598}}
        if (isset($result['code']) && $result['code'] == 0 && isset($result['data'])) {

            $user_action_set_id = fetch($result['data'], 'user_action_set_id');
            if ($type == 'IOS') {
                $marketing_config->ios_user_action_set_id = $user_action_set_id;
            } else {
                $marketing_config->android_user_action_set_id = $user_action_set_id;
            }
            $marketing_config->save();

            $this->renderJSON(ERROR_CODE_SUCCESS, '创建成功');
        } else {
            $this->renderJSON(ERROR_CODE_FAIL, '创建失败');
        }
    }

}