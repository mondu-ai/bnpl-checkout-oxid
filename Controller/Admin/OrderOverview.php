<?php

namespace OxidEsales\MonduPayment\Controller\Admin;

use OxidEsales\MonduPayment\Core\OrderShippingProcessor;
use OxidEsales\MonduPayment\Core\Utils\MonduHelper;

class OrderOverview extends OrderOverview_parent
{
    protected $_oOrder = null;
    protected OrderShippingProcessor $_monduShippingProcessor;

    public function __construct()
    {
        parent::__construct();

        $this->_oOrder = $this->getOrder();
        $this->_monduShippingProcessor = oxNew(OrderShippingProcessor::class, $this->_oOrder);
    }

    public function sendorder()
    {
        if ($this->isMonduPayment() && !$this->_monduShippingProcessor->shipMonduOrder()) {
            return MonduHelper::showErrorMessage('MONDU_CREATE_INVOICE_ERROR');
        }

        parent::sendorder();
    }

    public function resetorder()
    {
        if ($this->isMonduPayment() && !$this->_monduShippingProcessor->cancelMonduOrderShipping()) {
            return MonduHelper::showErrorMessage('MONDU_CANCEL_INVOICE_ERROR');
        }

        return parent::resetorder();
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
