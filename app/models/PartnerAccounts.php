<?php

class PartnerAccounts extends BaseModel
{
    static $STATUS = [OPERATOR_STATUS_NORMAL => '正常', OPERATOR_STATUS_BLOCKED => '禁用'];

    function getMd5()
    {
        return md5($this->id . $this->username . $this->password . $this->status);
    }
    function toSimpleJson()
    {
        return array(
            'id' => $this->id,
            'username' => $this->username,
            'status_text' => $this->status_text
        );
    }

    function isBlocked()
    {
        return $this->status == OPERATOR_STATUS_BLOCKED;
    }

    static function auth($id, $md5)
    {
        $partner_account = \PartnerAccounts::findFirstById($id);
        if ($partner_account && $md5 === $partner_account->md5) {
            return $partner_account;
        }
        return null;
    }
}