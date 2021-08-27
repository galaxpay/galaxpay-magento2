<?php

namespace GalaxPay\Payment\Model;

use \Magento\Config\Model\Config\CommentInterface;

class TimePix implements CommentInterface
{
    public function __construct(
        \Magento\Framework\UrlInterface $urlInterface
    ) {
        $this->urlInterface = $urlInterface;
    }

    public function getCommentText($elementValue)
    {
        return sprintf(
            __("Minutes can vary from 0 to 1440. Days can be from 0 to 90.")
        );
    }
}
