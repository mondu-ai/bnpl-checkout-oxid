<?php

declare(strict_types=1);

namespace OxidEsales\MonduPayment\Event;

use OxidEsales\EshopCommunity\Internal\Framework\Event\AbstractShopAwareEventSubscriber;
use OxidEsales\EshopCommunity\Internal\Framework\Module\Setting\Event\SettingChangedEvent;
use OxidEsales\MonduPayment\Core\Http\MonduClient;
use OxidEsales\MonduPayment\Model\Webhook;
use OxidEsales\MonduPayment\Core\Utils\MonduHelper;

class SettingChangedSubscriber extends AbstractShopAwareEventSubscriber
{
    protected const REQUIRED_WEBHOOK_TOPICS = ['order', 'invoice/created', 'invoice/canceled'];

    private $_client;

    public function __construct(MonduClient $client)
    {
        $this->_client = $client;
    }

    public function afterSettingChange(SettingChangedEvent $event)
    {
        if($event->getModuleId() !== 'oemondu') return;

        if ($event->getSettingName() === 'oemonduWebhookSecret') {
            return;
        }

        if (!$this->_client->getWebhooksSecret()) {
            return MonduHelper::showErrorMessage('INVALID_API_KEY');
        }

        $this->registerWebhooks();
    }

    protected function registerWebhooks()
    {
        foreach (self::REQUIRED_WEBHOOK_TOPICS as $webhookTopic) {
            $webhookParams = oxNew(Webhook::class, $webhookTopic)->getData();
            $response = $this->_client->registerWebhook($webhookParams);

            if ($response['status'] === 409) return;

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
