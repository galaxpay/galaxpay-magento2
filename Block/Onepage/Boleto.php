<?php

namespace GalaxPay\Payment\Block\Onepage;

class Boleto extends \Magento\Framework\View\Element\Template
{
    protected $checkoutSession;

    public function __construct(
        \Magento\Checkout\Model\Session $checkoutSession,
        \Magento\Framework\View\Element\Template\Context $context,
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->checkoutSession = $checkoutSession;
    }

    public function getOrder()
    {
        return $this->checkoutSession->getLastRealOrder();
    }

    public function canShowBoleto()
    {
        $order = $this->getOrder();
        if ($order->getPayment()->getMethod() === \GalaxPay\Payment\Model\Payment\Boleto::CODE) {
            return true;
        }

        return false;
    }

    public function getBoletoPrintUrl()
    {
        return $this->getOrder()->getPayment()->getAdditionalInformation('print_url');
    }
}
