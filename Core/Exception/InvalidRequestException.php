<?php

namespace OxidEsales\MonduPayment\Core\Exception;

use OxidEsales\Eshop\Core\Exception\StandardException;
use OxidEsales\Eshop\Core\Registry;

class InvalidRequestException extends StandardException
{
    public function __construct($message)
    {
        $logger = Registry::getLogger();
        $logger->error($message);

        parent::__construct($message);
    }
}
