import _ from 'underscore';
import mediator from 'oroui/js/mediator';
import GooglePaySelectComponent from './google-pay-select-component';

const GooglePayComponent = GooglePaySelectComponent.extend({

    /**
     * @inheritdoc
     */
    constructor: function GooglePayComponent(options) {
        GooglePayComponent.__super__.constructor.call(this, options);
    },

    /**
     * @inheritdoc
     */
    initialize: function (options) {
        this.processOptions(options);
        this.submitButton = $('.checkout-form__submit[type="submit"]');
        this.submitButton.on('click', {obj: this}, this.handlePayment);

        mediator.on('checkout:place-order:response', this.handleSubmit, this);
        mediator.on('checkout:payment:method:changed', this.onPaymentMethodChanged, this);
        mediator.on('checkout:payment:before-hide-filled-form', this.beforeHideFilledForm, this);
        mediator.on('checkout:payment:before-restore-filled-form', this.beforeRestoreFilledForm, this);
        mediator.on('checkout:payment:remove-filled-form', this.removeFilledForm, this);
        mediator.on('checkout-content:initialized', this.refreshPaymentMethod, this);
    },

    /**
     * @param {Object} eventData
     */
    handleSubmit: function (eventData) {
        if (eventData.responseData.paymentMethod === this.options.paymentMethod) {
            eventData.stopped = true;

            const resolvedEventData = _.extend(
                {
                    successUrl: '',
                    failureUrl: '',
                    successful: false
                },
                eventData.responseData
            );

            mediator.execute('redirectTo', {url: resolvedEventData.successful ? resolvedEventData.successUrl : resolvedEventData.failureUrl}, {redirect: true});
        }
    },

    dispose: function () {
        if (this.disposed || !this.disposable) {
            return;
        }

        this.submitButton.off('click', {obj: this}, this.handlePayment);
        mediator.off('checkout-content:initialized', this.refreshPaymentMethod, this);
        mediator.off('checkout:place-order:response', this.handleSubmit, this);
        mediator.off('checkout:payment:method:changed', this.onPaymentMethodChanged, this);
        mediator.off('checkout:payment:before-hide-filled-form', this.beforeHideFilledForm, this);
        mediator.off('checkout:payment:before-restore-filled-form', this.beforeRestoreFilledForm, this);
        mediator.off('checkout:payment:remove-filled-form', this.removeFilledForm, this);

        GooglePayComponent.__super__.dispose.call(this);
    },

    getGooglePaymentDataRequest: function () {
        const paymentDataRequest = Object.assign({}, this.googlePay.baseRequest);
        paymentDataRequest.allowedPaymentMethods = [this.getAllowedPaymentMethods()];
        paymentDataRequest.transactionInfo = this.options.transactionInfo;

        if (this.googlePay.isProduction) {
            paymentDataRequest.merchantInfo = {
                merchantId: this.options.merchantId,
            };
        } else {
            console.log({paymentDataRequest});
        }

        return paymentDataRequest;
    },

    /**
     * @param {Boolean} state
     */
    setGlobalPaymentValidate: function (state) {
        this.paymentValidationRequiredComponentState = state;
        mediator.trigger('checkout:payment:validate:change', state);
    },

    /**
     * @returns {Boolean}
     */
    getGlobalPaymentValidate: function () {
        const validateValueObject = {};
        mediator.trigger('checkout:payment:validate:get-value', validateValueObject);
        return validateValueObject.value;
    },

    /**
     * @param {Object} eventData
     */
    onPaymentMethodChanged: function (eventData) {
        if (eventData.paymentMethod === this.options.paymentMethod) {
            this.onCurrentPaymentMethodSelected();
        }
    },

    onCurrentPaymentMethodSelected: function () {
        this.setGlobalPaymentValidate(this.paymentValidationRequiredComponentState);
    },

    getForm($element) {
        return $element.prop('form') ? $($element.prop('form')) : $element.closest('form');
    },
    /**
     * @param {Object} event
     */
    handlePayment: function (event) {
        event.preventDefault();
        const obj = event.data.obj;
        const paymentDataRequest = obj.getGooglePaymentDataRequest();

        const paymentsClient = obj.getGooglePaymentsClient();
        paymentsClient.loadPaymentData(paymentDataRequest)
            .then(function (paymentData) {
                mediator.trigger('checkout:payment:additional-data:set', JSON.stringify({
                    'token': btoa(paymentData.paymentMethodData.tokenizationData),
                }));

                obj.getForm(obj.submitButton).trigger('submit');
            })
            .catch(function (err) {
                console.error(err);
            });
    },

    beforeHideFilledForm: function () {
        this.disposable = false;
    },

    beforeRestoreFilledForm: function () {
        if (this.disposable) {
            this.dispose();
        }
    },

    removeFilledForm: function () {
        // Remove hidden form js component
        if (!this.disposable) {
            this.disposable = true;
            this.dispose();
        }
    }
});

export default GooglePayComponent;
