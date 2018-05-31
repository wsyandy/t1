<?php

return new \Phalcon\Config([
    'alioss' => [
        'crypt_key' => env('crypt_key', md5(APP_NAME . '_' . APP_ENV)),
        'host' => env('alioss_host', 'http://oss-cn-hangzhou.aliyuncs.com'),
        'access_key' => env('alioss_access_key', 'LTAIEcaQFRJz6qai'),
        'secret_key' => env('alioss_secret_key', 'iFWLfYotm6TWpjkGBY4DICRYjTbVsm'),
        'bucket' => env('alioss_bucket', 'mt-development'),
        'domain' => env('alioss_domain', 'http://mt-development.oss-cn-hangzhou.aliyuncs.com')

    ],

    'hot_cache_endpoints' => env('hot_cache_endpoints', 'redis://127.0.0.1:6379/' . APP_NAME),
    'cache_endpoint' => env('cache_endpoint', 'redis://127.0.0.1:6379/' . APP_NAME),
    'job_queue' => [
        'endpoint' => env('job_queue_endpoint', 'redis://127.0.0.1:6379/job_queue_' . APP_NAME),
        'remote_endpoint' => env('job_queue_remote_endpoint', 'redis://127.0.0.1:6379/job_queue_' . APP_NAME),
        'tubes' => ['default' => isProduction() ? 10 : 2]],
    'user_db_endpoints' => env('user_db_endpoints', 'ssdb://127.0.0.1:8888/' . APP_NAME),
    'stat_db' => env('stat_db', 'ssdb://127.0.0.1:8888/' . APP_NAME),
    'room_db' => env('room_db', 'ssdb://127.0.0.1:8888/' . APP_NAME),
    'msg_db' => env('msg_db', 'ssdb://127.0.0.1:8888/' . APP_NAME),
    'redlock_endpoints' => env('redlock_endpoints', 'redis://127.0.0.1:6379/' . APP_NAME),

    'search_endpoints' => env('search_endpoints', 'http://127.0.0.1:9200/'),

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

    'websocket_client_endpoint' => env('websocket_client_endpoint', "ws://wstest.momoyuedu.cn"),
    'websocket_side_server_ip' => env('websocket_side_server_ip', "0.0.0.0"),
    'websocket_side_server_port' => env('websocket_side_server_port', 9509),
    'websocket_local_server_ip' => env('websocket_local_server_ip', "0.0.0.0"),
    'websocket_local_server_port' => env('websocket_local_server_port', 9508),
    'websocket_worker_num' => env('websocket_worker_num', 2),
    'websocket_max_request' => env('websocket_max_request', 10000),
    'websocket_task_worker_num' => env('websocket_task_worker_num', 1),
    'websocket_reactor_num' => env('websocket_reactor_num', 1),

    'data_collection_endpoints' => env('data_collection_endpoints', 'http://120.55.51.33:7200/hi/rest/data'),
]);