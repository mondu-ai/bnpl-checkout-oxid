<?php

declare(strict_types=1);

namespace OxidEsales\MonduPayment\Event;

use OxidEsales\EshopCommunity\Internal\Framework\Event\AbstractShopAwareEventSubscriber;
use OxidEsales\EshopCommunity\Internal\Framework\Module\Setting\Event\SettingChangedEvent;
use OxidEsales\MonduPayment\Core\Config;
use OxidEsales\MonduPayment\Core\Http\MonduClient;
use OxidEsales\MonduPayment\Model\Webhook;
use OxidEsales\MonduPayment\Core\Utils\MonduHelper;

class SettingChangedSubscriber extends AbstractShopAwareEventSubscriber
{
    protected const REQUIRED_WEBHOOK_TOPICS = ['order', 'invoice/created', 'invoice/canceled'];

    private MonduClient $_client;
    private Config $_config;

    public function __construct(MonduClient $client)
    {
        $this->_client = $client;
        $this->_config = oxNew(Config::class);
    }

    public function afterSettingChange(SettingChangedEvent $event)
    {
        if (
            $event->getSettingName() === 'oemonduWebhookSecret' ||
            $event->getSettingName() === 'oemonduIsMerchantIdentified'
        ) {
            return;
        }

        if (!$this->_client->getWebhooksSecret()) {
            $this->setIsMerchantIdentified(false);
            return MonduHelper::showErrorMessage('INVALID_API_KEY');
        }

        $this->setIsMerchantIdentified(true);
        $this->registerWebhooks();
    }

    protected function setIsMerchantIdentified($isMerchantIdentified)
    {
        $this->_config->setIsMerchantIdentified($isMerchantIdentified);
    }

    protected function registerWebhooks()
    {
        foreach (self::REQUIRED_WEBHOOK_TOPICS as $webhookTopic) {
            $webhookParams = oxNew(Webhook::class, $webhookTopic)->getData();
            $response = $this->_client->registerWebhook($webhookParams);

            if (!$response['webhook']) {
                $errorMessage = $response['status'] === 403 ? 'INVALID_API_KEY' : 'MONDU_REGISTER_WEBHOOK_ERROR';

                return MonduHelper::showErrorMessage($errorMessage);
            }
        }
    }

    public static function getSubscribedEvents()
    {
        return [
            SettingChangedEvent::NAME => 'afterSettingChange'
        ];
    }
}
