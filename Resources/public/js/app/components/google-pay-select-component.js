import _ from 'underscore';
import mediator from 'oroui/js/mediator';
import BaseComponent from 'oroui/js/app/components/base/component';

const GooglePaySelectComponent = BaseComponent.extend({

    /**
     * @property {Object}
     */
    options: {
        paymentMethod: null,
        merchantId: null,
        gatewayMerchantId: null,
        isProduction: true,
        selector: '.tpay-google-pay',
        transactionInfo: {
            countryCode: 'PL',
            currencyCode: 'PLN',
            totalPriceStatus: 'FINAL',
            totalPrice: '1.00'
        },
    },

    googlePay: {
        isProduction: true,
        paymentsClient: null,
        baseRequest: {
            apiVersion: 2,
            apiVersionMinor: 0
        },
        tokenizationSpecification: {
            type: "PAYMENT_GATEWAY",
            parameters: {
                gateway: "tpaycom",
                gatewayMerchantId: "{MERCHANT_ID}",
            },
        },
        allowedCardNetworks: ["MASTERCARD", "VISA"],
        allowedCardAuthMethods: ["PAN_ONLY", "CRYPTOGRAM_3DS"],
    },

    loadedScript: false,

    /**
     * @property {Boolean}
     */
    paymentValidationRequiredComponentState: false,

    /**
     * @property {Boolean}
     */
    disposable: true,


    /**
     * @inheritdoc
     */
    constructor: function GooglePaySelectComponent(options) {
        GooglePaySelectComponent.__super__.constructor.call(this, options);
    },

    checkIfAvailable() {
        if (typeof (window.google) !== 'undefined' && typeof (window.google.payments) !== 'undefined') {
            this.onGooglePayLoaded();
        } else {
            this.loadGoogleApiScript(this.googlePay.apiScript);
        }
    },

    processOptions(options) {
        this.options = _.extend({}, this.options, options);
        this.googlePay = _.extend(this.googlePay, {
            isProduction: this.options.isProduction,
            tokenizationSpecification: {
                parameters: {
                    gatewayMerchantId: this.options.gatewayMerchantId,
                }
            }
        });
    },

    /**
     * @inheritdoc
     */
    initialize: function (options) {
        this.processOptions(options);

        mediator.on('checkout:payment:method:changed', this.onPaymentMethodChanged, this);
        mediator.on('checkout:payment:before-hide-filled-form', this.beforeHideFilledForm, this);
        mediator.on('checkout:payment:before-restore-filled-form', this.beforeRestoreFilledForm, this);
        mediator.on('checkout:payment:remove-filled-form', this.removeFilledForm, this);
        mediator.on('checkout-content:initialized', this.refreshPaymentMethod, this);

        this.checkIfAvailable();
    },

    loadGoogleApiScript: function (src) {
        const loadGoogleApi = new Promise(resolve => {
            const element = document.createElement("script");
            element.addEventListener("load", resolve, { once : true });
            element.src = "https://pay.google.com/gp/p/js/pay.js"
            document.head.appendChild(element);
        });

        loadGoogleApi.then(() => {
            this.onGooglePayLoaded()
        });
    },

    getAllowedPaymentMethods() {
        return {
            type: 'CARD',
            parameters: {
                allowedAuthMethods: this.googlePay.allowedCardAuthMethods,
                allowedCardNetworks: this.googlePay.allowedCardNetworks
            }
        };
    },

    getGoogleIsReadyToPayRequest: function () {
        return Object.assign(
            {},
            this.googlePay.baseRequest,
            {
                allowedPaymentMethods: [this.getAllowedPaymentMethods()]
            }
        );
    },

    onGooglePayLoaded: function () {
        const googlePayChoice = $(this.options.selector);
        const paymentsClient = this.getGooglePaymentsClient();
        paymentsClient.isReadyToPay(this.getGoogleIsReadyToPayRequest())
            .then(function (response) {
                if (response.result) {
                    googlePayChoice.removeClass('hidden');
                } else {
                    googlePayChoice.addClass('hidden');
                }
            })
            .catch(function (err) {
                googlePayChoice.addClass('hidden');
                console.error(err);
            });
    },

    refreshPaymentMethod: function () {
        mediator.trigger('checkout:payment:method:refresh');
        this.checkIfAvailable();
    },

    getGooglePaymentsClient: function () {
        if (this.googlePay.paymentsClient === null) {
            this.googlePay.paymentsClient = new google.payments.api.PaymentsClient({environment: this.googlePay.isProduction ? 'PRODUCTION' : 'TEST'});
        }
        return this.googlePay.paymentsClient;
    },
});

export default GooglePaySelectComponent;
