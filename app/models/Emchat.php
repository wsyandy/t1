<?php

/**
 * Created by PhpStorm.
 * User: maoluanjuan
 * Date: 03/01/2018
 * Time: 10:30
 */
class Emchat extends BaseModel
{
    static $_only_cache = true;

    private $client_id;
    private $client_secret;
    private $org_name;
    private $app_name;
    private $url;

    static function getCacheEndPoint()
    {
        $config = self::di('config');
        $endpoints = $config->cache_endpoint;
        return explode(',', $endpoints)[0];
    }

    static function createEmUser($user)
    {
        $emchat = new \Emchat();
        if (!is_a($user, 'Users')) {
            $user = \Users::findById(intval($user));
        }
        $create_result = $emchat->createUser($user->id, $user->im_password);
        if (!$create_result) {
            $user_info = $emchat->getUser($user->id);
            if (isPresent($user_info)) {
                return true;
            }
        }
        return $create_result;
    }

    /**
     * 初始化参数
     */
    public function __construct()
    {
        $config = self::di('config');
        $this->client_id = $config->emchat->client_id;
        $this->client_secret = $config->emchat->client_secret;
        $this->org_name = $config->emchat->org_name;
        $this->app_name = $config->emchat->app_name;
        $host = $config->emchat->host;
        debug("host: " . $host);
        if (preg_match('/\/$/', $config->emchat->host)) {
            $host = preg_replace('/\/$/', '', $config->enchat->host);
        }
        $this->url = $host . '/' . $this->org_name . '/' . $this->app_name . '/';
        parent::__construct();
    }


    function getTokenCacheKey()
    {
        return 'emchat_token_' . $this->org_name . '_' . $this->app_name;
    }

    /**
     * 获取管理员token
     * @return string
     */
    function getToken()
    {
        $token_cache_key = $this->token_cache_key;
        $redis = \Emchat::getXRedis(0);
        $token = $redis->get($token_cache_key);
        if (isPresent($token)) {
            return $token;
        }

        $options = [
            "grant_type" => "client_credentials",
            "client_id" => $this->client_id,
            "client_secret" => $this->client_secret
        ];
        $body = json_encode($options, JSON_UNESCAPED_UNICODE);
        $url = $this->url . 'token';
        $result = httpPost($url, $body, []);
        if ($this->reqSuccess($result->code)) {
            $token_result = $this->parseResult($result);

            $token = $token_result['access_token'];
            $expire_secs = intval($token_result['expires_in']) - 10;
            $redis->setex($token_cache_key, $expire_secs, $token);
            return $token;
        }
        return false;
    }

    /**
     * 构造统一header
     * @return array
     */
    function getHeaders()
    {
        return ["Content-Type" => "application/json", "Authorization" => "Bearer " . strval($this->token)];
    }

    /**
     * 解析返回json内容
     * @param json
     * @return array
     */
    function parseResult($result)
    {
        return json_decode($result, true);
    }

    /**
     * http请求是否成功
     * @param integer
     * @return boolean
     */
    function reqSuccess($http_code)
    {
        return 200 == $http_code;
    }

    /**
     * 授权注册
     * @param string
     * @param string
     * @return boolean
     */
    function createUser($username, $password)
    {
        $url = $this->url . 'users';
        $options = [
            "username" => $username,
            "password" => $password
        ];
        $body = json_encode($options, JSON_UNESCAPED_UNICODE);
        $header = $this->headers;
        $result = httpPost($url, $body, $header);
        info($username, $password, $result->code, $result->body);
        return $this->reqSuccess($result->code);
    }

    /*
     * 批量注册用户
     * @param array
     * @return boolean
     */
    function createUsers($users_info)
    {
        $url = $this->url . 'users';

        $body = json_encode($users_info, JSON_UNESCAPED_UNICODE);
        $header = $this->headers;
        $result = httpPost($url, $body, $header);
        //debug(var_dump($result));
        debug($result->code);
        return $this->reqSuccess($result->code);
    }

    /*
     * 重置用户密码
     * @param string
     * @param string
     * @param string
     * @return boolean
     */
    function resetPassword($username, $old_password, $new_password)
    {
        $url = $this->url . 'users/' . $username . '/password';
        $options = [
            "oldpassword" => $old_password,
            "newpassword" => $new_password
        ];
        $body = json_encode($options, JSON_UNESCAPED_UNICODE);
        $header = $this->headers;
        $result = httpPost($url, $body, $header);
        //var_dump($result);
        return $this->reqSuccess($result->code);
    }

