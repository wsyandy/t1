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
    'job_queue' => array('endpoint' => env('job_queue_endpoint', 'redis://127.0.0.1:6379/job_queue_' . APP_NAME),
        'tubes' => array('default' => 24)),
]);