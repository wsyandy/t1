<?php

class PartnerUrls extends BaseModel
{
    /**
     * @type Operators
     */
    private $_operator;

    static $PLATFORM = ['android' => '安卓', 'ios' => 'ios'];

    static $TYPE = ['gdt' => '广点通', 'toutiao' => '头条', 'uc' => '阿里汇川UC', 'wx_gdt' => '微信广告', 'momo' => '陌陌', 'baidu' => '百度', 'sina' => '新浪'];

    static $PARTNER_PARMS = [
        'android_toutiao' => '/sources/active?code=%s&imei=__IMEI__&ip=__IP__&click_time=__TS__&os=__OS__&callback=__CALLBACK_URL__',
        'ios_toutiao' => '/sources/active?code=%s&fr=%s&idfa=__IDFA__&ip=__IP__&click_time=__TS__&os=__OS__&callback=__CALLBACK_URL__',
        'android_gdt' => '/sources/gdt_click?code=%s',
        'ios_gdt' => '/sources/gdt_click?code=%s&fr=%s',
        'android_wx_gdt' => '/sources/gdt_click?source=wx_gdt&code=%s',
        'ios_wx_gdt' => '/sources/gdt_click?source=wx_gdt&code=%s&fr=%s',
        'android_uc' => '/sources/uc_click?code=%s&imei={IMEI_SUM}&time={TS}&callback={CALLBACK_URL}',
        'ios_uc' => '/sources/uc_click?code=%s&fr=%s&idfa={IDFA_SUM}&time={TS}&callback={CALLBACK_URL}',
        'android_momo' => '/sources/mm_click?code=%s&imei=[IMEI]&os=[OS]&ts=[TS]&callback=[CALLBACK]&ua=[UA]&lbs=[LBS]',
        'ios_momo' => '/sources/mm_click?code=%s&fr=%s&idfa=[IDFA]&os=[OS]&ts=[TS]&callback=[CALLBACK]&ua=[UA]&lbs=[LBS]',
        'android_baidu' => '/sources/baidu_click?code=%s&imei_md5={{IMEI_MD5}}&os={{OS}}&ip={{IP}}&ua={{UA}}&ts={{TS}}&userid={{USER_ID}}&pid={{PLAN_ID}}&uid={{UNIT_ID}}&aid={{IDEA_ID}}&click_id={{CLICK_ID}}&callback_url={{CALLBACK_URL}}&sign={{SIGN}}',
        'ios_baidu' => '/sources/baidu_click?code=%s&fr=%s&idfa={{IDFA}}&os={{OS}}&ip={{IP}}&ua={{UA}}&ts={{TS}}&userid={{USER_ID}}&pid={{PLAN_ID}}&uid={{UNIT_ID}}&aid={{IDEA_ID}}&click_id={{CLICK_ID}}&callback_url={{CALLBACK_URL}}&sign={{SIGN}}',
        'android_sina' => '/sources/sina_click?code=%s&uuid={Uuid}&devid={User}&groupid={Groupid}',
        'ios_sina' => '/sources/sina_click?code=%s&fr2=%s&uuid={Uuid}&devid={User}&groupid={Groupid}'
    ];

    function mergeJson()
    {
        return ['operator_username' => $this->operator_username];
    }

}
