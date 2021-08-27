<?php

namespace GalaxPay\Payment\Model\Payment;

use GalaxPay\Payment\Helper\Data;

class Profile
{
    private $api;
    public $helperData;

    public function __construct(Api $api, Data $helperData, PaymentMethod $paymentMethod)
    {
        $this->api = $api;
        $this->helperData = $helperData;
        $this->paymentMethod = $paymentMethod;
    }

    public function getNodeCard($payment)
    {
        $creditCardData = [
            'Card' => [
                'holder' => $payment->getCcOwner(),
                'expiresAt' => $payment->getCcExpYear() . '-' . str_pad($payment->getCcExpMonth(), 2, '0', STR_PAD_LEFT),
                'number' => $payment->getCcNumber(),
                'cvv' => $payment->getCcCid() ?: ''
            ]
        ];


        if ($creditCardData === false) {
            throw new \Magento\Framework\Exception\LocalizedException(__('Error while informing credit card data. Verify data and try again'));
        }

        return $creditCardData;
    }
}
