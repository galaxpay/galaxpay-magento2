<?php

namespace GalaxPay\Payment\Model\Payment;

use Magento\Framework\DataObject;

abstract class AbstractMethod extends \Magento\Payment\Model\Method\AbstractMethod
{
    /**
     * @var \GalaxPay\Payment\Model\Payment\Api
     */
    protected $api;

    /**
     * @var \Magento\Sales\Model\Service\InvoiceService
     */
    protected $invoiceService;

    /**
     * @var Customer
     */
    protected $customer;

    /**
     * @var Product
     */
    protected $product;

    /**
     * @var Bill
     */
    protected $bill;

    /**
     * @var Profile
     */
    protected $profile;

    /**
     * @var PaymentMethod
     */
    protected $paymentMethod;

    /**
     * @var \Psr\Log\LoggerInterface
     */
    protected $psrLogger;

    /**
     * @var \Magento\Framework\Stdlib\DateTime\TimezoneInterface
     */
    protected $date;

    public function __construct(
        \Magento\Framework\Model\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\Api\ExtensionAttributesFactory $extensionFactory,
        \Magento\Framework\Api\AttributeValueFactory $customAttributeFactory,
        \Magento\Payment\Helper\Data $paymentData,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Magento\Payment\Model\Method\Logger $logger,
        \GalaxPay\Payment\Model\Payment\Api $api,
        \Magento\Sales\Model\Service\InvoiceService $invoiceService,
        Customer $customer,
        Product $product,
        Bill $bill,
        Profile $profile,
        PaymentMethod $paymentMethod,
        \Psr\Log\LoggerInterface $psrLogger,
        \Magento\Framework\Stdlib\DateTime\TimezoneInterface $date,
        \Magento\Framework\Model\ResourceModel\AbstractResource $resource = null,
        \Magento\Framework\Data\Collection\AbstractDb $resourceCollection = null,
        array $data = []
    ) {
        parent::__construct(
            $context,
            $registry,
            $extensionFactory,
            $customAttributeFactory,
            $paymentData,
            $scopeConfig,
            $logger,
            $resource,
            $resourceCollection,
            $data
        );

        $this->api = $api;
        $this->invoiceService = $invoiceService;
        $this->customer = $customer;
        $this->product = $product;
        $this->bill = $bill;
        $this->profile = $profile;
        $this->paymentMethod = $paymentMethod;
        $this->psrLogger = $psrLogger;
        $this->date = $date;
    }

    /**
     * @return string
     */
    abstract protected function getPaymentMethodCode();

    /**
     * Check whether payment method can be used
     *
     * @param \Magento\Quote\Api\Data\CartInterface|null $quote
     * @return bool
     */
    public function isAvailable(\Magento\Quote\Api\Data\CartInterface $quote = null)
    {
        return parent::isAvailable($quote);
    }

    /**
     * Assign data to info model instance
     *
     * @param mixed $data
     *
     * @return $this
     */
    public function assignData(DataObject $data)
    {
        parent::assignData($data);
        return $this;
    }

    /**
     * Validate payment method information object
     *
     * @return $this
     */
    public function validate()
    {
        parent::validate();
        return $this;
    }

    /**
     * Authorize payment abstract method
     *
     * @param \Magento\Framework\DataObject|InfoInterface $payment
     * @param float $amount
     * @throws \Magento\Framework\Exception\LocalizedException
     * @return $this|string
     */
    public function authorize(\Magento\Payment\Model\InfoInterface $payment, $amount)
    {
        parent::authorize($payment, $amount);
        $this->processPayment($payment, $amount);
    }

    /**
     * Capture payment abstract method
     *
     * @param \Magento\Framework\DataObject|InfoInterface $payment
     * @param float $amount
     * @throws \Magento\Framework\Exception\LocalizedException
     * @return $this|string
     */
    public function capture(\Magento\Payment\Model\InfoInterface $payment, $amount)
    {
        parent::capture($payment, $amount);
        $this->processPayment($payment, $amount);
    }

    /**
     * @param \Magento\Framework\DataObject|InfoInterface $payment
     * @param float $amount
     * @throws \Magento\Framework\Exception\LocalizedException
     * @return $this|string
     */
    protected function processPayment(\Magento\Payment\Model\InfoInterface $payment, $amount)
    {
        /** @var \Magento\Sales\Model\Order $order */
        $order = $payment->getOrder();
        $customer = $this->customer->getNode($order);
        $total = $this->product->getTotalAmount($order);
        $total = number_format($total,2,'','');
        $total = (int) $total;
        $body = [
            'myId'=> $order->getIncrementId(),
            'Customer' => $customer,
            'mainPaymentMethodId' => $this->getPaymentMethodCode(),
            'value' => $total,
            'payday' => date('Y-m-d')
        ];

        $helperData = $this->profile->helperData;

        if ($body['mainPaymentMethodId'] === PaymentMethod::CREDIT_CARD) {
            $body['PaymentMethodCreditCard'] = $this->profile->getNodeCard($payment);
            if ($installments = $payment->getAdditionalInformation('installments')) {
                $body['PaymentMethodCreditCard']['qtdInstallments'] = (int)$installments;
            }
        } else if ($body['mainPaymentMethodId'] === PaymentMethod::PIX) {
            $body['PaymentMethodPix']['instructions'] = 'Pedido '.$order->getIncrementId();
            $body['PaymentMethodPix']['Deadline']['type'] = $helperData->getTypeTimePix();
            $body['PaymentMethodPix']['Deadline']['value'] = $helperData->getQtdTimeToPayPix();
        } else if ($body['mainPaymentMethodId'] === PaymentMethod::BOLETO) {
            $body['PaymentMethodBoleto']['deadlineDays'] = $helperData->getDaysBoleto();
            $body['PaymentMethodBoleto']['instructions'] = 'Pedido '.$order->getIncrementId();
        }
        
        if ($bill = $this->bill->create($body)) {
            if ($body['mainPaymentMethodId'] === PaymentMethod::BOLETO) {
                $transaction = $bill['Transactions']['0'];
                $payment->setAdditionalInformation('print_url', $transaction['Boleto']['pdf']);
                $payment->setAdditionalInformation('due_at', $transaction['payday']);
            }
        
            if ($body['mainPaymentMethodId'] === PaymentMethod::PIX) {
                $transaction = $bill['Transactions']['0'];
                $payment->setAdditionalInformation('print_url', $transaction['Pix']['page']);
                $payment->setAdditioPix('due_at', $transaction['payday']);
                
            }

            if (
                $body['mainPaymentMethodId'] === PaymentMethod::BOLETO
                || $body['mainPaymentMethodId'] === PaymentMethod::PIX
                || $bill['Transactions']['0']['status'] === Bill::PAID_STATUS
                || $bill['Transactions']['0']['status'] === Bill::REVIEW_STATUS
            ) {
                $order->setGalaxPayBillId($bill['galaxPayId']);
                return $bill['galaxPayId'];
            }
            $this->bill->delete($bill['galaxPayId']);
        }

        $this->psrLogger->error(__(sprintf('Error on order payment %d.', $order->getId())));
        $message = __('There has been a payment confirmation error. Verify data and try again');
        $order->setState(\Magento\Sales\Model\Order::STATE_CANCELED)
            ->setStatus($order->getConfig()->getStateDefaultStatus(\Magento\Sales\Model\Order::STATE_CANCELED))
            ->addStatusHistoryComment($message->getText());
        throw new \Magento\Framework\Exception\LocalizedException($message);

        return $this;
    }
}
