<?php

define('OPERATOR_STATUS_NORMAL', 1);
define('OPERATOR_STATUS_BLOCKED', 2);

// 是否有效
define('STATUS_FORBIDDEN', -1);   //禁用
define('STATUS_OFF', 0);   //无效
define('STATUS_ON', 1);    //有效
define('STATUS_PROGRESS', 3); // 进行中
define('STATUS_BLOCKED', 4); // 被封

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
define('AUTH_NONE', 0);
define('AUTH_SUCCESS', 1); // 校验成功
define('AUTH_FAIL', 2); // 校验失败
define('AUTH_WAIT', 3); // 等待校验
define('AUTH_EXPIRE', 4);

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

// 用户角色
define('USER_ROLE_NO', 0); // 无角色 不在房间
define('USER_ROLE_HOST_BROADCASTER', 5); // 房主 在自己房间
define('USER_ROLE_MANAGER', 10); // 管理员
define('USER_ROLE_BROADCASTER', 15); // 主播 在他人房间上麦
define('USER_ROLE_AUDIENCE', 20); // 旁听 在他人房间旁听

//礼物
define('GIFT_STATUS_ON', 1); //有效
define('GIFT_STATUS_OFF', 0); //无效

//钻石消费
define('ACCOUNT_TYPE_BUY_DIAMOND', 1); //购买钻石
define('ACCOUNT_TYPE_BUY_GIFT', 2); //购买礼物
define('ACCOUNT_TYPE_GIVE', 3); //系统赠送

define('ERROR_CODE_NEED_PAY', -2);//需要付费

define('GIFT_ORDER_STATUS_WAIT', 0); //等待支付
define('GIFT_ORDER_STATUS_SUCCESS', 1); //支付成功
define('GIFT_ORDER_STATUS_FAIL', 2); //支付失败

define('PRODUCT_GROUP_FEE_TYPE_DIAMOND', 'diamond'); //钻石

define('ORDER_STATUS_WAIT', 0);
define('ORDER_STATUS_SUCCESS', 1);
define('ORDER_STATUS_FAIL', 2);

define('PAYMENT_PAY_STATUS_SUCCESS', 1);
define('PAYMENT_PAY_STATUS_WAIT', 0);
define('PAYMENT_PAY_STATUS_FAIL', 2);
define('PAYMENT_PAY_STATUS_FAILED', 2);

//通话状态
define('CALL_STATUS_WAIT', 0);//等待接听
define('CALL_STATUS_NO_ANSWER', 1);//无应答
define('CALL_STATUS_BUSY', 2);//对方正忙
define('CALL_STATUS_REFUSE', 3);//拒绝
define('CALL_STATUS_CANCEL', 4);//取消
define('CALL_STATUS_HANG_UP', 5);//挂断
define('CALL_STATUS_ANSWERED', 6);//接听

define('CHAT_CONTENT_TYPE_TEXT', 'text/plain');

define('MAX_OFFLINE_TASK_HANG_UP_TIME', 48 * 60 * 60);

// 房间类型
define('ROOM_THEME_TYPE_NORMAL',0); //正常
define('ROOM_THEME_TYPE_BROADCAST', 1);//电台

define('AUDIO_TYPE_STORY',1); //故事
define('AUDIO_TYPE_MUSIC',2); //音乐

define('WITHDRAW_STATUS_WAIT',0); //等待提现
define('WITHDRAW_STATUS_SUCCESS',1); //提现成功
define('WITHDRAW_STATUS_FAIL',2); //提现失败