    /*
     * 获取单个用户详情
     * @param string
     * @return array
     */
    function getUser($username)
    {
        $url = $this->url . 'users/' . $username;
        $header = $this->headers;
        var_dump($header);
        $result = httpGet($url, null, $header);
        info($result->body, $result->code, $username);
        if ($this->reqSuccess($result->code)) {
            $result_data = $this->parseResult($result);
            return $result_data['entities'];
        }
        return [];
    }

    /*
     * 批量获取用户
     * @param integer
     * @param integer
     * @return array
     */
    function getUsers($page = 1, $per_page = 10)
    {
        $limit = $per_page;
        $cursor = ($page - 1) * $per_page;

        $url = $this->url . 'users?limit=' . $limit . '&cursor=' . $cursor;
        $header = $this->headers;
        $result = httpGet($url, null, $header);
        if ($this->reqSuccess($result->code)) {
            $result_data = $this->parseResult($result);
            return $result_data['entities'];
        }
        return [];
    }

    /*
     * 删除单个用户
     * @param string
     * @return boolean
     */
    function deleteUser($username)
    {
        $url = $this->url . 'users/' . $username;
        $header = $this->headers;

        $result = httpDelete($url, null, $header);
        return $this->reqSuccess($result->code);
    }

    /*
     * 删除批量用户
     * limit:建议在100-500之间，、
     * 注：具体删除哪些并没有指定, 可以在返回值中查看。
     * @param integer
     * @return array
     */
    function deleteUsers($limit)
    {
        $url = $this->url . 'users?limit=' . $limit;
        $header = $this->headers;

        $result = httpDelete($url, null, $header);
        $users = [];
        if ($this->reqSuccess($result->code)) {
            $result_data = $this->parseResult($result);
            foreach ($result_data['entites'] as $entity) {
                $users[] = $entity['username'];
            }
        }
        return $users;
    }

    /*
     * 查看用户是否在线
     * @param string
     * @return boolean
     */
    function isOnline($username)
    {
        $url = $this->url . 'users/' . $username . '/status';
        $header = $this->headers;

        $result = httpGet($url, null, $header);
        //var_dump($result);
        if ($this->reqSuccess($result->code)) {
            $hash = $this->parseResult($result);
            $online_data = fetch($hash, 'data');
            return $online_data[$username] == 'online';
        }
        return false;
    }

    /*
     * 查看用户离线消息数
     * @param string
     * @return integer
     */
    function getOfflineMessages($username)
    {
        $url = $this->url . 'users/' . $username . '/offline_msg_count';
        $header = $this->headers;

        $result = httpGet($url, null, $header);
        var_dump($result);
        if ($this->reqSuccess($result->code)) {
            $result_data = $this->parseResult($result);
            return intval(fetch($result_data['data'], $username));
        }
        return 0;
    }

    /*
     * 查看某条消息的离线状态
     * deliverd 表示此用户的该条离线消息已经收到
     * @param $username string
     * @param $msg_id string
     * @return string
     */
    function getOfflineMessageStatus($username, $msg_id)
    {
        $url = $this->url . 'users/' . $username . '/offline_msg_status/' . $msg_id;
        $header = $this->headers;

        $result = httpGet($url, null, $header);
        if ($this->reqSuccess($result->code)) {
            $hash = $this->parseResult($result);
            return $hash['data'][$msg_id];
        }
        return false;
    }

    /**
     * 离线消息是否已经收到
     * @param $username string
     * @param $msg_id string
     * @return bool
     */
    function offlineMessageReceived($username, $msg_id)
    {
        $message_status = $this->getOfflineMessageStatus($username, $msg_id);
        return $message_status == 'deliverd';
    }

    /**
     * 禁用用户账号
     * @param string $username
     * @return boolean
     */
    function deactiveUser($username)
    {
        $url = $this->url . 'users/' . $username . '/deactivate';
        $header = $this->headers;

        $result = httpPost($url, null, $header);
        //var_dump($result);
        //var_dump($result->code);
        return $this->reqSuccess($result->code);
    }

    /**
     * 解禁用户账号
     * @param string $username
     * @return boolean
     */
    function activeUser($username)
    {
        $url = $this->url . 'users/' . $username . '/activate';
        $header = $this->headers;
        $result = httpPost($url, null, $header);

        return $this->reqSuccess($result->code);
    }

    /**
     * 强制用户下线
     * @param string $username
     * @return boolean
     */
    function disconnectUser($username)
    {
        $url = $this->url . 'users/' . $username . '/disconnect';
        $header = $this->headers;
        $result = httpGet($url, null, $header);
        return $this->reqSuccess($result->code);
    }

    /**
     * 添加好友
     * @param string $username
     * @param string $friend_name
     * @return boolean
     */
    function addFriend($username, $friend_name)
    {
        $url = $this->url . 'users/' . $username . '/contacts/users/' . $friend_name;

        $header = $this->headers;
        $result = httpPost($url, null, $header);
        return $this->reqSuccess($result->code);
    }

