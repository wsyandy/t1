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
        'client_id' => env('emchat_client_id', 'YXA64sSGMOVkEeewwC0l6qU03Q'),
        'client_secret' => env('emchat_client_secret', 'YXA6b77RBjGYm2TzJKIpNsQdzM4WRK8'),
        'app_name' => env('emchat_app_name', 'niwoyuewan'),
        'org_name' => env('em_chat_org_name', '1109171220115678'),
        'host' => env('em_chat_host', 'https://a1.easemob.com'),
    ],

    'websocket_server' => ['host' => '0.0.0.0', 'port' => 9509],
    'websocket_end_point' => "ws://0.0.0.0:9509",
    'request_protocol' => env('request_protocol', isProduction() ? 'https' : 'http')
]);