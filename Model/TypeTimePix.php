<?php

namespace GalaxPay\Payment\Model;

use Magento\Catalog\Model\ResourceModel\Category\CollectionFactory;
use Magento\Framework\Data\OptionSourceInterface;

class TypeTimePix implements OptionSourceInterface
{
    /**
     * @var CollectionFactory
     */
    protected $collectionFactory;

    public function __construct(
        CollectionFactory $collectionFactory
    ) {
        $this->collectionFactory = $collectionFactory;
    }

    public function toOptionArray()
    {
        $options[] = ['label' => '-- Please Select --', 'value' => ''];
        $options[] = [
            'label' => 'Minutos',
            'value' => 'minutes',
        ];
        $options[] = [
            'label' => 'Dias',
            'value' => 'days',
        ];


        return $options;
    }
}