    /**
     * 删除好友
     * @param string $username
     * @param string $friend_name
     * @return boolean
     */
    function deleteFriend($username, $friend_name)
    {
        $url = $this->url . 'users/' . $username . '/contacts/users/' . $friend_name;
        $header = $this->headers;

        $result = httpDelete($url, '', $header);
        var_dump($result);
        return $this->reqSuccess($result->code);
    }

    /**
     * 查看好友列表
     * @param string $username
     * @return array
     */
    function getFriends($username)
    {
        $url = $this->url . 'users/' . $username . '/contacts/users';
        $header = $this->headers;
        $result = httpGet($url, null, $header);
        var_dump($result);
        if ($this->reqSuccess($result->code)) {
            $result_data = $this->parseResult($result);
            return $result_data['data'];
        }
        return null;
    }

    /**
     * 查看用户黑名单列表
     * @param string $username
     * @return array
     */
    function getBlacks($username)
    {
        $url = $this->url . 'users/' . $username . '/blocks/users';
        $header = $this->headers;

        $result = httpGet($url, null, $header);
        var_dump($result);
        if ($this->reqSuccess($result->code)) {
            $result_data = $this->parseResult($result);
            return $result_data['data'];
        };
        return null;
    }

    /**
     * @param $username
     * @param $black
     * @return bool
     */
    function addBlack($username, $black)
    {
        return $this->addBlacks($username, [$black]);
    }

    /**
     * 往黑名单中加人
     * @param string $username
     * @param array $blacks
     * @return boolean
     */
    function addBlacks($username, $blacks)
    {
        $url = $this->url . 'users/' . $username . '/blocks/users';
        $body = json_encode(["usernames" => $blacks], JSON_UNESCAPED_UNICODE);
        $header = $this->headers;

        $result = httpPost($url, $body, $header);
        var_dump($result);
        var_dump($result->code);
        return $this->reqSuccess($result->code);
    }

    /**
     * 从黑名单中减人
     * @param string $username
     * @param string $black
     * @return boolean
     */
    function deleteBlack($username, $black)
    {
        $url = $this->url . 'users/' . $username . '/blocks/users/' . $black;
        $header = $this->headers;

        $result = httpDelete($url, null, $header);
        return $this->reqSuccess($result->code);
    }


    //--------------------------------------------------------发送消息

    function generateSendContext($from, $target, $content_type, $content,
                                 $target_type = 'users', $ext = null)
    {
        $body = [];
        $body['target_type'] = $target_type;
        if (is_array($target)) {
            $body['target'] = $target;
        } else {
            $body['target'] = [$target];
        }
        $options = [];
        $options['type'] = $content_type;
        if ($content_type == 'txt') {
            $options['msg'] = $content;
        }
        if ($content_type == 'cmd') {
            $options['action'] = $content;
        }
        if (preg_match('/img|audio|video|file|/', $content_type) && is_array($content)) {
            $options = array_merge($options, $content);
        }
        $body['msg'] = $options;
        $body['from'] = $from;
        if (isPresent($ext)) {
            $body['ext'] = $ext;
        }
        return json_encode($body, JSON_UNESCAPED_UNICODE);
    }

    /**
     * 解析发送结果
     * @param string $result
     * @param string $target
     * @return array|mixed|null
     */
    function sendResult($result, $target)
    {
        $send_results = [];
        if ($this->reqSuccess($result->code)) {
            $result_data = $this->parseResult($result);
            info($result_data);
            $datas = $result_data['data'];
            foreach ($datas as $key => $value) {
                $send_results[$key] = $value == 'success';
            }
        }
        if (!is_array($target)) {
            return fetch($send_results, $target);
        }
        return $send_results;
    }

    /**
     * @param $result
     * @param $filename
     * @param null $length
     * @return array
     */
    function generateFileContent($result, $filename, $length = null)
    {
        $uri = $result['uri'];
        $uuid = $result['entities'][0]['uuid'];
        $shareSecret = $result['entities'][0]['share-secret'];
        $content = [];
        $content['url'] = $uri . '/' . $uuid;
        $content['filename'] = $filename;
        if (isPresent($length)) {
            $content['length'] = $length;
        }
        $content['secret'] = $shareSecret;
        return $content;
    }

    /**
     * 发送文本消息
     * @param string $from
     * @param string $target_type
     * @param string $target
     * @param string $content
     * @param string $ext
     * @return boolean || array
     */
    function sendText($from, $target, $content, $target_type = 'users', $ext = null)
    {
        $url = $this->url . 'messages';
        $content_type = 'txt';
        $body = $this->generateSendContext($from, $target, $content_type, $content, $target_type, $ext);
        $header = $this->headers;

        $result = httpPost($url, $body, $header);
        return $this->sendResult($result, $target);
    }

