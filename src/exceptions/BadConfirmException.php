<?php
/**
 * HIAM module for MRDP database compatibility
 *
 * @link      https://github.com/hiqdev/hiam-mrdp
 * @package   hiam-mrdp
 * @license   BSD-3-Clause
 * @copyright Copyright (c) 2016, HiQDev (http://hiqdev.com/)
 */

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
