<?php

namespace OxidEsales\MonduPayment\Controller;

use OxidEsales\MonduPayment\Core\Http\MonduClient;
use OxidEsales\MonduPayment\Core\Mappers\MonduOrderMapper;
use OxidEsales\Eshop\Core\Registry;
use OxidEsales\MonduPayment\Model\MonduPayment;
use OxidEsales\Eshop\Application\Model\Order;

class MonduCheckoutController extends \OxidEsales\Eshop\Application\Controller\FrontendController
{
    protected MonduClient $client;

    public function __construct()
    {
        parent::__construct();
        $this->client = oxNew(MonduClient::class);
    }

    public function createOrder()
    {
        $orderMapper = $this->getMonduOrderMapper();
        $orderMapper->setDeliveryAddress($this->getDelAddress());
        $paymentMethod = $this->getPaymentMethod();
        $orderData = $orderMapper->getMappedOrderData($paymentMethod);

        $response = $this->client->createOrder($orderData);
        $token = isset($response['uuid']) ? $response['uuid'] : 'error';

        if ($token !== 'error') {
            $session = Registry::getSession();
            $session->setVariable('mondu_order_uuid', $token);
        }

        echo json_encode(['token' => $token]);

        exit();
    }

    protected function getMonduOrderMapper()
    {
        if ($this->monduOrderMapper === null) {
            $this->monduOrderMapper = oxNew(MonduOrderMapper::class, $basket);
        }

        return $this->monduOrderMapper;
    }

    protected function getDelAddress()
    {
        $order = oxNew(Order::class);
        return $order->getDelAddressInfo();
    }

    protected function getPaymentMethod()
    {
        $session = Registry::getSession();
        $paymentId = $session->getVariable("paymentid");
        $payment = MonduPayment::getMonduPaymentMethodFromPaymentId($paymentId);

        return $payment ? $payment['mondu_payment_method'] : 'invoice';
    }
}
