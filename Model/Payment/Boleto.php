<?php

namespace GalaxPay\Payment\Model\Payment;

use Magento\Framework\DataObject;
use GalaxPay\Payment\Block\Info\Boleto as InfoBlock;

class Boleto extends \GalaxPay\Payment\Model\Payment\AbstractMethod
{
    const CODE = 'GalaxPay_boleto';

    protected $_code = self::CODE;
    protected $_isOffline = true;
    protected $_infoBlockType = InfoBlock::class;

    /**
     * @var bool
     */
    protected $_isGateway = true;

    /**
     * @var bool
     */
    protected $_canAuthorize = true;

    /**
     * @var bool
     */
    protected $_canCapture = true;

    /**
     * @var bool
     */
    protected $_canCapturePartial = false;

    /**
     * @var bool
     */
    protected $_canRefund = false;

    /**
     * @var bool
     */
    protected $_canVoid = false;

    /**
     * @var bool
     */
    protected $_canUseInternal = true;

    /**
     * @var bool
     */
    protected $_canUseCheckout = true;

    /**
     * @var bool
     */
    protected $_canUseForMultishipping = false;

    /**
     * @var bool
     */
    protected $_isInitializeNeeded = false;

    /**
     * @var bool
     */
    protected $_canSaveCc = false;

    /**
     * Assign data to info model instance
     *
     * @param mixed $data
     *
     * @return Boleto
     */
    public function assignData(DataObject $data)
    {
        $info = $this->getInfoInstance();
        $info->setAdditionalInformation('installments', 1);
        $info->save();

        parent::assignData($data);

        return $this;
    }

    /**
     * @return string
     */
    protected function getPaymentMethodCode()
    {
        return PaymentMethod::BOLETO;
    }
}
