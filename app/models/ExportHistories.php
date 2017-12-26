<?php

class ExportHistories extends BaseModel
{

    /**
     * @type Operators
     */
    private $_operator;

    function mergeJson()
    {
        return ['operator_username' => $this->operator_username];
    }

    function fileUrl()
    {
        if (preg_match('/^http/', $this->file)) {
            return $this->file;
        }

        $url = StoreFile::getUrl($this->file);
        return $url;
    }

}