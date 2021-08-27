<?php

namespace GalaxPay\Payment\Model\Payment;

class Product
{
    public function __construct(
        \Magento\Customer\Api\CustomerRepositoryInterface $customerRepository,
        Api $api,
        \Magento\Framework\Message\ManagerInterface $messageManager
    ) {

        $this->customerRepository = $customerRepository;
        $this->api = $api;
        $this->messageManager = $messageManager;
    }

    public function getTotalAmount($order)
    {
        $total = 0;
        foreach ($order->getItems() as $item) {
            $productType = $item->getProduct()->getTypeId();
            for ($i = 0; $i < $item->getQtyOrdered(); $i++) {
                $itemPrice = $this->getItemPrice($item, $productType); 
                $total += $itemPrice;
            }
        }

        if ($order->getShippingAmount() > 0) {
            $total += $order->getShippingAmount();
        }
        return $total;
    }

    private function getItemPrice($item, $productType)
    {
        if ('bundle' == $productType)
            return 0;

        return $item->getPrice();
    }

}
