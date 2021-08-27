<?php

namespace GalaxPay\Payment\Plugin;

class SetOrderStatusOnPlace
{
    public function afterPlace(\Magento\Sales\Model\Order\Payment $subject, $result)
    {
        if ($subject->getMethod() == \GalaxPay\Payment\Model\Payment\Boleto::CODE) {
            $order = $subject->getOrder();
            $order->setState('new')
                ->setStatus('pending');
        }
        if ($subject->getMethod() == \GalaxPay\Payment\Model\Payment\Pix::CODE) {
            $order = $subject->getOrder();
            $order->setState('new')
                ->setStatus('pending');
        }
        return $result;
    }
}