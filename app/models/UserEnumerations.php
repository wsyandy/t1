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
        'province_id' => '省份',
        'city_id' => '城市',
        'province_name' => '省份',
        'city_name' => '城市',
        'monologue' => '个人签名',
        'interests' => '兴趣爱好',
        'height' => '身高',
        'age' => '年龄',
        'birthday' => '生日'
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
        USER_LOGIN_TYPE_SINAWEIBO => '新浪微博', USER_LOGIN_TYPE_OTHER => '其他'];

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
    //段位
    static $SEGMENT = ['starshine1' => '星耀1', 'starshine2' => '星耀2', 'starshine3' => '星耀3', 'starshine4' => '星耀4',
        'starshine5' => '星耀5', 'king1' => '王者1', 'king2' => '王者2', 'king3' => '王者3', 'king4' => '王者4', 'king5' => '王者5',
        'diamond1' => '钻石1', 'diamond2' => '钻石2', 'diamond3' => '钻石3', 'diamond4' => '钻石4', 'diamond5' => '钻石5',
        'platinum1' => '铂金1', 'platinum2' => '铂金2', 'platinum3' => '铂金3', 'platinum4' => '铂金4', 'platinum5' => '铂金5',
        'gold1' => '黄金1', 'gold2' => '黄金2', 'gold3' => '黄金3', 'gold4' => '黄金4', 'gold5' => '黄金5', 'silver1' => '白银1',
        'silver2' => '白银2', 'silver3' => '白银3', 'silver4' => '白银4', 'silver5' => '白银5', 'bronze1' => '青铜1', 'bronze2' => '青铜2',
        'bronze3' => '青铜3', 'bronze4' => '青铜4', 'bronze5' => '青铜5',
    ];
}
