<?php
 \Magento\Framework\Component\ComponentRegistrar::register(
    \Magento\Framework\Component\ComponentRegistrar::MODULE,
    'GalaxPay_Payment',
    __DIR__
); 


spl_autoload_register(function ($name) {
    if(strpos($name,'GalaxPay') === false){
        return;
    }
    $name = str_replace('\\','/',$name);
    $name = str_replace('GalaxPay/Payment/','',$name);
    if(!file_exists('/home2/magento/public_html/app/code/galaxpay/galaxpay-magento2/'.$name.'.php')){
        return;
    }
    require_once '/home2/magento/public_html/app/code/galaxpay/galaxpay-magento2/'.$name.'.php'; 
});