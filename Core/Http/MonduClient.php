<?php

namespace OxidEsales\MonduPayment\Core\Http;

use OxidEsales\Eshop\Core\Exception\StandardException;
use OxidEsales\MonduPayment\Core\Config;
use OxidEsales\Eshop\Core\Registry;
use OxidEsales\MonduPayment\Core\Http\HttpRequest;

class MonduClient
{
    private Config $_config;
    private HttpRequest $_client;
    private $_logger = null;

    public function __construct()
    {
        $this->_config = oxNew(Config::class);
        $this->_client = oxNew(
            HttpRequest::class,
            $this->_config->getApiUrl(),
            [
                'Content-Type: application/json',
                'Api-Token: ' . $this->_config->getApiToken()
            ]
        );

        $this->_logger = Registry::getLogger();
    }

    public function createOrder($data = [])
    {
        try {
            $order = $this->_client->post('orders', $data);
            return $order['order'];
        } catch (StandardException $e) {
            $this->_logger->error('MonduClient::createOrder Failed with an exception message: ' . $e->getString());
            return null;
        }
    }

    public function updateOrderExternalInfo($orderUuid, $data = [])
    {
        try {
            $order = $this->_client->post('orders/' . $orderUuid . '/update_external_info', $data);
            return $order['order'];
        } catch (StandardException $e) {
            $this->_logger->error('MonduClient::updateOrderExternalInfo Failed with an exception message: ' . $e->getString());
            return null;
        }
    }

    public function getMonduOrder($orderUuid)
    {
        try {
            $order = $this->_client->get('orders/' . $orderUuid, []);
            return $order['order'];
        } catch (StandardException $e) {
            $this->_logger->error('MonduClient::getMonduOrder Failed with an exception message: ' . $e->getString());
            return null;
        }
    }

    public function createInvoice($orderUuid, $data)
    {
        try {
            $invoice = $this->_client->post('orders/' . $orderUuid . '/invoices', $data);
            return $invoice['invoice'];
        } catch (StandardException $e) {
            $this->_logger->error('MonduClient::createInvoice Failed with an exception message: ' . $e->getString());
            return null;
        }
    }

    public function cancelInvoice($orderUuid, $invoiceUuid)
    {
        try {
            $invoice = $this->_client->post('orders/' . $orderUuid . '/invoices/' . $invoiceUuid . '/cancel', []);
            return $invoice['invoice'];
        } catch (StandardException $e) {
            $this->_logger->error('MonduClient::cancelInvoice Failed with an exception message: ' . $e->getString());
            return null;
        }
    }

    public function getPaymentMethods()
    {
        try {
            $paymentMethods = $this->_client->get('payment_methods', []);
            return $paymentMethods['payment_methods'];
        } catch (StandardException $e) {
            $this->_logger->error('MonduClient::getPaymentMethods failed with an exception message: ' . $e->getString());
            return null;
        }
    }

    public function cancelOrder($orderUuid)
    {
        try {
            $order = $this->_client->post('orders/' . $orderUuid . '/cancel', []);
            return $order['order'];
        } catch (StandardException $e) {
            $this->_logger->error('MonduClient::cancelOrder failed with an exception message: ' . $e->getString());
            return null;
        }
    }
}
