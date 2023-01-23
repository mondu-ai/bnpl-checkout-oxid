<?php

namespace OxidEsales\MonduPayment\Controller\Admin;

use OxidEsales\Eshop\Core\Registry;
use OxidEsales\EshopCommunity\Internal\Framework\Module\Setting\Event\SettingChangedEvent;
use OxidEsales\EshopCommunity\Internal\Framework\Module\Configuration\Bridge\ModuleConfigurationDaoBridgeInterface;

class ModuleConfiguration extends ModuleConfiguration_parent
{
    public function saveConfVars()
    {
        $oldValues = $this->getCurrentConfigVariables();

        parent::saveConfVars();

        $newValues = $this->getCurrentConfigVariables();

        foreach (array_diff($newValues, $oldValues) as $settingKey => $settingValue) {
            $this->dispatchSettingChangedEvent($settingKey);
        }
    }

    private function getCurrentConfigVariables()
    {
        $moduleConfiguration = $this->getContainer()->get(ModuleConfigurationDaoBridgeInterface::class)->get('oemondu');
        $moduleSettings = [];

        foreach ($moduleConfiguration->getModuleSettings() as $setting) {
            $moduleSettings[$setting->getName()] = $setting->getValue();
        }

        return $moduleSettings;
    }

    private function dispatchSettingChangedEvent($settingKey)
    {
        $this->dispatchEvent(
            new SettingChangedEvent(
                $settingKey,
                Registry::getConfig()->getShopId(),
                'oemondu'
            ),
            SettingChangedEvent::NAME
        );
    }
}
