<?php

namespace GalaxPay\Payment\Model\Source; 

class TypeTimePix implements \Magento\Framework\Option\ArrayInterface
{
 
    public function toOptionArray()
    {
        $options[] = ['label' => '-- Please Select --', 'value' => ''];
        $options[] = [
            'label' => __('Minutes'),
            'value' => 'minutes',
        ];
        $options[] = [
            'label' => __('Days'),
            'value' => 'days',
        ];


        return $options;
    }
}
