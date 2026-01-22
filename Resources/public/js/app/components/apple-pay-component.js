import _ from 'underscore';
import mediator from 'oroui/js/mediator';
import ApplePaySelectComponent from './apple-pay-select-component';

const ApplePayComponent = ApplePaySelectComponent.extend({

    /**
     * @inheritdoc
     */
    constructor: function ApplePayComponent(options) {
        ApplePayComponent.__super__.constructor.call(this, options);
    },

    /**
     * @inheritdoc
     */
    initialize: function (options) {
        this.options = _.extend({}, this.options, options);
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

        ApplePayComponent.__super__.dispose.call(this);
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
    handlePayment: async function (event) {
        event.preventDefault();
        const obj = event.data.obj;

        try {
            const request = new PaymentRequest(obj.options.methodData, obj.options.paymentDetails, obj.options.paymentOptions);

            request.onmerchantvalidation = async event => {
                await fetch(this.options.sessionEndpoint, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify({
                        domainName: window.location.hostname,
                        displayName: obj.options.paymentDetails.label,
                        validationUrl: event.validationURL,
                    })
                }).then(async response => {
                    if (response.status === 200) {
                        const result = await response.json();

                        event.complete(JSON.parse(atob(result.session)));
                    }
                });
            };

            const response = await request.show();
            const status = "success";
            await response.complete(status);

            mediator.trigger('checkout:payment:additional-data:set', JSON.stringify({
                'token': btoa(JSON.stringify(response.details.token.paymentData)),
            }));

            obj.getForm(obj.submitButton).trigger('submit');
        } catch (e) {
            console.error(e);
        }
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

export default ApplePayComponent;
