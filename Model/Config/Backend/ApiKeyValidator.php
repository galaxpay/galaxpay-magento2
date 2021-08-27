<?php

namespace GalaxPay\Payment\Model\Config\Backend;

use Magento\Framework\App\Cache\TypeListInterface;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\App\Config\Value as ConfigValue;
use Magento\Framework\Data\Collection\AbstractDb;
use Magento\Framework\Model\Context;
use Magento\Framework\Model\ResourceModel\AbstractResource;
use Magento\Framework\Registry;
use Magento\Framework\Serialize\SerializerInterface;
use GalaxPay\Payment\Helper\Data;
use GalaxPay\Payment\Model\Payment\Api;

class ApiKeyValidator extends ConfigValue
{
    /**
     * Json Serializer
     *
     * @var SerializerInterface
     */
    protected $serializer;

    /**
     * @param SerializerInterface $serializer
     * @param Context $context
     * @param Registry $registry
     * @param ScopeConfigInterface $config
     * @param TypeListInterface $cacheTypeList
     * @param AbstractResource|null $resource
     * @param AbstractDb|null $resourceCollection
     * @param array $data
     */
    public function __construct(
        Data $helperData,
        Api $api,
        SerializerInterface $serializer,
        Context $context,
        Registry $registry,
        ScopeConfigInterface $config,
        TypeListInterface $cacheTypeList,
        AbstractResource $resource = null,
        AbstractDb $resourceCollection = null,
        array $data = []
    ) {
        $this->serializer = $serializer;
        $this->helperData = $helperData;
        $this->api = $api;
        parent::__construct($context, $registry, $config, $cacheTypeList, $resource, $resourceCollection, $data);
    }

    /**
     * Prepare data before save
     *
     * @return void
     */
    public function beforeSave()
    {
        $apiKey = $this->helperData->getModuleGeneralConfig("api_hash");
        $apiId = $this->helperData->getModuleGeneralConfig("api_id");
        $value = $this->getValue();

        if ($value) {
            if (!$apiKey) {
                throw new \Magento\Framework\Exception\LocalizedException(
                    __("The api key was not set on the module basic configuration")
                );
            }

            if (!$apiId) {
                throw new \Magento\Framework\Exception\LocalizedException(
                    __("The api id was not set on the module basic configuration")
                );
            }
        }
    }

    /**
     * Process data after load
     *
     * @return void
     */
    protected function _afterLoad()
    {
        return;
        /** @var string $value */
        $value = $this->getValue();
        $decodedValue = $this->serializer->unserialize($value);

        $this->setValue($decodedValue);
    }
}

