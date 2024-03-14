<?php

namespace OxidEsales\MonduPayment\Core\WebhookHandler;

use OxidEsales\MonduPayment\Model\MonduInvoice;
use OxidEsales\MonduPayment\Model\MonduOrder;
use Symfony\Component\HttpFoundation\Response;

class WebhookHandler
{
    public function handleWebhook($params)
    {
        switch ($params['topic']) {
            case 'order/confirmed':
            case 'order/authorized':
            case 'order/pending':
            case 'order/declined':
            case 'order/canceled':
                return $this->handleOrderStateChanged($params);
            case 'invoice/created':
                return $this->handleInvoiceStateChanged($params, 'created');
            case 'invoice/canceled':
                return $this->handleInvoiceStateChanged($params, 'canceled');
            default:
                return [['error' => 'Unregistered topic'], Response::HTTP_UNPROCESSABLE_ENTITY];
        }
    }

    public function handleOrderStateChanged($params)
    {
        $monduOrder = $this->getOrder($params['order_uuid']);

        if ($monduOrder) {
            $monduOrder->updateOrderState($params['order_state']);
            return [['order' => $monduOrder], Response::HTTP_OK];
        }

        return [['error' => 'Order not found'], Response::HTTP_BAD_REQUEST];
    }

    public function handleInvoiceStateChanged($params, $state)
    {
        $monduInvoice = $this->getInvoice($params['invoice_uuid']);

        if ($monduInvoice) {
            $monduInvoice->updateInvoiceState($state);
            return [['invoice' => $monduInvoice], Response::HTTP_OK];
        }

        return [['error' => 'Invoice not found'], Response::HTTP_BAD_REQUEST];
    }

    private function getOrder($orderUuid)
    {
        $monduOrder = oxNew(MonduOrder::class);
        $monduOrder->loadByOrderUuid($orderUuid);
        return $monduOrder;
    }

    private function getInvoice($invoiceUuid)
    {
        $monduInvoice = oxNew(MonduInvoice::class);
        $monduInvoice->loadByInvoiceUuid($invoiceUuid);
        return $monduInvoice;
    }
}
