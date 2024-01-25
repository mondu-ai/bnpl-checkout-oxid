<?php

namespace OxidEsales\MonduPayment\Controller;

use OxidEsales\Eshop\Application\Model\Order;
use OxidEsales\Eshop\Core\Registry;
use OxidEsales\Eshop\Core\Request;
use OxidEsales\MonduPayment\Core\Http\MonduClient;
use Symfony\Component\Config\Definition\Exception\Exception;

class SuccessController extends \OxidEsales\Eshop\Application\Controller\FrontendController
{
    private MonduClient $_client;
    private ?Order $_oOrder;

    /**
     * @var null
     */
    private $_oMonduOrder;

    public function __construct(Order $order = null)
    {
        parent::__construct();

        $this->_client = oxNew(MonduClient::class);
        $this->_oOrder = $order;
        $this->_oMonduOrder = null;
        $this->getMonduOrder();
    }

    /**
     * @throws \Exception
     */
    public function createOrder()
    {
        $monduId = Registry::get(Request::class)->getRequestEscapedParameter('order_uuid');
        $externalReferenceId = Registry::get(Request::class)->getRequestEscapedParameter('external_reference_id');

        if (!$monduId || !$externalReferenceId) {
            throw new \Exception('Mondu: Not found');
        }

        try {
            $response = $this->_client->authorizeOrder([
                'orderUid' => $monduId,
                'external_reference_id' => $externalReferenceId
            ]);

        } catch (Exception $e) {
            throw new \Exception('Mondu: Error during the order process');
        }
    }

    protected function getMonduOrder()
    {
        if ($this->_oOrder && $this->_oOrder->isMonduPayment() && !$this->_oMonduOrder) {
            $this->_oMonduOrder = array_values($this->_oOrder->getMonduOrders()->getArray())[0];
        }
    }
}
