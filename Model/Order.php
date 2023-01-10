<?php

namespace OxidEsales\MonduPayment\Model;

use OxidEsales\Eshop\Core\Model\ListModel;
use OxidEsales\MonduPayment\Core\Utils\MonduHelper;
use OxidEsales\MonduPayment\Model\MonduOrder;
use OxidEsales\MonduPayment\Model\MonduInvoice;

class Order extends Order_parent
{
    public function getMonduOrders()
    {
        if ($this->isMonduPayment()) {
            $sQuery = 'SELECT * FROM `oemondu_orders` WHERE `oxid_order_id`=:oxorderid ORDER BY created_at DESC';

            $oMonduOrders = oxNew(ListModel::class);
            $oMonduOrders->init(MonduOrder::class);
            $oMonduOrders->selectString($sQuery, [':oxorderid' => $this->getId()]);

            return $oMonduOrders;
        }

        return [];
    }

    public function getMonduInvoices()
    {
        if ($this->isMonduPayment()) {
            $sQuery = 'SELECT * FROM `oemondu_invoices` WHERE `invoice_id`=:oxorderid ORDER BY created_at DESC';

            $oMonduInvoices = oxNew(ListModel::class);
            $oMonduInvoices->init(MonduInvoice::class);
            $oMonduInvoices->selectString($sQuery, [':oxorderid' => $this->getId()]);

            return $oMonduInvoices;
        }

        return [];
    }

    public function isMonduPayment()
    {
        return MonduHelper::isMonduPayment($this->getFieldData('oxpaymenttype'));
    }
}
