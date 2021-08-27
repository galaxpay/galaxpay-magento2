define(
    [
        'underscore',
        'Magento_Checkout/js/view/payment/default',
        'mage/translate',
        'jquery',
        'mageUtils'
    ],
    function (_, Component, $t, $, utils) {
        'use strict';
        return Component.extend({
            defaults: {
                template: 'GalaxPay_Payment/payment/GalaxPay-pix'
            }
        });
    }
);
