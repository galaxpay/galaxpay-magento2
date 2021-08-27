<?php

namespace GalaxPay\Payment\Model\Payment;

class Customer
{
    public function __construct(
        \Magento\Customer\Api\CustomerRepositoryInterface $customerRepository,
        Api $api,
        \Magento\Framework\Message\ManagerInterface $messageManager
    ) {
        $this->customerRepository = $customerRepository;
        $this->api = $api;
        $this->messageManager = $messageManager;
    }

    public function getNode($order)
    {
        $billing = $order->getBillingAddress();
        $customer = $this->customerRepository->get($billing->getEmail());
        //  $customer = $this->customerRepository->getById($order->getCustomerId());
        $zipCode = $billing->getPostcode();
        if (empty($zipCode)) {
            $zipCodeO =  $customer->getCustomAttribute('postcode');
            if (is_object($zipCode0)) {
                $zipCode = $zipCodeO->getValue();
            }
        }
        $zipCode = preg_replace('/[^0-9]/', '', $zipCode);

        $address = [
            'street' => $billing->getStreetLine(1) ?: '',
            'number' => $billing->getStreetLine(2) ?: '',
            'complement' => $billing->getStreetLine(4) ?: '',
            'neighborhood' => $billing->getStreetLine(3) ?: '',
            'zipCode' => $zipCode,
            'city' => $billing->getCity(),
            'state' => $billing->getRegionCode(),
            // 'country' => $billing->getCountryId(),
        ];
        $doc = $customer->getCustomAttribute('cpf');
        if(!is_object($doc)){
            $doc = $customer->getCustomAttribute('cnpj');
        }
        $doc = preg_replace('/[^0-9]/', '', $doc->getValue());
        $customerGalaxPay = [
            'name' => $billing->getFirstname() . ' ' . $billing->getLastname(),
            'emails' => [$billing->getEmail()],
            'document' => $doc,
            'phones' => [$this->formatPhone($billing->getTelephone())],
            'Address' => $address
        ];

        return $customerGalaxPay;
    }

    /**
     * Make an API request to create a Customer.
     *
     * @param array $body (name, email, code)
     *
     * @return array|bool|mixed
     */
    public function createCustomer($body)
    {
        if ($response = $this->api->request('customers', 'POST', $body)) {
            return $response['Customer'];
        }

        return false;
    }

    public function formatPhone($phone)
    {
        return preg_replace('/^0|\D+/', '', $phone);
    }

}