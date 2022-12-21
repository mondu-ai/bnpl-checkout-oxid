<?php

namespace OxidEsales\MonduPayment\Controller\Admin;

use OxidEsales\MonduPayment\Core\Http\MonduClient;
use OxidEsales\MonduPayment\Core\Utils\MonduHelper;

class OrderList extends OrderList_parent
{
    protected MonduClient $client;
    protected $_oOrder = null;
    protected $_oMonduOrder = null;

    public function __construct()
    {
        parent::__construct();

        $this->client = oxNew(MonduClient::class);
        $this->_oOrder = $this->getOrder();
        $this->_oMonduOrder = $this->getMonduOrder();
    }

    public function cancelOrder()
    {
        if ($this->isMonduPayment() && !$this->cancelMonduOrder()) {
            return MonduHelper::showErrorMessage('MONDU_CANCEL_ORDER_ERROR');
        }

        $this->_oOrder->cancelOrder();
        $this->resetContentCache();
        $this->init();
    }

    public function deleteEntry()
    {
        if ($this->isMonduPayment() && !$this->cancelMonduOrder()) {
            return MonduHelper::showErrorMessage('MONDU_CANCEL_ORDER_ERROR');
        }

        parent::deleteEntry();
    }

    protected function cancelMonduOrder()
    {
        if ($this->_oMonduOrder) {
            $response = $this->client->cancelOrder($this->_oMonduOrder->getFieldData('order_uuid'));

            if ($response) {
                $this->_oMonduOrder->cancelMonduOrder();
            }

            return $response;
        }

        return false;
    }

    protected function getOrder()
    {
        $oOrder = oxNew(\OxidEsales\Eshop\Application\Model\Order::class);
        $oxId = $this->getEditObjectId();

        if (isset($oxId) && $oxId != "-1") {
            $oOrder->load($oxId);
            return $oOrder;
        }

        return null;
    }

    protected function getMonduOrder()
    {
        if ($this->isMonduPayment()) {
            return array_values($this->_oOrder->getMonduOrders()->getArray())[0];
        }
    }

    protected function isMonduPayment()
    {
        return $this->_oOrder && $this->_oOrder->isMonduPayment();
    }
}
