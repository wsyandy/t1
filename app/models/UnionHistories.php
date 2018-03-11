<?php
/**
 * Created by PhpStorm.
 * User: meixinghao
 * Date: 2018/3/11
 * Time: 下午4:05
 */

class UnionHistories extends BaseModel
{
    /**
     * @type Users
     */
    private $_user;

    /**
     * @type Unions
     */
    private $_union;

    static $STATUS = [STATUS_ON => '已加入', STATUS_BLOCKED => '被踢出', STATUS_OFF => '已退出'];
}