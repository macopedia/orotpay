import _ from 'underscore';
import mediator from 'oroui/js/mediator';
import BaseComponent from 'oroui/js/app/components/base/component';

const ApplePaySelectComponent = BaseComponent.extend({

    /**
     * @property {Object}
     */
    options: {
        paymentMethod: null,
        merchantId: null,
        selector: '.tpay-apple-pay',
        sessionEndpoint: '',
        methodData: [{
            "supportedMethods": "https://apple.com/apple-pay",
            "data": {
                "version": 3,
                "merchantIdentifier": '{merchantIdentifier}',
                "merchantCapabilities": [
                    "supports3DS"
                ],
                "supportedNetworks": [
                    "masterCard",
                    "visa"
                ],
                "countryCode": "PL"
            }
        }],
        paymentDetails: {
            "total": {
                "label": '{channelName}',
                "amount": {
                    "value": '{amount}',
                    "currency": '{currency}'
                }
            }
        },
        paymentOptions: {
            "requestPayerName": false,
            "requestBillingAddress": false,
            "requestPayerEmail": false,
            "requestPayerPhone": false,
            "requestShipping": false,
            "shippingType": "shipping"
        }
    },

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
    constructor: function ApplePaySelectComponent(options) {
        ApplePaySelectComponent.__super__.constructor.call(this, options);
    },

    checkIfAvailable() {
        if (window.ApplePaySession) {
            this.onApplePayLoaded();
        } else {
            this.loadApplePayScript();
        }
    },

    /**
     * @inheritdoc
     */
    initialize: function (options) {
        this.options = _.extend({}, this.options, options);

        mediator.on('checkout:payment:method:changed', this.onPaymentMethodChanged, this);
        mediator.on('checkout:payment:before-hide-filled-form', this.beforeHideFilledForm, this);
        mediator.on('checkout:payment:before-restore-filled-form', this.beforeRestoreFilledForm, this);
        mediator.on('checkout:payment:remove-filled-form', this.removeFilledForm, this);
        mediator.on('checkout-content:initialized', this.refreshPaymentMethod, this);

        this.checkIfAvailable();
    },

    loadApplePayScript: function (src) {
        const applePayJSLoadedPromise = new Promise(resolve => {
            const element = document.createElement("script");
            element.addEventListener("load", resolve, {once: true});
            element.src = "https://applepay.cdn-apple.com/jsapi/1.latest/apple-pay-sdk.js"
            // element.crossOrigin = "anonymous";
            document.head.appendChild(element);
        });

        applePayJSLoadedPromise.then(() => {
            this.onApplePayLoaded()
        })
    },

    onApplePayLoaded: function () {
        const choice = $(this.options.selector);

        try {
            if (!window.ApplePaySession || !this.options.merchantId || !PaymentRequest) {
                choice.addClass('hidden');
                return;
            }

            ApplePaySession.applePayCapabilities(this.options.merchantId).then((capabilities) => {
                switch (capabilities.paymentCredentialStatus) {
                    case 'paymentCredentialsAvailable':
                        choice.removeClass('hidden');
                        break;
                    case 'paymentCredentialStatusUnknown':
                    case 'paymentCredentialsUnavailable':
                    case 'applePayUnsupported':
                    default:
                        choice.addClass('hidden');
                        break;
                }
            });
        } catch (err) {
            console.log(err);
            choice.addClass('hidden');
        }
    },

    refreshPaymentMethod: function () {
        mediator.trigger('checkout:payment:method:refresh');
        this.checkIfAvailable();
    },
});

export default ApplePaySelectComponent;
