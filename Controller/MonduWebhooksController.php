<?php

namespace OxidEsales\MonduPayment\Controller;

use OxidEsales\Eshop\Core\Registry;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use OxidEsales\MonduPayment\Core\WebhookHandler\WebhookHandler;
use OxidEsales\MonduPayment\Core\Config;

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

        $signature = hash_hmac('sha256', $content, $this->_config->getWebhooksSecret());
        if ($signature !== $headers->get('X-Mondu-Signature')) {
            $this->_logger->debug('MonduWebhooksController [WebhooksSecret]: ' . print_r($this->_config->getWebhooksSecret(), true));
            $this->_logger->debug('MonduWebhooksController [Content]: ' . print_r($content, true));
            $this->_logger->debug('MonduWebhooksController [X-Mondu-Signature]: ' . print_r($headers->get('X-Mondu-Signature'), true));
            $this->_logger->debug('MonduWebhooksController [Signature]: ' . print_r($signature, true));

            return new Response('Invalid signature', 401);
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
