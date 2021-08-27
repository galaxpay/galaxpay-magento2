<?php
namespace GalaxPay\Payment\Block\Info;

use GalaxPay\Payment\Model\Payment\PaymentMethod;

class Cc extends \Magento\Payment\Block\Info
{
    use \GalaxPay\Payment\Block\InfoTrait;

    /**
     * @var string
     */
    protected $_template = 'GalaxPay_Payment::info/cc.phtml';

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
}
