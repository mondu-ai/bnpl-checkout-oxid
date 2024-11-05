<?php

namespace OxidEsales\MonduPayment\Core\WebhookHandler;

use OxidEsales\Eshop\Core\Registry;
use OxidEsales\MonduPayment\Model\MonduInvoice;
use OxidEsales\MonduPayment\Model\MonduOrder;
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpFoundation\Response;

class WebhookHandler
{
    private LoggerInterface $_logger;

    public function __construct()
    {
        $this->_logger = Registry::getLogger();
    }

    public function handleWebhook($params)
    {
        $this->_logger->debug('MonduWebhookHandler [handleWebhook]: ' . print_r($params, true));
        $logger = \OxidEsales\Eshop\Core\Registry::getLogger();
        $logger->debug('MonduWebhooksController [WebhooksSecret]: ' . print_r($params, true));

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
        $this->_logger->debug('MonduWebhookHandler [handleOrderStateChanged]: ' . print_r($params, true));
        $monduOrder = $this->getOrder($params['order_uuid']);

        if ($monduOrder) {
            $monduOrder->updateOrderState($params['order_state']);
            return [['order' => $monduOrder], Response::HTTP_OK];
        }

        return [['error' => 'Order not found'], Response::HTTP_BAD_REQUEST];
    }

    public function getWebhookSecretByShopId($shopId)
    {
        return Registry::getConfig()->getShopConfVar(
            'oemonduWebhookSecret',
            $shopId,
            'module:oemondu'
        );
    }

    public function getShopId($params)
    {
        $monduOrder = $this->getOrder($params['order_uuid']);
        $order = oxNew(\OxidEsales\Eshop\Application\Model\Order::class);
        $order->load($monduOrder->getFieldData('oemondu_orders__oxid_order_id'));

        return $order->oxorder__oxshopid->value;
    }

    public function handleInvoiceStateChanged($params, $state)
    {
        $this->_logger->debug('MonduWebhookHandler [handleInvoiceStateChanged]: ' . print_r($params, true));
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