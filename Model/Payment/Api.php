<?php

namespace GalaxPay\Payment\Model\Payment;

use Magento\Framework\Module\ModuleListInterface;
use GalaxPay\Payment\Helper\Data;

class Api extends \Magento\Framework\Model\AbstractModel
{
    private $apiHash, $apiId;

    public function __construct(
        Data $helperData,
        ModuleListInterface $moduleList,
        \Psr\Log\LoggerInterface $logger,
        \Magento\Framework\Message\ManagerInterface $messageManager
    ) {

        $this->apiHash = $helperData->getModuleGeneralConfig("api_hash");
        $this->apiId = $helperData->getModuleGeneralConfig("api_id");
        $this->base_path = $helperData->getBaseUrl();

        $this->moduleList = $moduleList;
        $this->logger = $logger;
        $this->messageManager = $messageManager;
    }

    public function request($endpoint, $method = 'POST', $data = [], $dataToLog = null)
    {
        if (!$this->apiHash) {
            return false;
        }
        if (!$this->apiId) {
            return false;
        }
        $requestToken = $this->getTokenToUse();

        $url = $this->base_path . $endpoint;
        $body = json_encode($data);
        $requestId = number_format(microtime(true), 2, '', '');
        //nao gravar no log dados de cartao
        unset($dataToLog['PaymentMethodCreditCard']['Card']['number']);
        unset($dataToLog['PaymentMethodCreditCard']['Card']['holder']);
        unset($dataToLog['PaymentMethodCreditCard']['Card']['expiresAt']);
        unset($dataToLog['PaymentMethodCreditCard']['Card']['cvv']);
        unset($body['PaymentMethodCreditCard']['Card']['number']);
        unset($body['PaymentMethodCreditCard']['Card']['holder']);
        unset($body['PaymentMethodCreditCard']['Card']['expiresAt']);
        unset($body['PaymentMethodCreditCard']['Card']['cvv']);
        $dataToLog = null !== $dataToLog ? json_encode($dataToLog) : $body;
        $this->logger->info(__(sprintf(
            '[Request #%s]: New Api Request.\n%s %s\n%s',
            $requestId,
            $method,
            $url,
            $dataToLog
        )));
        $ch = curl_init();
        $ch_options = [
            CURLOPT_HTTPHEADER => [
                'Authorization: Bearer ' . $requestToken,
                'Content-Type application/json',
            ],
            CURLOPT_TIMEOUT => 60,
            CURLOPT_SSL_VERIFYPEER => true,
            CURLOPT_HEADER => true,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_USERAGENT => 'GalaxPay-Magento2/' . $this->getVersion(),
            CURLOPT_SSLVERSION => 'CURL_SSLVERSION_TLSv1_2',
            CURLOPT_URL => $url,
            CURLOPT_CUSTOMREQUEST => $method
        ];
        if (!empty($body)) {
            $ch_options[CURLOPT_POSTFIELDS] = $body;
        }
        curl_setopt_array($ch, $ch_options);
        $response = curl_exec($ch);
        $statusCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $body = substr($response, curl_getinfo($ch, CURLINFO_HEADER_SIZE));
        if (curl_errno($ch) || $response === false) {
            $this->logger->error(
                __(sprintf('[Request #%s]: Error while executing request!\n%s', $requestId, print_r($response, true)))
            );
            curl_close($ch);
            return false;
        }
        curl_close($ch);
        $status = "HTTP Status: $statusCode";
        $this->logger->info(__(sprintf('[Request #%s]: New API Answer.\n%s\n%s', $requestId, $status, $body)));
        $responseBody = json_decode($body, true);
        if (!$responseBody) {
            $this->logger->info(__(sprintf(
                '[Request #%s]: Error while recovering request body! %s',
                $requestId,
                print_r($body, true)
            )));
            return false;
        }
        if (!$this->checkResponse($responseBody, $endpoint)) {
            return false;
        }
        return $responseBody;
    }




