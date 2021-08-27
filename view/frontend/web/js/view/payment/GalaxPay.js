define(
    [
        'uiComponent',
        'Magento_Checkout/js/model/payment/renderer-list'
    ],
    function (
        Component,
        rendererList
    ) {
        'use strict';
        rendererList.push(
            {
                type: 'GalaxPay',
                component: 'GalaxPay_Payment/js/view/payment/method-renderer/GalaxPay-cc'
            },
            {
                type: 'GalaxPay_boleto',
                component: 'GalaxPay_Payment/js/view/payment/method-renderer/GalaxPay-boleto'
            },
            {
                type: 'GalaxPay_pix',
                component: 'GalaxPay_Payment/js/view/payment/method-renderer/GalaxPay-pix'
            }
        );
        return Component.extend({});
    }
);