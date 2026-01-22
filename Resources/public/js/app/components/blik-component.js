import $ from 'jquery';
import _ from 'underscore';
import mediator from 'oroui/js/mediator';
import BaseComponent from 'oroui/js/app/components/base/component';
import errorTemplate from 'tpl-loader!orotpay/js/templates/fieldError.html';
import MobileAppConfirmModal from "./mobile-app-confirm-modal";

const BlikComponent = BaseComponent.extend({

    /**
     * @property {Object}
     */
    options: {
        paymentMethod: null,
        selectors: {
            form: '[data-blik-form]',
            token: '[data-blik-token]',
        },
        app_icon: '',
    },

    /**
     * @property {Boolean}
     */
    paymentValidationRequiredComponentState: false,

    /**
     * @property {jQuery}
     */
    $el: null,

    /**
     * @property {jQuery}
     */
    $form: null,

    /**
     * @property {Boolean}
     */
    disposable: true,


    /**
     * @inheritdoc
     */
    constructor: function BlikComponent(options) {
        BlikComponent.__super__.constructor.call(this, options);
    },

    /**
     * @inheritdoc
     */
    initialize: function (options) {
        this.options = _.extend({}, this.options, options);

        this.$el = this.options._sourceElement;
        this.$form = this.$el.find(this.options.selectors.form);

        this.$el
            .on('keyup.' + this.cid, this.options.selectors.token, this.removeAllExceptNumbers.bind(this))
            .on('focusout.' + this.cid, this.options.selectors.token, this.validate.bind(this));

        mediator.on('checkout:place-order:response', this.handleSubmit, this);
        mediator.on('checkout:payment:method:changed', this.onPaymentMethodChanged, this);
        mediator.on('checkout:before-submit', this.beforeFormSubmit, this);
        mediator.on('checkout:payment:before-hide-filled-form', this.beforeHideFilledForm, this);
        mediator.on('checkout:payment:before-restore-filled-form', this.beforeRestoreFilledForm, this);
        mediator.on('checkout:payment:remove-filled-form', this.removeFilledForm, this);
        mediator.on('checkout-content:initialized', this.refreshPaymentMethod, this);
    },

    refreshPaymentMethod: function () {
        mediator.trigger('checkout:payment:method:refresh');
    },

    removeAllExceptNumbers: function (e) {
        e.target.value = e.target.value.replace(/\D/g, '');
    },

    /**
     * @param {Object} eventData
     */
    handleSubmit: function (eventData) {
        if (eventData.responseData.paymentMethod === this.options.paymentMethod) {
            eventData.stopped = true;

            const resolvedEventData = _.extend(
                {
                    returnUrl: '',
                    statusUrl: '',
                    successful: false
                },
                eventData.responseData
            );

            MobileAppConfirmModal.handle(resolvedEventData, this.options);
        }
    },

    dispose: function () {
        if (this.disposed || !this.disposable) {
            return;
        }

        this.$el.off('.' + this.cid);

        mediator.off('checkout-content:initialized', this.refreshPaymentMethod, this);
        mediator.off('checkout:place-order:response', this.handleSubmit, this);
        mediator.off('checkout:payment:method:changed', this.onPaymentMethodChanged, this);
        mediator.off('checkout:before-submit', this.beforeFormSubmit, this);
        mediator.off('checkout:payment:before-hide-filled-form', this.beforeHideFilledForm, this);
        mediator.off('checkout:payment:before-restore-filled-form', this.beforeRestoreFilledForm, this);
        mediator.off('checkout:payment:remove-filled-form', this.removeFilledForm, this);

        BlikComponent.__super__.dispose.call(this);
    },

    validate: function () {
        const tokenInput = $(this.options.selectors.token);
        const validToken = tokenInput.val() !== undefined && tokenInput.val().length === 6;
        const tokenContainer = tokenInput.parent();

        if (validToken) {
            tokenContainer.find('.validation-failed').remove()
            tokenInput.removeClass('error');
        } else {
            tokenInput.toggleClass('error');
            if (tokenContainer.find('.validation-failed').length === 0) (
                tokenContainer.append(errorTemplate({message: tokenInput.data('error-msg')}))
            )
        }

        return validToken;
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

    /**
     * @param {Object} eventData
     */
    beforeFormSubmit: function (eventData) {

        if ($(this.options.selectors.token).length === 0) {
            return;
        }

        const validData = this.validate();

        eventData.stopped = !validData;

        if (validData) {
            const additionalData = {
                'blik_token': $(this.options.selectors.token).val(),
            };
            mediator.trigger('checkout:payment:additional-data:set', JSON.stringify(additionalData));
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

export default BlikComponent;
