<?php

namespace GalaxPay\Payment\Helper\WebHookHandlers;

use GalaxPay\Payment\Model\Payment\Api;
use GalaxPay\Payment\Model\Payment\Bill;

class ChargeRejected
{
    private $bill, $order, $logger;

    public function __construct(
        Bill $bill,
        Order $order,
        \Psr\Log\LoggerInterface $logger
    ) {
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
    public function chargeRejected($data)
    {
        $charge = $data['Charge'];

        if (!($order = $this->getOrderFromBill($charge['myId']))) {
            $this->logger->warning(__('Order not found'));

            return false;
        }

        //$gatewayMessage = 'Motive: '.$charge['Transaction']['status'];
        $gatewayMessage = '';//nao temos o motivo na api por enquanto

        $order->addStatusHistoryComment(__(sprintf(
                    'Payment rejected. "%s"',
                    $gatewayMessage
                )));
                $order->setState(\Magento\Sales\Model\Order::STATE_CANCELED, true, __(sprintf(
                    'All payment tries were rejected. "%s".',
                    $gatewayMessage
                )), true);
                $order->setStatus(\Magento\Sales\Model\Order::STATE_CANCELED, true, __(sprintf(
                    'All payment tries were rejected. "%s".',
                    $gatewayMessage
                )), true);
                $this->logger->info(__(sprintf(
                    'All payment tries were rejected. "%s".',
                    $order->getId(),
                    $gatewayMessage
                )));

        $order->save();

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
