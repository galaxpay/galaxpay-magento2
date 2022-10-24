<?php

namespace GalaxPay\Payment\Model;

use Magento\Checkout\Model\ConfigProviderInterface;
use Magento\Framework\View\Asset\Source;
use GalaxPay\Payment\Helper\Data;
use GalaxPay\Payment\Model\Payment\Api;
use GalaxPay\Payment\Model\Payment\PaymentMethod;

class ConfigProvider implements ConfigProviderInterface
{
    private $helperData;

    /**
     * @param CcConfig $ccConfig
     * @param Source $assetSource
     */
    public function __construct(
        \Magento\Payment\Model\CcConfig $ccConfig,
        Source $assetSource,
        Data $data,
        \Magento\Checkout\Model\Cart $cart,
        \Magento\Directory\Model\Currency $currency,
        PaymentMethod $paymentMethod
    ) {

        $this->ccConfig = $ccConfig;
        $this->assetSource = $assetSource;
        $this->helperData = $data;
        $this->cart = $cart;
        $this->currency = $currency;
        $this->paymentMethod = $paymentMethod;
        

        
    }

    /**
     * @var string[]
     */
    protected $_methodCode = 'GalaxPay_cc';

    /**
     * {@inheritdoc}
     */
    public function getConfig()
    {
        return [
            'payment' => [
                'GalaxPay_cc' => [
                    'availableTypes' => [$this->_methodCode => $this->paymentMethod->getCreditCardTypes()],
                    'months' => [$this->_methodCode => $this->ccConfig->getCcMonths()],
                    'years' => [$this->_methodCode => $this->ccConfig->getCcYears()],
                    'hasVerification' => [$this->_methodCode => $this->ccConfig->hasVerification()],
                    'installments' => [$this->_methodCode => $this->getInstallments()],
                ]
            ]
        ];
    }

    public function getInstallments()
    {
        $allowInstallments = $this->helperData->isInstallmentsAllowedInStore();
        $maxInstallmentsNumber = $this->helperData->getMaxInstallments();
        $minInstallmentsValue = $this->helperData->getMinInstallmentsValue();
        // var_dump($allowInstallments,$maxInstallmentsNumber,$minInstallmentsValue);
        $quote = $this->cart->getQuote();
        $installments = [1 => 'Crédito a vista'];
        
        if ($maxInstallmentsNumber > 1 && $allowInstallments == true) {
            $total = $quote->getGrandTotal();
            $installmentsTimes = floor($total / $minInstallmentsValue);

            for ($i = 1; $i <= $maxInstallmentsNumber; $i++) {
                $value = ceil($total / $i * 100) / 100;
                $price = $this->currency->format($value, null, null, false);
                $installments[$i] = $i . " de " . $price;
                if (($i + 1) > $installmentsTimes) {
                    break;
                }
            }
        }

        return $installments;
    }
}
