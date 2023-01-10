<?php

namespace OxidEsales\MonduPayment\Core;

class Config
{
    protected const API_URL = 'https://api.mondu.ai/api/v1';
    protected const WIDGET_URL = 'https://checkout.mondu.ai/widget.js';
    protected const SANDBOX_API_URL = 'https://api.demo.mondu.ai/api/v1';
    protected const SANDBOX_WIDGET_URL = 'https://checkout.demo.mondu.ai/widget.js';
    protected const LOCAL_API_URL = 'http://localhost:3000/api/v1';
    protected const LOCAL_WIDGET_URL = 'http://localhost:3002/widget.js';

    public function isSandbox()
    {
        return $this->getParameter('oemonduSandboxMode');
    }

    public function getBaseApiUrl()
    {
        return $this->isSandbox() ? self::SANDBOX_API_URL : self::API_URL;
    }

    public function getWidgetUrl()
    {
        return $this->isSandbox() ? self::SANDBOX_WIDGET_URL : self::WIDGET_URL;
    }

    public function getApiUrl($url = '')
    {
        return $this->getBaseApiUrl() . '/' . $url;
    }

    public function getApiToken()
    {
        return $this->getParameter('oemonduApiKey');
    }

    public function getModuleName()
    {
        return 'oxid';
    }

    public function getModuleVersion()
    {
        $moduleData = $this->getModuleData();

        if ($moduleData) {
            return $moduleData['version'];
        }

        return '';
    }

    public function getShopVersion()
    {
        return oxNew(\OxidEsales\EshopCommunity\Core\ShopVersion::class)->getVersion();
    }

    protected function getModuleData()
    {
        $module = oxNew(\OxidEsales\Eshop\Core\Module\Module::class);

        if ($module->load('oemondu')) {
            return $module->getModuleData();
        }

        return null;
    }

    protected function getParameter($paramName)
    {
        return $this->getConfig()->getConfigParam($paramName);
    }

    protected function getConfig()
    {
        return \OxidEsales\Eshop\Core\Registry::getConfig();
    }
}
