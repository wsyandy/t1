<?php

class XcxBaseEvents
{

    static $ERROR_TYPE = [XCX_OK => 0, XCX_ILLEGAL_AES_KEY => -41001,
        XCX_ILLEGAL_IV => -41002, XCX_ILLEGAL_BUFFER => -41003, XCX_DECODE_BASE64_ERROR => -41004];

    public $product_channel;
    private $grant_type = 'authorization_code';
    private $session_datas;


    function __construct($product_channel)
    {
        $this->product_channel = $product_channel;
    }

    /**
     * 检验数据的真实性，并且获取解密后的明文.
     * @param $encrypted_data string 加密的用户数据
     * @param $iv string 与用户数据一同返回的初始向量
     * @param $data string 解密后的原文
     *
     * @return int 成功0，失败返回对应的错误码
     */
    public function decryptData($opt, &$data)
    {
        $session_key = fetch($opt, 'session_key');
        $iv = fetch($opt, 'iv');
        $encrypted_data = fetch($opt, 'encrypted_data');
        if (strlen($session_key) != 24) {
            return XCX_ILLEGAL_AES_KEY;
        }
        $aesKey = base64_decode($session_key);


        if (strlen($iv) != 24) {
            return XCX_ILLEGAL_IV;
        }
        $aesIV = base64_decode($iv);

        $aesCipher = base64_decode($encrypted_data);

        $result = openssl_decrypt($aesCipher, "AES-128-CBC", $aesKey, 1, $aesIV);

        $dataObj = json_decode($result);
        if ($dataObj == NULL) {
            return XCX_ILLEGAL_BUFFER;
        }
        if ($dataObj->watermark->appid != $this->product_channel->xcx_appid) {
            return XCX_ILLEGAL_BUFFER;
        }
        $data = $result;
        return XCX_OK;
    }

    function getOpenid($code)
    {
        if (!$code) {
            return null;
        }

        $data = $this->getSessionData($code);

        if (!$data) {
            return null;
        }

        debug($data);

        $openid = fetch($data, 'openid');

        return $openid;
    }

    function getSessionKey($code)
    {
        if (!$code) {
            return null;
        }

        $data = $this->getSessionData($code);

        if (!$data) {
            return null;
        }

        debug($data);

        $session_key = fetch($data, 'session_key');

        return $session_key;
    }


    function getSessionData($code)
    {
        if ($this->session_datas) {
            return $this->session_datas;
        }
        $body = ['appid' => $this->product_channel->xcx_appid, 'secret' => $this->product_channel->xcx_secret,
            'grant_type' => $this->grant_type, 'js_code' => $code];
        $url = 'https://api.weixin.qq.com/sns/jscode2session';
        try {
            $res = httpGet($url, $body);
        } catch (Exception $e) {
            warn("Exec false, Exception:", $e->getMessage());
            return null;
        }

        $this->session_datas = json_decode($res->body, true);

        return $this->session_datas;
    }

    function getUserInfo($code, $data)
    {
        $session_key = $this->getSessionKey($code);
        $encrypted_data = fetch($data, 'encryptedData');
        $iv = fetch($data, 'iv');
        if ($encrypted_data && $iv) {
            $opt = ['encrypted_data' => $encrypted_data, 'iv' => $iv, 'session_key' => $session_key];
            info('解密用户信息参数', $opt);
            $error_code = $this->decryptData($opt, $user_info);
            return [$user_info, $error_code];
        }
    }

}