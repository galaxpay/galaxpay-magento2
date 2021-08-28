<?php

namespace GalaxPay\Payment\Model;

use \Magento\Framework\Option\ArrayInterface;

class TypeTimePix implements OptionSourceInterface
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
