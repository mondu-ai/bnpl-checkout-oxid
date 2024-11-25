<?php

namespace OxidEsales\MonduPayment\Controller;

use OxidEsales\Eshop\Core\Registry;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use OxidEsales\MonduPayment\Core\WebhookHandler\WebhookHandler;
use OxidEsales\MonduPayment\Core\Config;
use OxidEsales\MonduPayment\Model\MonduOrder;
use OxidEsales\Eshop\Application\Model\Order;
use OxidEsales\MonduPayment\Core\Utils\MonduHelper;

class MonduWebhooksController extends \OxidEsales\Eshop\Application\Controller\FrontendController
{
    private $_webhookHandler;
    private $_config;
    private $_logger;

    public function __construct()
    {
        $this->_webhookHandler = oxNew(WebhookHandler::class);
        $this->_config = oxNew(Config::class);
        $this->_logger = Registry::getLogger();
    }

    public function render()
    {
        ini_set('html_errors', 'off');

        $request = $this->getContainer()->get('request');
        $response = $this->handleRequest($request);

        $response->send();

        exit();
    }

    private function handleRequest(Request $request): Response
    {
        $content = $request->getContent();
        $headers = $request->headers;
        $params = json_decode($content, true);
        $signatureIsValid = false;
        $shopId = $this->_webhookHandler->getShopId($params);
        $shopIds = $shopId ? [['OXID' => $shopId]] : MonduHelper::getAllShopIds();

        foreach ($shopIds as $shopId) {
            if (isset($shopId['OXID'])) {
                $signature = hash_hmac('sha256', $content, $this->_webhookHandler->getWebhookSecretByShopId($shopId['OXID']));
                if ($signature === $headers->get('X-Mondu-Signature')) {
                    $signatureIsValid = true;
                    break;
                }
            }
        }

        if (!$signatureIsValid) {
            return new Response($logData, 401);
        }

        $params = json_decode($content, true);
        [$resBody, $resStatus] = $this->_webhookHandler->handleWebhook($params);

        return new Response(
            json_encode($resBody),
            $resStatus,
            ['content-type' => 'application/json']
        );
    }
}