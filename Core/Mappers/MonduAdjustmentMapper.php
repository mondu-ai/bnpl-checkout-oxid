<?php

namespace OxidEsales\MonduPayment\Core\Mappers;

use OxidEsales\MonduPayment\Core\Utils\MonduHelper;

class MonduAdjustmentMapper
{
    public function getMappedOrderData($order)
    {
        $tax = array_values($order->getProductVats(false))[0];
        $discount = (float) $order->getFieldData('oxorder__oxdiscount');
        $shipping = (float) $order->getFieldData('oxorder__oxdelcost');

        $data = [
            "currency" => $order->getOrderCurrency()->name,
            "external_reference_id" => $order->getFieldData('oxorder__oxordernr') ? (string) $order->getFieldData('oxorder__oxordernr') : $order->getId(),
            "amount" => [
                "net_price_cents" => round($order->getOrderNetSum() * 100),
                "tax_cents" => round($tax * 100),
                "gross_amount_cents" => round((float) $order->getFieldData('oxtotalordersum') * 100)
            ],
            "lines" => [[
                "tax_cents" => round($tax * 100),
                "shipping_price_cents" => round($shipping * 100),
                "discount_cents" => round($discount * 100),
                "line_items" => $this->getOrderLineItems($order)
            ]]
        ];

        return MonduHelper::removeEmptyElementsFromArray($data);
    }

    protected function getOrderLineItems($order)
    {
        $orderArticles = array_values($order->getOrderArticles()->getArray());
        return MonduHelper::getMappedOrderArticles($orderArticles);
    }
}