    public function getTokenToUse()
    {
        $tokenSaved = isset($_SESSION['tokenSavedGalaxPay']) ?  $_SESSION['tokenSavedGalaxPay'] : null;
        if (!empty($tokenSaved) && !$this->isTokenExpired()) {
            return $tokenSaved;
        }
        $url = $this->base_path . 'token';
        $data = array(
            'grant_type' => 'authorization_code',
            'scope' => 'payment-methods.read customers.read customers.write plans.read plans.write transactions.read transactions.write webhooks.write cards.read cards.write card-brands.read subscriptions.read subscriptions.write charges.read charges.write boletos.read'
        );
        $body = json_encode($data);
        $requestId = number_format(microtime(true), 2, '', '');
        $this->logger->info(__(sprintf(
            '[Request #%s]: New Api Request.\n%s %s\n%s',
            $requestId,
            'post',
            $url,
            $body
        )));
        $ch = curl_init();
        $ch_options = [
            CURLOPT_HTTPHEADER => [
                'Authorization: Basic ' . base64_encode($this->apiId . ':' . $this->apiHash),
                'Content-Type: application/json',
            ],
            CURLOPT_TIMEOUT => 60,
            CURLOPT_SSL_VERIFYPEER => true,
            CURLOPT_HEADER => true,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_USERAGENT => 'GalaxPay-Magento2/' . $this->getVersion(),
            CURLOPT_SSLVERSION => 'CURL_SSLVERSION_TLSv1_2', 
            CURLOPT_URL => $url,
            CURLOPT_CUSTOMREQUEST => 'POST'
        ];
        if (!empty($body)) {
            $ch_options[CURLOPT_POSTFIELDS] = $body;
        }
        curl_setopt_array($ch, $ch_options);
        $response = curl_exec($ch);
        $statusCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $body = substr($response, curl_getinfo($ch, CURLINFO_HEADER_SIZE));
        if (curl_errno($ch) || $response === false) {
            $this->logger->error(
                __(sprintf('[Request #%s]: Error while executing request!\n%s', $requestId, print_r($response, true)))
            );
            curl_close($ch);
            return false;
        }
        curl_close($ch);
        $status = "HTTP Status: $statusCode";
        $this->logger->info(__(sprintf('[Request #%s]: New API Answer.\n%s\n%s', $requestId, $status, $body)));
        $responseBody = json_decode($body, true);
        if (!$responseBody) {
            return false;
        }
        $_SESSION['tokenSavedGalaxPay'] = $responseBody['access_token'];
        $_SESSION['tokenSavedExpiresInGalaxPay'] = $responseBody['expires_in'];
        $_SESSION['tokenSavedCreatedAtGalaxPay'] = date('Y-m-d H:i:s');
        return $responseBody['access_token'];
    }

    private function isTokenExpired()
    {
        if (!isset($_SESSION['tokenSavedExpiresInGalaxPay'])) {
            return true;
        }
        if (!isset($_SESSION['tokenSavedCreatedAtGalaxPay'])) {
            return true;
        }
        $seconds = $_SESSION['tokenSavedExpiresInGalaxPay'] - 60; //margem de seguranca de 1 minuto
        $date_now = $_SESSION['tokenSavedCreatedAtGalaxPay'];

        $validUntil =  date("Y-m-d H:i:s", (strtotime(date($date_now)) + $seconds));
        $now = date('Y-m-d H:i:s');
        if ($validUntil <= $now) {
            return true;
        }
        return false;
    }



    public function getVersion()
    {
        return $this->moduleList
            ->getOne('GalaxPay_Payment')['setup_version'];
    }

    /**
     * @param array $response
     * @param       $endpoint
     *
     * @return bool
     */
    private function checkResponse($response, $endpoint)
    {
        if (isset($response['errors']) && !empty($response['errors'])) {
            foreach ($response['errors'] as $error) {
                $message = $this->getErrorMessage($error, $endpoint);

                $this->messageManager->addErrorMessage($message);

                $this->lastError = $message;
            }

            return false;
        }

        $this->lastError = '';

        return true;
    }

    /**
     * @param array $error
     * @param       $endpoint
     *
     * @return string
     */
    private function getErrorMessage($error, $endpoint)
    {
        return "Erro em $endpoint: {$error['id']}: {$error['parameter']} - {$error['message']}";
    }
}
