<?php

namespace GalaxPay\Payment\Helper;


class WebhookHandler
{
    /**
     * @var \Magento\Framework\HTTP\PhpEnvironment\RemoteAddress
     */
    protected $remoteAddress;

    /**
     * @var \Psr\Log\LoggerInterface
     */
    protected $logger;

    /**
     * @var \GalaxPay\Payment\Helper\WebHookHandlers\BillCreated
     */
    protected $billCreated;

    /**
     * @var \GalaxPay\Payment\Helper\WebHookHandlers\BillPaid
     */
    protected $billPaid;

    /**
     * @var \GalaxPay\Payment\Helper\WebHookHandlers\ChargeRejected
     */
    protected $chargeRejected;

    /**
     * @var \GalaxPay\Payment\Helper\WebHookHandlers\BillCanceled
     */
    protected $billCanceled;

    public function __construct(
        \Magento\Framework\HTTP\PhpEnvironment\RemoteAddress $remoteAddress,
        \Psr\Log\LoggerInterface $logger,
        \GalaxPay\Payment\Helper\WebHookHandlers\BillCreated $billCreated,
        \GalaxPay\Payment\Helper\WebHookHandlers\BillPaid $billPaid,
        \GalaxPay\Payment\Helper\WebHookHandlers\ChargeRejected $chargeRejected,
        \GalaxPay\Payment\Helper\WebHookHandlers\BillCanceled $billCanceled
    ) {
        $this->remoteAddress = $remoteAddress;
        $this->logger = $logger;
        $this->billCreated = $billCreated;
        $this->billPaid = $billPaid;
        $this->chargeRejected = $chargeRejected;
        $this->billCanceled = $billCanceled;
    }

    public function getRemoteIp()
    {
        return $this->remoteAddress->getRemoteAddress();
    }

    /**
     * Handle incoming webhook.
     *
     * @param string $body
     *
     * @return bool
     */
    public function handle($body)
    {
        try {
            $jsonBody = json_decode($body, true);

            if (!$jsonBody || !isset($jsonBody['event'])) {
                throw new \Magento\Framework\Exception\LocalizedException(__('Webhook event not found!'));
            }

            $type = $jsonBody['event'];
            $data = $jsonBody;
        } catch (\Exception $e) {
            $this->logger->info(__(sprintf('Fail when interpreting webhook JSON: %s', $e->getMessage())));
            return false;
        }
        if($type != 'transaction.updateStatus'){
            $this->logger->warning(__(sprintf('Webhook event ignored by plugin: "%s".', $type)));
            return;
        }
        if(isset($jsonBody['Subscription'])){
            $this->logger->warning(__(sprintf('Webhook event ignored by plugin, subscription: "%s".', $type)));
            return;
        }
        $status = $jsonBody['Transaction']['status'];
        $this->logger->info(__(sprintf('Status: %s', $status)));

        if(in_array($status,['notSend','pendingBoleto','pendingPix'])){
            $this->logger->info('billCreated');
            return $this->billCreated->billCreated($data);
        }
        if(in_array($status,['captured','payedBoleto','authorized','payExternal','payedPix'])){
             $this->logger->info('billPaid');
            return $this->billPaid->billPaid($data);
        }
        if(in_array($status,['denied'])){
                 $this->logger->info('denied');
            return $this->chargeRejected->chargeRejected($data);
        }
        if(in_array($status,['cancel','notCompensated','reversed','cancelByContract','unavailablePix'])){
             $this->logger->info('billCanceled');
            return $this->billCanceled->billCanceled($data);
        }

    }
}
