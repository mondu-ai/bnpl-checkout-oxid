<?php

namespace OxidEsales\MonduPayment\Core\PaymentHandler;

use OxidEsales\Eshop\Application\Model\Order;
use OxidEsales\MonduPayment\Core\Http\MonduClient;
use OxidEsales\Eshop\Core\Registry;
use OxidEsales\MonduPayment\Model\MonduOrder;

class MonduInvoiceHandler
{
    protected MonduClient $client;

    public function __construct()
    {
        $this->client = oxNew(MonduClient::class);
    }

    public function execute(Order $oOrder): bool
    {
        $session = Registry::getSession();
        $monduOrderUuid = $session->getVariable('mondu_order_uuid');

        if (!$monduOrderUuid) {
            return false;
        }

        $updatedOrder = $this->client->updateOrderExternalInfo(
            $monduOrderUuid,
            ['external_reference_id' => $oOrder->getId()]
        );

        $monduOrder = $this->client->getMonduOrder($updatedOrder['uuid']);
        $this->storeMonduOrder($oOrder, $monduOrder);
        $this->clearSession();

        return true;
    }

    protected function storeMonduOrder($order, $monduOrderData)
    {
        $monduOrder = oxNew(MonduOrder::class);
        $monduOrder->createMonduOrderFromResponse($monduOrderData, $order);
    }

    protected function clearSession()
    {
        $session = Registry::getSession();
        $session->deleteVariable('mondu_order_uuid');
    }
}
