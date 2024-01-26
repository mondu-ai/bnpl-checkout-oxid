<?php

namespace OxidEsales\MonduPayment\Controller;

use OxidEsales\Eshop\Application\Model\Basket;
use OxidEsales\Eshop\Application\Model\Order;
use OxidEsales\Eshop\Application\Model\User;
use OxidEsales\Eshop\Core\Exception\StandardException;
use OxidEsales\Eshop\Core\Registry;
use OxidEsales\Eshop\Core\Request;
use OxidEsales\Eshop\Core\UtilsView;
use OxidEsales\MonduPayment\Core\Http\MonduClient;
use OxidEsales\MonduPayment\Core\Utils\MonduHelper;
use Symfony\Component\Config\Definition\Exception\Exception;

class OrderController extends OrderController_parent
{
    private MonduClient $_client;
    private User|null|false $_oUser;

    public function __construct()
    {
        parent::__construct();

        $this->_client = oxNew(MonduClient::class);
        $this->_oUser = $this->getUser();
    }

    public function isMonduPayment()
    {
        $session = Registry::getSession();
        $paymentId = $session->getVariable('paymentid');

        return MonduHelper::isMonduPayment($paymentId);
    }

    public function getPaymentPageUrl()
    {
        $shopUrl = \OxidEsales\Eshop\Core\Registry::getConfig()->getShopSecureHomeURL();
        return $shopUrl . '&cl=payment&payerror=2';
    }

    /**
     * @throws \Exception
     */
    public function execute()
    {
        if($this->isMonduPayment()){
            $orderUuid = Registry::get(Request::class)->getRequestEscapedParameter('order_uuid');

            if (!$orderUuid) {
                throw new \Exception('Mondu: Not found');
            }

            $monduOrder = $this->_client->getMonduOrder($orderUuid);
            $response = $this->_client->confirmOrder($orderUuid, [
                'external_reference_id' => $monduOrder['external_reference_id']
            ]);

            if (isset($response['state']) && $response['state'] == 'confirmed') {
                try {
                    $iSuccess = $this->monduExecute($this->getBasket());

                    return $this->_getNextStep($iSuccess);
                } catch (Exception $e) {
                    throw new \Exception('Mondu: Error during the order process');
                }
            }
        }

        // if user is not logged in set the user
        if(!$this->getUser() && isset($this->_oUser)){
            $this->setUser($this->_oUser);
        }

        return parent::execute();
    }

    /**
     * Save order to database, delete order_id from session and redirect to thank you page
     *
     * @param Basket $oBasket
     *
     * @return bool|int|mixed
     */
    protected function monduExecute(Basket $oBasket)
    {
        if (!Registry::getSession()->getVariable('sess_challenge')) {
            Registry::getSession()->setVariable('sess_challenge', Registry::getUtilsObject()->generateUID());
        }

        $oBasket->calculateBasket(true);

        $oOrder = oxNew(Order::class);
        try {
            $iSuccess = $oOrder->finalizeOrder($oBasket, $oBasket->getUser());
        } catch (StandardException $e) {
            Registry::get(UtilsView::class)->addErrorToDisplay($e);
        }

        if ($iSuccess === 1) {
            // performing special actions after user finishes order (assignment to special user groups)
            $this->_oUser->onOrderExecute($oBasket, $iSuccess);

            if ($this->isRegisterNewUserNeeded()) {
                $oEmail = oxNew(\OxidEsales\Eshop\Core\Email::class);
                $oEmail->sendForgotPwdEmail($this->_oUser->oxuser__oxusername->value);
            }

            Registry::getSession()->setVariable('paymentid', 'oxmondu');
        }

        return $iSuccess;
    }
}
