<?php

class XingYuTask extends \Phalcon\Cli\Task
{
    function companyIpAction()
    {
        $company_ip = '116.226.124.47';
        $users = \Users::findByIp($company_ip);
        foreach ($users as $user) {
            $user->organisation = USER_ORGANISATION_COMPANY;
            $user->update();
            info('id:' . $user->id, '，员工昵称：' . $user->nickname, '，所属组织：', $user->organisation,'ip地址：'.$user->ip);
        }
    }
}