<?php

namespace GalaxPay\Payment\Model;

use \Magento\Config\Model\Config\CommentInterface;

class WebhookKey implements CommentInterface
{
    public function __construct(
        \Magento\Framework\UrlInterface $urlInterface
    ) {
        $this->urlInterface = $urlInterface;
    }

    public function getCommentText($elementValue)
    {
        return sprintf(
            __("Esse hash é gerado automaticamente")
        ) .
        " <strong>" .
        $this->urlInterface->getBaseUrl() .
        "GalaxPayPayment/index/webhook?key=" .
        $elementValue .
        "</strong>";
    }
}
