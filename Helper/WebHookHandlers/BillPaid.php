<?php

namespace GalaxPay\Payment\Helper\WebHookHandlers;

use Magento\Sales\Model\Order\Invoice;
use GalaxPay\Payment\Helper\Data;

class BillPaid
{
    /**
     * @var \Magento\Sales\Api\OrderRepositoryInterface
     */
    protected $orderRepository;

    /**
     * @var \Magento\Sales\Api\InvoiceRepositoryInterface
     */
    protected $invoiceRepository;

    public function __construct(
        \Psr\Log\LoggerInterface $logger,
        \Magento\Sales\Api\OrderRepositoryInterface $orderRepository,
        \Magento\Sales\Api\InvoiceRepositoryInterface $invoiceRepository,
        Order $order,
        Data $helperData
    ) {
        $this->logger = $logger;
        $this->orderRepository = $orderRepository;
        $this->invoiceRepository = $invoiceRepository;
        $this->order = $order;
        $this->helperData = $helperData;
    }

    /**
     * Handle 'bill_paid' event.
     * The bill can be related to a subscription or a single payment.
     *
     * @param array $data
     *
     * @return bool
     */
    public function billPaid($data)
    {
        $this->logger->error('enter billpaid');
        $this->logger->error(json_encode($data));
        if (!($order = $this->order->getOrder($data))) {
            $this->logger->error('false billpaid');
            return false;
        }

        return $this->createInvoice($order);
    }

    /**
     * @return bool
     */
    public function createInvoice(\Magento\Sales\Model\Order $order)
    {
        $this->logger->error('enter billpaid createInvoice');
        $this->logger->info(__('id ' . $order->getId()));
        if (!$order->getId()) {
            $this->logger->info('no id billpaid');
            return false;
        }

        $this->logger->info(__(sprintf('Generating invoice for the order %s.', $order->getId())));

        if (!$order->canInvoice()) {

            $this->logger->error(__(sprintf('Impossible to generate invoice for order %s.', $order->getId())));

            return false;
        }
        
        $invoice = $order->prepareInvoice();
        $invoice->setRequestedCaptureCase(Invoice::CAPTURE_OFFLINE);
        $invoice->register();
        $invoice->setSendEmail(true);
        $this->invoiceRepository->save($invoice);
        $this->logger->info(__('Invoice created with success'));

        $order->addStatusHistoryComment(
            __('The payment was confirmed and the order is beeing processed')->getText(),
            $order->getConfig()->getStateDefaultStatus(\Magento\Sales\Model\Order::STATE_PROCESSING)
        );
        $this->logger->info('passed confirmed payment');
        $this->orderRepository->save($order);

        return true;
    }
}
