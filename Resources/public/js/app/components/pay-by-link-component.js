import $ from 'jquery';
import _ from 'underscore';
import 'jquery.validate';
import mediator from 'oroui/js/mediator';
import BaseComponent from 'oroui/js/app/components/base/component';
import errorTemplate from 'tpl-loader!orotpay/js/templates/fieldError.html';

const PayByLinkComponent = BaseComponent.extend({
    /**
     * @property {Object}
     */
    options: {
        paymentMethod: null,
        selectors: {
            form: '[data-pbl-form]',
            channelId: '[data-channel]',
        }
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
    constructor: function PayByLinkComponent(options) {
        PayByLinkComponent.__super__.constructor.call(this, options);
    },

    /**
     * @inheritdoc
     */
    initialize: function (options) {
        this.options = _.extend({}, this.options, options);

        this.$el = this.options._sourceElement;
        this.$form = this.$el.find(this.options.selectors.form);

        this.$el
            .on('focusout.' + this.cid, this.options.selectors.channelId, this.validate.bind(this));

        mediator.on('checkout:place-order:response', this.handleSubmit, this);
        mediator.on('checkout:payment:method:changed', this.onPaymentMethodChanged, this);
        mediator.on('checkout:payment:before-transit', this.beforeTransit, this);
        mediator.on('checkout:payment:before-hide-filled-form', this.beforeHideFilledForm, this);
        mediator.on('checkout:payment:before-restore-filled-form', this.beforeRestoreFilledForm, this);
        mediator.on('checkout:payment:remove-filled-form', this.removeFilledForm, this);
        mediator.on('checkout-content:initialized', this.refreshPaymentMethod, this);
    },

    refreshPaymentMethod: function () {
        mediator.trigger('checkout:payment:method:refresh');
    },

    /**
     * @param {Object} eventData
     */
    handleSubmit: function (eventData) {
        if (eventData.responseData.paymentMethod === this.options.paymentMethod) {
            eventData.stopped = true;

            const resolvedEventData = _.extend(
                {
                    paymentUrl: '',
                    returnUrl: '',
                    errorUrl: '',
                    successful: false,
                },
                eventData.responseData
            );

            let url;

            switch (true) {
                case resolvedEventData.successful && resolvedEventData.paymentUrl !== null:
                    url = resolvedEventData.paymentUrl;
                    break;
                case resolvedEventData.successful:
                    url = resolvedEventData.returnUrl;
                    break;
                default:
                    url = resolvedEventData.errorUrl;
                    break;
            }

            mediator.execute('redirectTo', {url: url}, {redirect: true});
        }
    },

    dispose: function () {
        if (this.disposed || !this.disposable) {
            return;
        }

        this.$el.off('.' + this.cid);

        mediator.trigger('checkout:payment:additional-data:set', JSON.stringify({}));
        mediator.off('checkout-content:initialized', this.refreshPaymentMethod, this);
        mediator.off('checkout:place-order:response', this.handleSubmit, this);
        mediator.off('checkout:payment:method:changed', this.onPaymentMethodChanged, this);
        mediator.off('checkout:payment:before-transit', this.beforeTransit, this);
        mediator.off('checkout:payment:before-hide-filled-form', this.beforeHideFilledForm, this);
        mediator.off('checkout:payment:before-restore-filled-form', this.beforeRestoreFilledForm, this);
        mediator.off('checkout:payment:remove-filled-form', this.removeFilledForm, this);

        PayByLinkComponent.__super__.dispose.call(this);
    },

    validate: function () {
        const selectedChannel = $(this.options.selectors.channelId);
        const isChannelSelected = selectedChannel.is(':checked');
        const channelContainer = selectedChannel.parent().parent();

        if (isChannelSelected) {
            channelContainer.find('.validation-failed').remove()
            selectedChannel.removeClass('error');
        } else {
            selectedChannel.toggleClass('error');
            if (channelContainer.find('.validation-failed').length === 0) (
                channelContainer.prepend(errorTemplate({message: selectedChannel.data('error-msg') }))
            )
        }

        return isChannelSelected;
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
    beforeTransit: function (eventData) {
        if (eventData.data.paymentMethod === this.options.paymentMethod) {
            eventData.stopped = !this.validate();

            if (!eventData.stopped) {
                const additionalData = {
                    'channelId': $(this.options.selectors.channelId + ':checked').val(),
                };
                mediator.trigger('checkout:payment:additional-data:set', JSON.stringify(additionalData));
                eventData.resume();
            }
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

export default PayByLinkComponent;
