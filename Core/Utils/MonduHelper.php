<?php

namespace OxidEsales\MonduPayment\Core\Utils;

use OxidEsales\Eshop\Core\Registry;

class MonduHelper
{
  public static function removeEmptyElementsFromArray(array $array)
  {
    return array_filter($array, function ($v) {
      return !is_null($v) && $v !== '';
    });
  }

  public static function showErrorMessage($message = '')
  {
    Registry::getUtilsView()->addErrorToDisplay($message, false);
  }

  public static function isMonduPayment($paymentId = '')
  {
    return stripos($paymentId, "oxmondu") !== false;
  }
}
