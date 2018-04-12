<?php

/**
 * Created by PhpStorm.
 * User: apple
 * Date: 2017/12/26
 * Time: 下午3:21
 */
trait UserEnumerations
{
    static $UPDATE_FIELDS = [
        'nickname' => '昵称',
        'avatar' => '头像',
        'ip' => 'ip',
        'sex' => '性别',
        'country_id' => '国家',
        'province_id' => '省份',
        'city_id' => '城市',
        'province_name' => '省份',
        'city_name' => '城市',
        'monologue' => '个人签名',
        'interests' => '兴趣爱好',
        'height' => '身高',
        'age' => '年龄',
        'birthday' => '生日',
        'lang' => '语言'
    ];

    // 用户状态
    static $USER_STATUS = [USER_STATUS_OFF => '注销', USER_STATUS_ON => '正常', USER_STATUS_BLOCKED_ACCOUNT => '封账号',
        USER_STATUS_BLOCKED_DEVICE => '封设备', USER_STATUS_LOGOUT => '已退出'];

    // 用户类型
    static $USER_TYPE = [USER_TYPE_ACTIVE => '活跃', USER_TYPE_SILENT => '沉默', USER_TYPE_TEST => '测试'];

    static $SEX = [USER_SEX_MALE => '男', USER_SEX_FEMALE => '女'];

    static $PLATFORM = [USER_PLATFORM_IOS => '苹果客户端', USER_PLATFORM_ANDROID => '安卓客户端',
        USER_PLATFORM_WEIXIN_IOS => '微信苹果端', USER_PLATFORM_WEIXIN_ANDROID => '微信安卓端'];

    static $LOGIN_TYPE = [USER_LOGIN_TYPE_MOBILE => '手机', USER_LOGIN_TYPE_WEIXIN => '微信', USER_LOGIN_TYPE_QQ => 'QQ',
        USER_LOGIN_TYPE_SINAWEIBO => '新浪微博', USER_LOGIN_TYPE_EMAIL => '邮箱登录', USER_LOGIN_TYPE_OTHER => '其他'];

    static $PROVINCE = [1 => "北京", 2 => "上海", 3 => "天津", 4 => "重庆",
        5 => "河北", 6 => "山西", 7 => "河南", 8 => "辽宁",
        9 => "吉林", 10 => "黑龙江", 11 => "内蒙古", 12 => "江苏",
        13 => "山东", 14 => "安徽", 15 => "浙江", 16 => "福建",
        17 => "湖北", 18 => "湖南", 19 => "广东", 20 => "广西",
        21 => "江西", 22 => "四川", 23 => "海南", 24 => "贵州",
        25 => "云南", 26 => "西藏", 27 => "陕西", 28 => "甘肃",
        29 => "青海", 30 => "宁夏", 31 => "新疆", 32 => "台湾",
        33 => "香港", 34 => "澳门"];

    static $FRIEND_STATUS = [
        1 => '已添加', 2 => '等待验证', 3 => '接受'
    ];

    //星座
    static $CONSTELLATION = [
        1 => '白羊', 2 => '金牛', 3 => '双子', 4 => '巨蟹',
        5 => '狮子', 6 => '处女', 7 => '天秤', 8 => '天蝎',
        9 => '射手', 10 => '摩羯', 11 => '水瓶', 12 => '双鱼'
    ];

    static $USER_ROLE = [0 => '无角色', 1 => '房主', 2 => '主播', 3 => '旁听'];
    static $AVATAR_STATUS = [AUTH_NONE => '等待上传', AUTH_SUCCESS => '审核成功', AUTH_FAIL => '审核失败', AUTH_WAIT => '等待审核'];
    //段位 Ⅰ   Ⅱ  Ⅲ  Ⅳ Ⅴ 
    static $SEGMENT = ['starshine1' => '星耀Ⅰ', 'starshine2' => '星耀Ⅱ', 'starshine3' => '星耀Ⅲ', 'starshine4' => '星耀Ⅳ',
        'starshine5' => '星耀Ⅴ', 'king1' => '王者Ⅰ', 'king2' => '王者Ⅱ', 'king3' => '王者Ⅲ', 'king4' => '王者Ⅳ', 'king5' => '王者Ⅴ',
        'diamond1' => '钻石Ⅰ', 'diamond2' => '钻石Ⅱ', 'diamond3' => '钻石Ⅲ', 'diamond4' => '钻石Ⅳ', 'diamond5' => '钻石Ⅴ',
        'platinum1' => '铂金Ⅰ', 'platinum2' => '铂金Ⅱ', 'platinum3' => '铂金Ⅲ', 'platinum4' => '铂金Ⅳ', 'platinum5' => '铂金Ⅴ',
        'gold1' => '黄金Ⅰ', 'gold2' => '黄金Ⅱ', 'gold3' => '黄金Ⅲ', 'gold4' => '黄金Ⅳ', 'gold5' => '黄金Ⅴ', 'silver1' => '白银Ⅰ',
        'silver2' => '白银Ⅱ', 'silver3' => '白银Ⅲ', 'silver4' => '白银Ⅳ', 'silver5' => '白银Ⅴ', 'bronze1' => '青铜Ⅰ', 'bronze2' => '青铜Ⅱ',
        'bronze3' => '青铜Ⅲ', 'bronze4' => '青铜Ⅳ', 'bronze5' => '青铜Ⅴ',
    ];

    static $ID_CARD_AUTH = [AUTH_NONE => '', AUTH_SUCCESS => '已认证', AUTH_FAIL => '未通过', AUTH_WAIT => '认证中'];
    static $ORGANISATION = [USER_ORGANISATION_PERSONAGE => '个人', USER_ORGANISATION_COMPANY => '公司'];
    static $USER_TEST = [USER_TEST_BUG => '测试swoole'];
    static $LANG = [LANG_ZH => '简体中文', LANG_ZH_TW => '繁体中文', LANG_EN => '英语', LANG_VI => '越南语',
        LANG_RU => '俄语', LANG_PT => '葡萄牙语', LANG_KO => '韩语', LANG_JA => '日语', LANG_FR => '法语', LANG_ES => '西班牙语',
        LANG_DE => '德语', LANG_AR => '阿拉伯语'
    ];
}
