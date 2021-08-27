<?php

namespace GalaxPay\Payment\Controller\Index;

use GalaxPay\Payment\Helper\Data;
use GalaxPay\Payment\Helper\WebhookHandler;
use GalaxPay\Payment\Model\Api;

class Webhook extends \Magento\Framework\App\Action\Action
{
    protected $_pageFactory;
    private $webhookHandler;

    public function __construct(
        \GalaxPay\Payment\Model\Payment\Api $api,
        \Psr\Log\LoggerInterface $logger,
        WebhookHandler $webhookHandler,
        Data $helperData,
        \Magento\Framework\App\Action\Context $context,
        \Magento\Framework\View\Result\PageFactory $pageFactory
    ) {
        $this->api = $api;
        $this->logger = $logger;
        $this->_pageFactory = $pageFactory;
        $this->webhookHandler = $webhookHandler;
        $this->helperData = $helperData;
        return parent::__construct($context);
    }

    /**
     * The route that webhooks will use.
     */
    public function execute()
    {
        if (!$this->validateRequest()) {
            $ip = $this->webhookHandler->getRemoteIp();

            $this->logger->error(__(sprintf('Invalid webhook attempt from IP %s', $ip)));

            return;
        }

        $body = file_get_contents('php://input');
        $this->logger->info(__(sprintf("Webhook New Event!\n%s", $body)));

        $this->webhookHandler->handle($body);
    }

    /**
     * Validate the webhook for security reasons.
     *
     * @return bool
     */
    private function validateRequest()
    {
        return true;
        $systemKey = $this->helperData->getWebhookKey();
        $requestKey = $this->getRequest()->getParam('key');

        return $systemKey === $requestKey;
    }
}
