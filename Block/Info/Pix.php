<?php
namespace GalaxPay\Payment\Block\Info;

use GalaxPay\Payment\Model\Payment\PaymentMethod;

class Pix extends \Magento\Payment\Block\Info
{
    /**
     * @var string
     */
    protected $_template = 'GalaxPay_Payment::info/pix.phtml';

    protected $_currency;

    public function __construct(
        PaymentMethod $paymentMethod,
        \Magento\Framework\Pricing\Helper\Data $currency,
        \Magento\Backend\Block\Template\Context $context,
        array $data = []
    ) {

        parent::__construct($context, $data);
        $this->paymentMethod = $paymentMethod;
        $this->_currency = $currency;
    }

    public function getOrder()
    {
        return $this->getInfo()->getOrder();
    }

    public function canShowBoletoInfo()
    {
        return $this->getOrder()->getPayment()->getMethod() === \GalaxPay\Payment\Model\Payment\Pix::CODE;
    }

    public function getPrintUrl()
    {
        return $this->getOrder()->getPayment()->getAdditionalInformation('print_url');
    }

    public function getDueDate()
    {
        return $this->getOrder()->getPayment()->getAdditionalInformation('due_at');
    }
}
