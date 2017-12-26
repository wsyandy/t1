<?php

class SoftVersionsController extends \ApplicationController
{
    function indexAction()
    {
        $id = $this->params('id');
        $id = intval($id);
        $soft_version = \SoftVersions::findFirstById($id);
        if ($soft_version) {

            $this->visitStat();

            $user_agent = $this->request->getUserAgent();
            if (preg_match('/ios|iphone|ipad/i', $user_agent)) {
                if ($soft_version->ios_down_url) {
                    $soft_version->increase('download_num');
                    $this->response->redirect($soft_version->ios_down_url);
                    return;
                }
                // SOFT_VERSION_STATUS_ON 防止影响落地页推广的数据
                $ios_soft_version = \SoftVersions::findFirst(['conditions' => 'product_channel_id=:product_channel_id: and platform=:platform:',
                    'bind' => ['product_channel_id' => $soft_version->product_channel_id, 'platform' => 'ios'],
                    'order' => 'id desc'
                ]);

                info($soft_version->id, $soft_version->product_channel_id, $ios_soft_version);
                if ($ios_soft_version && $ios_soft_version->ios_down_url) {
                    $ios_soft_version->increase('download_num');
                    $this->response->redirect($ios_soft_version->ios_down_url);
                    return;
                }

            } else {

                // 微信浏览器下载
                if ($this->isWeixinClient() && $soft_version->weixin_url) {
                    $soft_version->increase('download_num');
                    $this->response->redirect($soft_version->weixin_url);
                    return;
                } else {
                    if ($soft_version->file_url) {
                        $soft_version->increase('download_num');
                        $this->response->redirect($soft_version->file_url);
                        return;
                    }
                }

            }
        }

        $this->response->setStatusCode(404);
    }

    function visitStat()
    {

        $wap_visit_id = $this->session->get('wap_visit_id');
        if ($wap_visit_id) {
            $wap_visit = \WapVisits::findFirstById($wap_visit_id);
            if ($wap_visit) {
                $session_key = "wap_visit_uuid_{$wap_visit->id}";
                $wap_visit_uuid_val = $this->session->get($session_key);
                if ($wap_visit_uuid_val) {
                    $wap_visit_cache_key = "wap_visit_" . strval($wap_visit_uuid_val);
                    $hot_cache = \WapVisits::getHotWriteCache();
                    if (isBlank($hot_cache->get($wap_visit_cache_key))) {
                        $wap_visit->down_num += 1;
                        $wap_visit->update();
                        $hot_cache->setex($wap_visit_cache_key, 60 * 60, 1);
                        \WapVisitHistories::delay(1)->updateDownNumByWapVisit($wap_visit->id, $this->remoteIp());
                    }
                }
            }
        }

        $word_visit_id = $this->session->get('word_visit_id');
        if ($word_visit_id) {
            $word_visit = \WordVisits::findFirstById($word_visit_id);
            if ($word_visit) {
                $word_session_key = "word_visit_uuid_{$word_visit->id}";
                $word_visit_uuid_val = $this->session->get($word_session_key);
                if ($word_visit_uuid_val) {
                    $word_visit_cache_key = "word_visit_" . strval($word_visit_uuid_val);
                    $hot_cache = \WordVisits::getHotWriteCache();
                    if (isBlank($hot_cache->get($word_visit_cache_key))) {
                        $word_visit->down_num += 1;
                        $word_visit->update();
                        $hot_cache->setex($word_visit_cache_key, 60 * 60, 1);
                        \WordVisitHistories::delay(1)->updateDownNumByWordVisit($word_visit->id, $this->remoteIp());
                    }
                }
            }
        }

    }

}