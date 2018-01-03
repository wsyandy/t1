<?php

define('OPERATOR_STATUS_NORMAL', 1);
define('OPERATOR_STATUS_BLOCKED', 2);

// 是否有效
define('STATUS_FORBIDDEN', -1);   //禁用
define('STATUS_OFF', 0);   //无效
define('STATUS_ON', 1);    //有效
define('STATUS_PROGRESS', 3); // 进行中
define('STATUS_BLOCKED', 4); // 微信被封

//有无状态
define('STATUS_NO_LIMIT', 0); // 不限
define('STATUS_YES', 1); // 有
define('STATUS_NO', 2); // 无

define('SEND_STATUS_STOP', -1); // 终止
define('SEND_STATUS_WAIT', 0);   //等待
define('SEND_STATUS_SUCCESS', 1);  //成功
define('SEND_STATUS_SUBMIT', 2); // 已提交
define('SEND_STATUS_PROGRESS', 3); // 进行中

define('USER_PLATFORM_ANDROID', 'android');
define('USER_PLATFORM_IOS', 'ios');
define('USER_PLATFORM_WEIXIN_IOS', 'weixin_ios');
define('USER_PLATFORM_WEIXIN_ANDROID', 'weixin_android');
define('USER_PLATFORM_TOUCH_IOS', 'touch_ios');
define('USER_PLATFORM_TOUCH_ANDROID', 'touch_android');

define('PARTNER_GROUP_TYPE_NO', 0);// 默认

// 用户状态
define("USER_STATUS_OFF", 0);              // 注销用户
define("USER_STATUS_ON", 1);               // 正常状态
define("USER_STATUS_BLOCKED_ACCOUNT", 2);  // 封账号
define("USER_STATUS_BLOCKED_DEVICE", 3);   // 封设备
define("USER_STATUS_LOGOUT", 4);       # 已退出

// 用户类型
define('USER_TYPE_ACTIVE', 1);//正常用户
define('USER_TYPE_SILENT', 2);//沉默
define('USER_TYPE_TEST', 3);//测试

// 用户性别
define('USER_SEX_MALE', 1);
define('USER_SEX_FEMALE', 0);
define('USER_SEX_COMMON', 2); //不限
define('USER_SEX_UNKNOWN', -1);

define('SYSTEM_ID', 1); // 系统用户
define('USER_SUBSCRIBE', 1); // 关注
define('USER_UNSUBSCRIBE', 0); // 取消关注

#第三方登陆
define('THIRD_AUTH_THIRD_NAME_WEIXIN', 'weixin');//微信登陆
define('THIRD_AUTH_THIRD_NAME_QQ', 'qq');//微信登陆

//指定平台禁用该客户端主题
define('VERSION_CODE_FORBIDDEN', -1);

// 用户登录类型
define('USER_LOGIN_TYPE_MOBILE', 'mobile');//手机登陆类型
define('USER_LOGIN_TYPE_WEIXIN', 'weixin');//微信登陆类型
define('USER_LOGIN_TYPE_QQ', 'qq');//QQ登陆类型
define('USER_LOGIN_TYPE_OTHER', 'other');//其他登陆类型

// 设备状态
define('DEVICE_STATUS_NORMAL', 0);
define('DEVICE_STATUS_BLOCK', 1);
define('DEVICE_STATUS_WHITE', 2);

define('PARTNER_STATUS_NORMAL', 1);
define('PARTNER_STATUS_BLOCK', 2);

// 审核
define('VERIFY_WAIT', 0); // 等待校验
define('VERIFY_SUCCESS', 1); // 校验成功
define('VERIFY_FAIL', -1); // 校验失败

// 短信状态
define('SMS_HISTORY_SEND_STATUS_WAIT', 0);
define('SMS_HISTORY_SEND_STATUS_SUCCESS', 1);
define('SMS_HISTORY_SEND_STATUS_FAIL', 2);
define('SMS_HISTORY_AUTH_STATUS_WAIT', 0);
define('SMS_HISTORY_AUTH_STATUS_SUCCESS', 1);

// 软件状态
define('SOFT_VERSION_STATUS_ON', 1);//可用
define('SOFT_VERSION_STATUS_OFF', 0);//可用

// 软件是否强制升级
define('SOFT_VERSION_FORCE_UPDATE_ON', 1);//强制升级
define('SOFT_VERSION_FORCE_UPDATE_OFF', 0);//不强制升级

// 是否稳定版本
define('SOFT_VERSION_STABLE_ON', 1);//稳定
define('SOFT_VERSION_STABLE_OFF', 0);//不稳定

// 统计分类
define('STAT_HOUR', 1);
define('STAT_DAY', 2);
define('STAT_MONTH', 3);

// 运营商
define('MOBILE_OPERATOR_ALL', 0);
define('MOBILE_OPERATOR_CMCC', 1);
define('MOBILE_OPERATOR_UNICOM', 2);
define('MOBILE_OPERATOR_TELECOM', 3);

define('ERROR_CODE_BOX', -1001); //需要弹框
