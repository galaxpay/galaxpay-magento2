<?php

namespace GalaxPay\Payment\Model\Payment;

class Bill
{
    private $api;
    const PAID_STATUS = 'captured';
     const REVIEW_STATUS = 'authorized';

    public function __construct(Api $api)
    {
        $this->api = $api;
    }

    /**
     * @param array $body
     *
     * @return int|bool
     */
    public function create($body)
    {
        if ($response = $this->api->request('charges', 'POST', $body)) {
            return $response['Charge'];
        }

        return false;
    }

    /**
     * @param $billId
     */
    public function delete($billId)
    {
        $this->api->request("charges/{$billId}/galaxPayId", 'DELETE');
    }

    /**
     * @param $billId
     *
     * @return array|bool
     */
    public function getBill($billId)
    {
        $response = $this->api->request("charges?limit=1&startAt=0&galaxPayIds={$billId}", 'GET');

        if (! $response || ! isset($response['Charges']['0'])) {
            return false;
        }

        return $response['Charges']['0'];
    }
}