    /*
        发送透传消息
    */
    function sendCmd($from, $target, $action, $target_type = 'users', $ext = null)
    {
        $url = $this->url . 'messages';
        $content_type = 'cmd';
        $body = $this->generateSendContext($from, $target, $content_type, $action, $target_type, $ext);

        $header = $this->headers;
        $result = httpPost($url, $body, $header);
        return $this->sendResult($result, $target);
    }

    /**
     * @param $filePath
     * @param $from
     * @param $target
     * @param $target_type
     * @param $filename
     * @param $ext
     * @return \Httpful\Response
     */
    function sendImage($file_path, $from, $target, $filename, $target_type = 'users', $ext = null)
    {
        $result = $this->uploadFile($file_path);
        $url = $this->url . 'messages';
        $content_type = 'img';
        $content = $this->generateFileContent($result, $filename);
        $content['size'] = [
            "width" => 480,
            "height" => 720
        ];
        $body = $this->generateSendContext($from, $target, $content_type, $content, $target_type, $ext);

        $header = $this->headers;
        $result = httpPost($url, $body, $header);
        return $this->sendResult($result, $target);
    }

    /**
     * @param $filePath
     * @param string $from
     * @param $target_type
     * @param $target
     * @param $filename
     * @param $length
     * @param $ext
     * @return \Httpful\Response
     */
    function sendAudio($file_path, $from, $target, $filename, $length, $target_type = 'users', $ext = null)
    {
        $result = $this->uploadFile($file_path);

        $content = $this->generateFileContent($result, $filename, $length);
        $content_type = 'audio';

        $url = $this->url . 'messages';
        $body = $this->generateSendContext($from, $target, $content_type, $content, $target_type, $ext);
        $header = $this->headers;

        $result = httpPost($url, $body, $header);
        return $this->sendResult($result, $target);
    }

    /**
     * @param $filePath
     * @param string $from
     * @param $target_type
     * @param $target
     * @param $filename
     * @param $length
     * @param $thumb_image_path
     * @param $thumb
     * @param $thumb_secret
     * @param $ext
     * @return \Httpful\Response
     */
    function sendVideo($file_path, $from, $target, $filename, $length, $thumb_image_path = null, $target_type = 'users', $ext = null)
    {
        $result = $this->uploadFile($file_path);
        $content = $this->generateFileContent($result, $filename, $length);
        $content['msg']['file_length'] = filesize($file_path);
        if (isPresent($thumb_image_path)) {
            $thumb_content = $this->uploadThumbImage($thumb_image_path);
            $content['msg'] = array_merge($content['msg'], $thumb_content);
        }
        $content_type = 'video';
        $url = $this->url . 'messages';
        $body = $this->generateSendContext($from, $target, $content_type, $content, $target_type, $ext);

        $header = $this->headers;
        $result = httpPost($url, $body, $header);
        var_dump($result);
        return $this->sendResult($result, $target);
    }

    /**
     * @param $filePath
     * @param $from
     * @param $target
     * @param $filename
     * @param $length
     * @param string $target_type
     * @param null $ext
     * @return array|mixed|null
     */
    function sendFile($file_path, $from, $target, $filename, $length, $target_type = 'users', $ext = null)
    {
        $result = $this->uploadFile($file_path);

        $url = $this->url . 'messages';
        $content = $this->generateFileContent($result, $filename, $length);
        $content_type = 'file';

        $body = $this->generateSendContext($from, $target, $content_type, $content, $target_type, $ext);

        $header = $this->headers;
        $result = httpPost($url, $body, $header);
        return $this->sendResult($result, $target);
    }

    function uploadThumbImage($thumb_image_path)
    {
        $content = [];
        $result = $this->uploadFile($thumb_image_path);
        $uri = $result['uri'];
        $uuid = $result['entities'][0]['uuid'];
        $shareSecret = $result['entities'][0]['share-secret'];
        $content['thumb'] = $uri . '/' . $uuid;
        $content['thumb_secret'] = $shareSecret;

        return $content;
    }

    /**
     * @param $file_path string
     * @return array
     */
    function uploadFile($file_path)
    {
        $url = $this->url . 'chatfiles';
        $files = ['file' => $file_path];
        $header = $this->headers;
        $header['restrict-access'] = 'true';
        $header['Content-Type'] = 'multipart/form-data';

        $result = httpPost($url, [], $header, $files, 'json');

        debug($result->code);
        return $this->parseResult($result);
    }
}