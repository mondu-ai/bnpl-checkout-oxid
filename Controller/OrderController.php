<?php

namespace OxidEsales\MonduPayment\Controller;

use OxidEsales\Eshop\Core\Registry;
use OxidEsales\MonduPayment\Core\Utils\MonduHelper;

class OrderController extends OrderController_parent
{
  public function isMonduPayment()
  {
    $session = Registry::getSession();
    $paymentId = $session->getVariable("paymentid");

    return MonduHelper::isMonduPayment($paymentId);
  }

  public function getPaymentPageUrl()
  {
    $shopUrl = $this->getConfig()->getShopSecureHomeURL();
    return $shopUrl . '&cl=payment&payerror=2';
  }
}
