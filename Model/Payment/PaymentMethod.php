<?php

namespace GalaxPay\Payment\Model\Payment;

class PaymentMethod
{
    const BOLETO = 'boleto';
    const CREDIT_CARD = 'creditcard';
    const PIX = 'pix';

    public function __construct(Api $api, \Magento\Payment\Model\CcConfig $ccConfig)
    {
        $this->api = $api;
        $this->ccConfig = $ccConfig;
    }

    public function getCreditCardTypes()
    {
        $types = [];
        $response = $this->api->request('card-brands', 'GET');
        if ($response && isset($response['CardBrands'])) {
            $response = $response['CardBrands'];
            foreach ($response as $res) {
                $types[$res['id']] = $res['name'];
            }
        }
        if (empty($types)) {
            $types["mastercard"] = "MasterCard";
            $types["visa"] = "Visa";
        }
        return $types;
    }

    /**
     * Make an API request to retrieve Payment Methods.
     *
     * @return array|bool
     */
    public function get()
    {
        $paymentMethods = [
            'credit_card' => [],
            'Pix' => [],
            'Boleto' => false,
        ];

        $response = $this->api->request('payment-methods', 'GET');

        if (false === $response) {
            $this->acceptPix = false;
            return $this->acceptBoleto = false;
        }

        foreach ($response['PaymentMethods'] as $method) {
            $id = $method['id'];
            if ('PaymentMethod::CreditCard' === $id) {
                $paymentMethods['credit_card'] = array_merge(
                    $paymentMethods['credit_card'],
                    $method['payment_companies']
                );
            } elseif ('PaymentMethod::Pix' === $id) {
                $paymentMethods['Pix'] = true;
            } elseif ('PaymentMethod::Boleto' === $id) {
                $paymentMethods['Boleto'] = true;
            }
        }

        $this->acceptBoleto = $paymentMethods['Boleto'];
        $this->acceptPix = $paymentMethods['Pix'];

        return $paymentMethods;
    }

    // public function isCcTypeValid($ccType)
    // {
    //     $validCreditCardTypes = $this->getCreditCardTypes();
    //     $fullName = $this->getCcTypeFullName($ccType);
    //     $fullTrimmedName = strtolower(str_replace(' ', '', $fullName));

    //     foreach ($validCreditCardTypes as $validCreditCardType) {
    //         $trimmedName = strtolower(str_replace(' ', '', $validCreditCardType));

    //         if ($trimmedName == $fullTrimmedName) {
    //             return true;
    //         }
    //     }

    //     return false;
    // }

    // private function getCcTypeFullName($ccType)
    // {
    //     $fullNames = $this->getCreditCardTypes();

    //     if (isset($fullNames[$ccType])) {
    //         return $fullNames[$ccType];
    //     }

    //     throw new \Exception(__("Could Not Find Payment Credit Card Type")->getText());
    // }
}
