<?php

namespace OxidEsales\MonduPayment\Core\Http;

use OxidEsales\MonduPayment\Core\Config;
use OxidEsales\Eshop\Core\Registry;
use OxidEsales\Eshop\Core\Exception\StandardException;
use OxidEsales\MonduPayment\Core\Exception\InvalidRequestException;
use OxidEsales\MonduPayment\Core\Http\HttpRequest;
use OxidEsales\MonduPayment\Core\Utils\MonduHelper;

class MonduClient
{
    private Config $_config;
    private HttpRequest $_client;
    private $_baseUrl = '';
    private $_logger = null;

    public function __construct()
    {
        $this->_config = oxNew(Config::class);
        $this->_baseUrl = $this->_config->getApiUrl();
        $this->_logger = Registry::getLogger();

        $this->_client = oxNew(
            HttpRequest::class,
            $this->_baseUrl,
            [
                'Content-Type: application/json',
                'Api-Token: ' . $this->_config->getApiToken(),
                'x-plugin-name: ' . $this->_config->getModuleName(),
                'x-plugin-version: ' . $this->_config->getModuleVersion()
            ]
        );
    }

    public function createOrder($data = [])
    {
        $order = $this->sendRequest('POST', 'orders', $data);
        return $order['order'] ?? null;
    }

    public function updateOrderExternalInfo($orderUuid, $data = [])
    {
        $order = $this->sendRequest('POST', 'orders/' . $orderUuid . '/update_external_info', $data);
        return $order['order'] ?? null;
    }

    public function getMonduOrder($orderUuid)
    {
        $order = $this->sendRequest('GET', 'orders/' . $orderUuid);
        return $order['order'] ?? null;
    }

    public function createInvoice($orderUuid, $data)
    {
        $invoice = $this->sendRequest('POST', 'orders/' . $orderUuid . '/invoices', $data);
        return $invoice['invoice'] ?? null;
    }

    public function cancelInvoice($orderUuid, $invoiceUuid)
    {
        $invoice = $this->sendRequest('POST', 'orders/' . $orderUuid . '/invoices/' . $invoiceUuid . '/cancel');
        return $invoice['invoice'] ?? null;
    }

    public function getPaymentMethods()
    {
        $paymentMethods = $this->sendRequest('GET', 'payment_methods');
        return $paymentMethods['payment_methods'] ?? null;
    }

    public function cancelOrder($orderUuid)
    {
        $order = $this->sendRequest('POST', 'orders/' . $orderUuid . '/cancel');
        return $order['order'] ?? null;
    }

    public function logEvent($eventData)
    {
        try {
            $this->_client->post('plugin/events', $eventData);
        } catch (StandardException $e) {
            $this->_logger->error('MonduClient::logEvent Failed with an exception message: ' . $e->getString());
        }
    }

    protected function sendRequest($method = 'GET', $url = '', $body = [])
    {
        try {
            $url = $this->_baseUrl . $url;
            $response = $this->_client->send_request($url, $body, $method);
            return $response;
        } catch (InvalidRequestException $e) {
            $this->_logger->error("MonduClient [{$method} {$url}]: Failed with an exception message: {$e->getString()}");

            $logParams = MonduHelper::removeEmptyElementsFromArray([
                'plugin' => $this->_config->getModuleName(),
                'version' => $this->_config->getModuleVersion(),
                'language_version' => 'PHP ' . phpversion(),
                'shop_version' => $this->_config->getShopVersion(),
                'origin_event' => debug_backtrace()[1]['function'],
                'request_body' => $e->getRequestBody(),
                'response_status' => $e->getResponseStatus(),
                'response_body' => $e->getResponseBody(),
                'error_trace' => $e->getString()
            ]);

            $this->logEvent($logParams);

            return null;
        }
    }
}
