<?php

namespace GalaxPay\Payment\Helper;

use \Magento\Framework\App\Helper\AbstractHelper;
use GalaxPay\Payment\Model\Config\Source\Mode;

class Data extends AbstractHelper
{
    protected $scopeConfig;

    public function __construct(
        \Magento\Framework\App\Helper\Context $context
    ) {

        $this->scopeConfig = $context->getScopeConfig();
        parent::__construct($context);
    }

    public function getCreditCardConfig($field, $group = 'GalaxPay')
    {
        return $this->scopeConfig->getValue(
            'payment/' . $group . '/' . $field,
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
    }

    public function getModuleGeneralConfig($field)
    {
        return $this->scopeConfig->getValue(
            'galaxpayconfiguration/general/' . $field,
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
    }

    public function isInstallmentsAllowedInStore()
    {
        return $this->getCreditCardConfig('allow_installments');
    }

    public function getMaxInstallments()
    {
        return $this->getCreditCardConfig('max_installments');
    }

    public function getMinInstallmentsValue()
    {
        return $this->getCreditCardConfig('min_installment_value');
    }

    public function getShouldVerifyProfile()
    {
        return $this->getCreditCardConfig('verify_method');
    }

    public function getWebhookToken()
    {
        return $this->getModuleGeneralConfig('token_webhook');
    }

    public function getDaysBoleto()
    {
        return $this->getModuleGeneralConfig('days_payday_boleto');
    }

    public function getQtdTimeToPayPix()
    {
        return $this->getModuleGeneralConfig('days_payday_pix');
    }

    public function getTypeTimePix()
    {
        return $this->getModuleGeneralConfig('time_pix');
    }

    public function getMode()
    {
        return $this->getModuleGeneralConfig('mode');
    }

    public function getOrderStatus()
    {
        return $this->getCreditCardConfig('order_status');
    }

    public function getBaseUrl()
    {
        if ($this->getMode() == Mode::PRODUCTION_MODE) {
            return "https://api.galaxpay.com.br/v2/";    
        }
        return "https://api.sandbox.cloud.galaxpay.com.br/v2/";
        
    }
}
