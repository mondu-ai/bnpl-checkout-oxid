<?php

namespace OxidEsales\MonduPayment\Model;

use OxidEsales\Eshop\Core\Model\BaseModel;

class MonduInvoice extends BaseModel
{
    const TABLE_NAME = 'oemondu_invoices';

    protected $_oMonduOrder = null;
    protected $_aSkipSaveFields = ['created_at', 'updated_at'];

    public function __construct()
    {
        parent::__construct();
        $this->init(self::TABLE_NAME);
    }

    public function createMonduInvoiceFromResponse($invoice, $monduOrder)
    {
        $this->_oMonduOrder = $monduOrder;
        $this->assign($this->_mapMonduInvoiceData($invoice));
        $this->save();
    }

    //TODO: handle via webhook
    public function cancelMonduInvoice()
    {
        if ($this->exists()) {
            $this->oemondu_invoices__invoice_state->rawValue = 'canceled';
            $this->save();
        }
    }

    protected function _mapMonduInvoiceData($invoice)
    {
        return array(
            'oemondu_invoices__invoice_id'  => $invoice['external_reference_id'],
            'oemondu_invoices__invoice_uuid'  => $invoice['uuid'],
            'oemondu_invoices__invoice_state'  => $invoice['state'],
            'oemondu_invoices__mondu_order_id' => $this->_oMonduOrder->getId()
        );
    }
}
