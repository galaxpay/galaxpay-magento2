<?php

namespace GalaxPay\Payment\Helper\WebHookHandlers;

class BillCanceled
{
    /**
     * @var \GalaxPay\Payment\Model\Payment\Bill
     */
    protected $bill;

    /**
     * @var \GalaxPay\Payment\Helper\WebHookHandlers\Order
     */
    protected $order;

    /**
     * @var \Psr\Log\LoggerInterface
     */
    protected $logger;

    /**
     * @var \Magento\Sales\Api\OrderRepositoryInterface
     */
    protected $orderRepository;

    public function __construct(
        \Magento\Sales\Api\OrderRepositoryInterface $orderRepository,
        \GalaxPay\Payment\Model\Payment\Bill $bill,
        \GalaxPay\Payment\Helper\WebHookHandlers\Order $order,
        \Psr\Log\LoggerInterface $logger
    ) {
        $this->orderRepository = $orderRepository;
        $this->bill = $bill;
        $this->order = $order;
        $this->logger = $logger;
    }

    /**
     * @param array $data
     *
     * @return bool
     * @throws \Exception
     */
    public function billCanceled($data)
    {
        $bill = $data['Charge'];

        if (!$bill) {
            $this->logger->error(__('Error while interpreting webhook "bill_canceled"'));
            return false;
        }

        /** @var \Magento\Sales\Model\Order $order */
        if (!($order = $this->getOrderFromBill($bill['myId']))) {
            $this->logger->warning(__('Order not found'));
            return false;
        }

        $order->cancel();
        $order->addStatusHistoryComment(__(sprintf(
            'GalaxPay API: Order %s Canceled.',
            $order->getId()
        )));
        $this->orderRepository->save($order);

        $this->logger->info(__(sprintf(
            'GalaxPay API: Order %s Canceled.',
            $order->getId()
        )));

        return true;
    }

    private function getOrderFromBill($billId)
    {
        $bill = $this->bill->getBill($billId);

        if (!$bill) {
            return false;
        }

        return $this->order->getOrder($billId);
    }
}
