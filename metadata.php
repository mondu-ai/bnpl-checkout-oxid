<?php

$sMetadataVersion = '2.0';

$aModule = array(
    'id'           => 'oemondu',
    'title'        => 'Mondu',
    'description'  => array(
        'de' => 'Modul für die Zahlung mit Mondu.',
        'en' => 'Module for Mondu payment.',
    ),
    'thumbnail'    => 'out/src/images/plugin.png',
    'version'      => '1.0.0',
    'author'       => 'Mondu GmbH',
    'url'          => 'https://www.mondu.ai',
    'email'        => 'contact@mondu.ai',
    'extend' => array(
        // Models
        \OxidEsales\Eshop\Application\Model\Country::class                        => \OxidEsales\MonduPayment\Model\Country::class,
        \OxidEsales\Eshop\Application\Model\Payment::class                        => \OxidEsales\MonduPayment\Model\MonduPayment::class,
        \OxidEsales\Eshop\Application\Model\PaymentGateway::class                 => \OxidEsales\MonduPayment\Model\PaymentGateway::class,
        \OxidEsales\Eshop\Application\Model\Order::class                          => \OxidEsales\MonduPayment\Model\Order::class,

        // Controllers
        \OxidEsales\Eshop\Application\Controller\PaymentController::class         => \OxidEsales\MonduPayment\Controller\PaymentController::class,
        \OxidEsales\Eshop\Application\Controller\OrderController::class           => \OxidEsales\MonduPayment\Controller\OrderController::class,
        \OxidEsales\Eshop\Application\Controller\Admin\OrderOverview::class       => \OxidEsales\MonduPayment\Controller\Admin\OrderOverview::class,
        \OxidEsales\Eshop\Application\Controller\Admin\OrderMain::class           => \OxidEsales\MonduPayment\Controller\Admin\OrderMain::class,
        \OxidEsales\Eshop\Application\Controller\Admin\OrderList::class           => \OxidEsales\MonduPayment\Controller\Admin\OrderList::class,
        \OxidEsales\Eshop\Application\Controller\Admin\OrderArticle::class        => \OxidEsales\MonduPayment\Controller\Admin\OrderArticle::class,
        \OxidEsales\Eshop\Application\Controller\Admin\ModuleConfiguration::class => \OxidEsales\MonduPayment\Controller\Admin\ModuleConfiguration::class,

        \OxidEsales\Eshop\Core\ViewConfig::class                                  => \OxidEsales\MonduPayment\Core\ViewConfig::class,
    ),
    'controllers' => array(
        'oemonducheckout' => \OxidEsales\MonduPayment\Controller\MonduCheckoutController::class,
        'oemonduwebhooks' => \OxidEsales\MonduPayment\Controller\MonduWebhooksController::class
    ),
    'blocks' => array(
        array(
            'template' => 'page/checkout/payment.tpl',
            'block'    => 'select_payment',
            'file'     => '/views/blocks/page/checkout/paymentselector.tpl'
        ),
        array(
            'template' => 'page/checkout/order.tpl',
            'block'    => 'checkout_order_btn_confirm_bottom',
            'file'     => '/views/blocks/widget/mondu_widget.tpl'
        ),
        array(
            'template' => 'order_overview.tpl',
            'block'    => 'admin_order_overview_checkout',
            'file'     => 'views/blocks/admin/order_overview.tpl',
        ),
        array(
            'template' => 'order_main.tpl',
            'block'    => 'admin_order_main_send_order',
            'file'     => 'views/blocks/admin/order_main.tpl',
        )
    ),
    'events'       => array(
        'onActivate'   => '\OxidEsales\MonduPayment\Core\Events::onActivate',
        'onDeactivate' => '\OxidEsales\MonduPayment\Core\Events::onDeactivate'
    ),
    'settings' => array(
        array('group' => 'oemondu_banners', 'name' => 'oemonduApiKey', 'type' => 'str', 'value' => ''),
        array('group' => 'oemondu_banners', 'name' => 'oemonduSandboxMode', 'type' => 'bool', 'value' => 'false'),
        array('group' => 'oemondu_banners', 'name' => 'oemonduErrorLogging', 'type' => 'bool', 'value' => 'true'),
        array('name'  => 'oemonduWebhookSecret', 'type' => 'str', 'value' => '')
    )
);
