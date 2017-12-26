<?php

class MarketingConfigs extends BaseModel
{
    /**
     * @type Operators
     */
    private $_operator;


    function mergeJson()
    {
        return ['operator_username' => $this->operator_username];
    }

    function authorizeToken($authorization_code)
    {

        $url = "https://developers.e.qq.com/oauth/token?";
        $body = [
            'client_id' => $this->client_id,
            'client_secret' => $this->client_secret,
            'authorization_code' => $authorization_code,
            'grant_type' => "authorization_code",
            'timestamp' => time(),
            'nonce' => randStr(20),
            'redirect_uri' => $this->redirect_uri
        ];

        $response = httpGet($url, $body);
        $result = json_decode($response->raw_body, true);
        info($this->id, $result);
        if (isset($result['code']) && $result['code'] == 0 && isset($result['data'])) {
            $refresh_token = fetch($result['data'], 'refresh_token');
            $this->refresh_token = $refresh_token;
            $refresh_token_expires_in = fetch($result['data'], 'refresh_token_expires_in');
            $this->refresh_token_expire_at = time() + $refresh_token_expires_in - 3600 * 12;

            $access_token = fetch($result['data'], 'access_token');
            $access_token_expires_in = fetch($result['data'], 'access_token_expires_in');
            $this->access_token = $access_token;
            $this->access_token_expire_at = time() + $access_token_expires_in - 3600;
            $this->save();
        }
    }

    function refreshAccessToken()
    {

        if (!$this->refresh_token) {
            return '';
        }

        $url = "https://developers.e.qq.com/oauth/token?";
        $body = array('client_id' => $this->client_id,
            'client_secret' => $this->client_secret,
            'grant_type' => "refresh_token",
            'timestamp' => time(),
            'nonce' => randStr(20),
            'refresh_token' => $this->refresh_token
        );

        $response = httpGet($url, $body);

        $result = json_decode($response->raw_body, true);
        info($this->id, $result);
        if (isset($result['code']) && $result['code'] == 0 && isset($result['data'])) {

            $access_token = fetch($result['data'], 'access_token');
            $access_token_expires_in = fetch($result['data'], 'access_token_expires_in');
            debug($result, $access_token, $access_token_expires_in);

            $this->access_token = $access_token;
            $this->access_token_expire_at = time() + $access_token_expires_in - 3600;
            $this->save();
        }

        return $this->access_token;
    }

    function getToken()
    {

        if ($this->access_token_expire_at < time()) {
            $this->refreshAccessToken();
        }

        return $this->access_token;
    }


}