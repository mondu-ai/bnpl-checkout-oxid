<?php

namespace OxidEsales\MonduPayment\Core\Mappers;

use OxidEsales\MonduPayment\Core\Utils\MonduHelper;

class MonduOrderMapper
{
    protected $_basket = null;
    protected $_deliveryAddress = null;

    public function getBasket()
    {
        return $this->_basket;
    }

    public function setBasket($basket)
    {
        $this->_basket = $basket;
    }

    public function getDeliveryAddress()
    {
        return $this->_deliveryAddress;
    }

    public function setDeliveryAddress($deliveryAddress)
    {
        $this->_deliveryAddress = $deliveryAddress;
    }

    protected function getBasketUser()
    {
        return $this->getBasket()->getBasketUser();
    }

    public function getMappedOrderData($paymentMethod)
    {
        $basket = $this->getBasket();

        $tax = array_values($basket->getProductVats(false))[0];
        $discount = $basket->getTotalDiscount()->getPrice();
        $shipping = $basket->getDeliveryCost()->getPrice();

        $data = [
            "currency" => $basket->getBasketCurrency()->name,
            "payment_method" => $paymentMethod,
            "external_reference_id" => uniqid('M_OX_'),
            "gross_amount_cents" => round($basket->getPriceForPayment() * 100),
            "buyer" => MonduHelper::removeEmptyElementsFromArray($this->getBuyerData()),
            "billing_address" => MonduHelper::removeEmptyElementsFromArray($this->getUserBillingAddress()),
            "shipping_address" => MonduHelper::removeEmptyElementsFromArray($this->getUserDeliveryAddress()),
            "lines" => [[
                "tax_cents" => round($tax * 100),
                "shipping_price_cents" => round($shipping * 100),
                "discount_cents" => round($discount * 100),
                "line_items" => $this->getOrderLineItems()
            ]]
        ];

        return MonduHelper::removeEmptyElementsFromArray($data);
    }

    protected function getOrderLineItems()
    {
        $basketContents = $this->getBasket()->getContents();
        $items = array_values($basketContents);
        $lineItems = [];

        foreach ($items as $lineItem) {
            $article = $lineItem->getArticle();

            $lineItems[] = [
                'external_reference_id' => $article->oxarticles__oxid->value,
                'title' => $lineItem->getTitle(),
                'net_price_per_item_cents' => round($lineItem->getUnitPrice()->getNettoPrice() * 100),
                'quantity' => $lineItem->getAmount()
            ];
        }

        return $lineItems;
    }

    protected function getBuyerData()
    {
        $user = $this->getBasketUser();

        return [
            "email" => $user->oxuser__oxusername->rawValue,
            "first_name" => $user->oxuser__oxfname->rawValue,
            "last_name" => $user->oxuser__oxlname->rawValue,
            "company_name" => $user->oxuser__oxcompany->rawValue,
            "phone" => $user->oxuser__oxfon->rawValue
        ];
    }

    protected function getUserBillingAddress()
    {
        $user = $this->getBasketUser();
        $billingCountry = oxNew(\OxidEsales\Eshop\Application\Model\Country::class);
        $billingCountryId = $user->oxuser__oxcountryid->rawValue;
        $billingCountryCode = $billingCountry->getCodeById($billingCountryId);

        return [
            "address_line1" => $user->oxuser__oxstreet->rawValue . $user->oxuser__oxstreetnr->rawValue,
            "city" => $user->oxuser__oxcity->rawValue,
            "country_code" => $billingCountryCode,
            "zip_code" => $user->oxuser__oxzip->rawValue
        ];
    }

    protected function getUserDeliveryAddress()
    {
        $deliveryAddress = $this->getDeliveryAddress();

        if ($deliveryAddress != null) {
            $deliveryCountry = oxNew(\OxidEsales\Eshop\Application\Model\Country::class);
            $deliveryCountryId = $deliveryAddress->oxaddress__oxcountryid->value;
            $deliveryCountryCode = $deliveryCountry->getCodeById($deliveryCountryId);

            return [
                "address_line1" => $deliveryAddress->oxaddress__oxstreet->rawValue . $deliveryAddress->oxaddress__oxstreetnr->rawValue,
                "city" => $deliveryAddress->oxaddress__oxcity->rawValue,
                "country_code" => $deliveryCountryCode,
                "zip_code" => $deliveryAddress->oxaddress__oxzip->rawValue
            ];
        }

        return $this->getUserBillingAddress();
    }
}
