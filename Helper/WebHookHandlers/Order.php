<?php

namespace GalaxPay\Payment\Helper\WebHookHandlers;

class Order
{
    /**
     * @var \Psr\Log\LoggerInterface
     */
    protected $logger;

    /**
     * @var \Magento\Sales\Model\ResourceModel\Order\CollectionFactory
     */
    protected $orderCollectionFactory;

    public function __construct(
        \Psr\Log\LoggerInterface $logger,
        \Magento\Sales\Model\ResourceModel\Order\CollectionFactory $orderCollectionFactory
    ) {
        $this->logger = $logger;
        $this->orderCollectionFactory = $orderCollectionFactory;
    }

    /**
     * @param array $data
     * @return \Magento\Sales\Model\Order
     */
    public function getOrder($data)
    {
        if (!isset($data['Charge'])) {
            return false;
        }

        $order = $this->getOrderByBillId($data['Charge']['myId']);

        if (!$order || !$order->getId()) {
            $this->logger->warning(__(sprintf('No order was found to invoice: %d', $data['Charge']['myId'])));

            return false;
        }

        return $order;
    }

    /**
     * @param int $billId
     *
     * @return \Magento\Sales\Model\Order
     */
    private function getOrderByBillId($billId)
    {
        if (!$billId) {
            return false;
        }

        $order = $this->orderCollectionFactory->create()
            ->addAttributeToFilter('increment_id', ['eq' => $billId])
            ->getFirstItem();
        // $order = $this->orderCollectionFactory->get($billId);

        if (!$order) {
            return false;
        }

        return $order;
    }
}
