<?php

namespace OxidEsales\MonduPayment\Controller\Admin;

use OxidEsales\MonduPayment\Core\OrderShippingProcessor;
use OxidEsales\MonduPayment\Core\Utils\MonduHelper;

class OrderMain extends OrderMain_parent
{
    protected $_oOrder = null;
    protected OrderShippingProcessor $_monduShippingProcessor;

    public function __construct()
    {
        parent::__construct();

        $this->_oOrder = $this->getOrder();
        $this->_monduShippingProcessor = oxNew(OrderShippingProcessor::class, $this->_oOrder);
    }

    public function sendOrder()
    {
        if ($this->isMonduPayment() && !$this->_monduShippingProcessor->shipMonduOrder()) {
            return MonduHelper::showErrorMessage('MONDU_CREATE_INVOICE_ERROR');
        }

        parent::sendOrder();
    }

    public function resetOrder()
    {
        if ($this->isMonduPayment() && !$this->_monduShippingProcessor->cancelMonduOrderShipping()) {
            return MonduHelper::showErrorMessage('MONDU_CANCEL_INVOICE_ERROR');
        }

        return parent::resetOrder();
    }

    public function isMonduPayment()
    {
        return $this->_oOrder && $this->_oOrder->isMonduPayment();
    }

    protected function getOrder()
    {
        $oOrder = oxNew(\OxidEsales\Eshop\Application\Model\Order::class);
        $soxId = $this->getEditObjectId();

        if (isset($soxId) && $soxId != "-1") {
            $oOrder->load($soxId);
            return $oOrder;
        }

        return null;
    }
}
