<?php

/**
 * Created by PhpStorm.
 * User: apple
 * Date: 18/1/31
 * Time: 下午11:07
 */

class AccessTokens extends BaseModel
{
    /**
     * @type Users
     */
    private $_user;

    static $STATUS = [AUTH_SUCCESS => '成功', AUTH_WAIT => '等待验证'];

    function beforeCreate()
    {
        $this->expired_at = time() + 60 * 10;
        $this->status = AUTH_WAIT;
    }

    static function generateToken()
    {
        $access_token = new \AccessTokens();
        $access_token->expired_at = time() + 60 * 10;
        $access_token->save();

        return $access_token->token;
    }

    //用来检测token是否登录成功
    static function checkToken($token)
    {
        $access_token = \AccessTokens::findFirstByToken($token);
        debug($token, $access_token);
        if ($access_token && AUTH_SUCCESS == $access_token->status &&
            time() < $access_token->expired_at
        ) {
            return $access_token;
        }
        return false;
    }

    //客户端验证token合法性
    static function validToken($token)
    {
        $access_token = \AccessTokens::findFirstByToken($token);
        debug($token, $access_token);
        if ($access_token && $access_token->token == $token && time() < $access_token->expired_at) {
            return $access_token;
        }

        return false;
    }

    function getToken()
    {
        return substr($this->id . 'ac' . md5($this->id . '_' . $this->expired_at), 0, 32);
    }

    static function findFirstByToken($token)
    {
        $id = intval($token);
        return self::findFirstById($id);
    }

    static function deleteExpired()
    {
        $hot_cache = self::getHotWriteCache();
        $key = 'access_token_delete_expired';
        if ($hot_cache->get($key)) {
            return;
        }

        $hot_cache->setex($key, 1800, 1);

        $access_tokens = self::findForeach(['conditions' => 'expired_at<:expired_at: and status=:status:',
            'bind' => ['expired_at' => time(), 'status' => AUTH_WAIT]]);

        foreach ($access_tokens as $access_token) {
            $access_token->delete();
        }
    }

}