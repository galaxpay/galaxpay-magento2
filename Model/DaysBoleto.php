<?php

namespace GalaxPay\Payment\Model;

use \Magento\Config\Model\Config\CommentInterface;

class DaysBoleto implements CommentInterface
{
    public function __construct(
        \Magento\Framework\UrlInterface $urlInterface
    ) {
        $this->urlInterface = $urlInterface;
    }

    public function getCommentText($elementValue)
    {
        return sprintf(
            __("0 = the day of the purchase, 1 = one day after the purchase.. and so on")
        );
    }
}
