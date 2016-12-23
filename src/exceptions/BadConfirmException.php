<?php

namespace hiam\mrdp\exceptions;

/**
 * BadConfirmException sent when login confirmation from MRDP is broken.
 * Holds the confirmation data.
 */
class BadConfirmException extends \Exception
{
    public function __construct($data)
    {
        $this->data = $data;
    }

    private $data;

    public function getData()
    {
        return $this->data;
    }
}
