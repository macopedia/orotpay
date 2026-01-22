import $ from 'jquery';
import _ from 'underscore';
import mediator from 'oroui/js/mediator';
import BaseComponent from 'oroui/js/app/components/base/component';
import * as JSEncrypt from './jsencrypt.min';
import errorTemplate from 'tpl-loader!orotpay/js/templates/fieldError.html';

const CreditCardComponent = BaseComponent.extend({

    /**
     * @property {Object}
     */
    options: {
        paymentMethod: null,
        allowedCreditCards: [],
        rsaKey: null,
        selectors: {
            month: '[data-expiration-date-month]',
            year: '[data-expiration-date-year]',
            form: '[data-card-form]',
            expirationDate: '[data-expiration-date]',
            cvv: '[data-card-cvv]',
            cardNumber: '[data-card-number]',
            saveForLater: '[data-save-for-later]',
        }
    },

    cardNumberLength: 16,

    /**
     * @property {Boolean}
     */
    paymentValidationRequiredComponentState: false,

    /**
     * @property {jQuery}
     */
    $el: null,

    /**
     * @property string
     */
    month: null,

    /**
     * @property string
     */
    year: null,

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
    constructor: function CreditCardComponent(options) {
        CreditCardComponent.__super__.constructor.call(this, options);
    },

    /**
     * @inheritdoc
     */
    initialize: function (options) {
        this.options = _.extend({}, this.options, options);
        this.$el = this.options._sourceElement;
        this.$form = this.$el.find(this.options.selectors.form);
        this.$el
            .on('keyup.' + this.cid, this.options.selectors.cardNumber, this.normalizeCardNumber.bind(this))
            .on('keyup.' + this.cid, this.options.selectors.cvv, this.normalizeCvv.bind(this))
            .on('change.' + this.cid, this.options.selectors.month, this.collectMonthDate.bind(this))
            .on('change.' + this.cid, this.options.selectors.year, this.collectYearDate.bind(this))
            .on('focusout.' + this.cid, this.options.selectors.cardNumber, this.validate.bind(this))
            .on('focusout.' + this.cid, this.options.selectors.cvv, this.validate.bind(this))
            .on('change.' + this.cid, this.options.selectors.saveForLater, this.onSaveForLaterChange.bind(this));

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

    normalizeCvv: function (e) {
        e.target.value = e.target.value.replace(/\D/g, '');
    },

    normalizeCardNumber: function (e) {
        const number = e.target.value.replace(/\D/g, '');
        const numberParts = number.slice(0, this.cardNumberLength).match(/\d{1,4}/g) || [];

        e.target.value = numberParts.join(' ');
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
                    errorUrl: '',
                    paymentUrl: null,
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

    /**
     * @param {jQuery.Event} e
     */
    collectMonthDate: function (e) {
        this.month = e.target.value;

        this.setExpirationDate();
    },

    /**
     * @param {jQuery.Event} e
     */
    collectYearDate: function (e) {
        this.year = e.target.value;
        this.setExpirationDate();
    },

    setExpirationDate: function () {
        const hiddenExpirationDate = this.$el.find(this.options.selectors.expirationDate);
        if (this.month && this.year) {
            hiddenExpirationDate.val([this.month, this.year].join('/'));
        } else {
            hiddenExpirationDate.val('');
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

        CreditCardComponent.__super__.dispose.call(this);
    },

    handleError: function (condition, container, input, errorMsg) {
        if (condition) {
            container.find('.validation-failed').remove();
            input.removeClass('error');
        } else {
            input.toggleClass('error');
            if (container.find('.validation-failed').length === 0) (
                container.append(errorTemplate({message: errorMsg === undefined ? input.data('error-msg') : errorMsg}))
            )
        }
    },

    validate: function () {
        const cardNumberInput = $(this.options.selectors.cardNumber);
        const regex = new RegExp(`^\\d{${this.cardNumberLength}}$`);
        const isValidCreditCard = regex.test(cardNumberInput.val().replace(/\D/g, ''))
        this.handleError(isValidCreditCard, cardNumberInput.parent(), cardNumberInput);

        const currentDate = new Date();
        const cvvInput = $(this.options.selectors.cvv);
        const isValidCvv = cvvInput.val().replace(/\D/g, '').length === 3;
        this.handleError(isValidCvv, cvvInput.parent(), cvvInput);

        const currentYear = parseInt(currentDate.getFullYear());
        const currentMonth = parseInt(currentDate.getMonth())+1;

        const cardYear = $(this.options.selectors.year);
        const cardYearInputValue = cardYear.val();
        const isYearValid = cardYearInputValue !== '' && parseInt(cardYearInputValue) >= currentYear;
        this.handleError(isYearValid, cardYear.parent().parent().parent(), cardYear.siblings('.select2-container'), cardYear.data('error-msg'));

        const cardMonth = $(this.options.selectors.month);
        const cardMonthInput = cardMonth.val();

        let isMonthValid = cardMonthInput !== '' && isYearValid &&
            (
                (parseInt(cardYearInputValue) !== currentYear) ||
                (parseInt(cardYearInputValue) === currentYear && parseInt(cardMonthInput) >= currentMonth)
            );

        this.handleError(isMonthValid, cardMonth.parent().parent().parent(), cardMonth.siblings('.select2-container'), cardMonth.data('error-msg'));

        return isValidCreditCard && isMonthValid && isYearValid && isValidCvv;
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
     * @returns {jQuery}
     */
    getSaveForLaterElement: function () {
        if (!this.hasOwnProperty('$saveForLaterElement')) {
            this.$saveForLaterElement = this.$form.find(this.options.selectors.saveForLater);
        }

        return this.$saveForLaterElement;
    },

    /**
     * @returns {Boolean}
     */
    getSaveForLaterState: function () {
        return this.getSaveForLaterElement().prop('checked');
    },

    setSaveForLaterBasedOnForm: function () {
        mediator.trigger('checkout:payment:save-for-later:change', this.getSaveForLaterState());
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
        this.setSaveForLaterBasedOnForm();
    },

    /**
     * @param {Object} e
     */
    onSaveForLaterChange: function (e) {
        const $el = $(e.target);
        mediator.trigger('checkout:payment:save-for-later:change', $el.prop('checked'));
    },

    /**
     * @param {Object} eventData
     */
    beforeTransit: function (eventData) {
        if (eventData.data.paymentMethod !== this.options.paymentMethod) {
            return;
        }

        const validData = this.validate();

        eventData.stopped = !validData;

        if (!validData) {
            return;
        }

        const cardNumber = $(this.options.selectors.cardNumber).val().replace(/\s/g, '');
        const cvcInput = $(this.options.selectors.cvv).val().replace(/\s/g, '');
        const expirationDate = $(this.options.selectors.expirationDate).val().replace(/\s/g, '');
        const saveForLater = $(this.options.selectors.saveForLater).prop('checked');

        const payload = [cardNumber, expirationDate, cvcInput, document.location.origin].join('|');
        const encrypt = new JSEncrypt();
        encrypt.setPublicKey(atob(this.options.rsaKey));

        const additionalData = {
            'card': encrypt.encrypt(payload),
            'saveForLater': saveForLater,
        };

        mediator.trigger('checkout:payment:additional-data:set', JSON.stringify(additionalData));
        eventData.resume();
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

export default CreditCardComponent;

