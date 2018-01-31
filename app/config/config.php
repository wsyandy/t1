<?php

return new \Phalcon\Config([
    'alioss' => [
        'crypt_key' => env('crypt_key', md5(APP_NAME . '_' . APP_ENV)),
        'host' => env('alioss_host', 'http://oss-cn-hangzhou.aliyuncs.com'),
        'access_key' => env('alioss_access_key', 'qCIEyToynSzy3Zw7'),
        'secret_key' => env('alioss_secret_key', 'nm1pIUeSRyk8llxbMx5FaGWLQ9E1iF'),
        'bucket' => env('alioss_bucket', 'yiyuan-development'),
        'domain' => env('alioss_domain', 'yiyuan-development.img-cn-hangzhou.aliyuncs.com')
    ],

    'hot_cache_endpoints' => env('hot_cache_endpoints', 'redis://127.0.0.1:6379/' . APP_NAME),
    'cache_endpoint' => env('cache_endpoint', 'redis://127.0.0.1:6379/' . APP_NAME),
    'job_queue' => ['endpoint' => env('job_queue_endpoint', 'redis://127.0.0.1:6379/job_queue_' . APP_NAME),
        'tubes' => ['default' => isProduction() ? 16 : 3]],
    'user_db_endpoints' => env('user_db_endpoints', 'ssdb://127.0.0.1:8888/' . APP_NAME),
    'stat_db' => env('stat_db', 'ssdb://127.0.0.1:8888/' . APP_NAME),
    'redlock_endpoints' => env('redlock_endpoints', 'redis://127.0.0.1:6379/' . APP_NAME),

    'emchat' => [
        'client_id' => env('emchat_client_id', 'YXA60kgNMPEEEeenxnECtCKVLw'),
        'client_secret' => env('emchat_client_secret', 'YXA6tyk2jtQevpgBdPhzJnhD2Ifu0Q0'),
        'app_name' => env('emchat_app_name', 'yuewantest'),
        'org_name' => env('emchat_org_name', '1134180104115441'),
        'host' => env('emchat_host', 'https://a1.easemob.com'),
    ],
    'agora_app_id' => env('agora_app_id', 'ed397936850c4dc9afd8be6d66109e9e'),
    'agora_app_certificate' => env('agora_app_certificate', '773c59982b3e4a5a968efbe0c9b15c5c'),
    'request_protocol' => env('request_protocol', isProduction() ? 'https' : 'http'),

    'websocket_client_endpoint' => env('websocket_client_endpoint', "ws://wstest.yueyuewo.cn"),
    'websocket_listen_client_ip' => env('websocket_listen_client_ip', "0.0.0.0"),
    'websocket_listen_client_port' => env('websocket_listen_client_port', 9509),
    'websocket_listen_server_ip' => env('websocket_listen_server_ip', "0.0.0.0"),
    'websocket_listen_server_port' => env('websocket_listen_server_port', 9508),
    'websocket_worker_num' => env('websocket_worker_num', 4),
    'websocket_max_request' => env('websocket_max_request', 100000),
]);