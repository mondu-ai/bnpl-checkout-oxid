<?php

namespace OxidEsales\MonduPayment\Core\Utils;

use OxidEsales\Eshop\Core\Registry;
use OxidEsales\EshopCommunity\Internal\Framework\Module\Setup\Bridge\ModuleActivationBridgeInterface;
use OxidEsales\EshopCommunity\Internal\Container\ContainerFactory;

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

  public static function isMonduModuleActive()
  {
    $container = ContainerFactory::getInstance()->getContainer();
    $moduleActivationBridge = $container->get(ModuleActivationBridgeInterface::class);

    return $moduleActivationBridge->isActive(
      'oemondu',
      Registry::getConfig()->getShopId()
    );
  }

  public static function camelToSnakeCase($string)
  {
    return strtoupper(preg_replace("/([a-z])([A-Z])/", "$1_$2", $string));
  }
}
