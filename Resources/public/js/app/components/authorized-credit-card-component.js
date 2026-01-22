import mediator from 'oroui/js/mediator';
import CreditCardComponent from './credit-card-component';
import $ from "jquery";

const AuthorizedCreditCardComponent = CreditCardComponent.extend({
    /**
     * @property {Object}
     */
    authorizedOptions: {
        differentCard: '[data-different-card]',
        authorizedCards: '[data-authorized-cards]',
        cardSelector: '[data-authorized-cards-selector]',
        differentCardHandle: '[data-different-card-handle]',
        authorizedCardHandle: '[data-authorized-card-handle]'
    },

    /**
     * @property {Boolean}
     */
    paymentValidationRequiredComponentState: false,

    /**
     * @property {jQuery}
     */
    $authorizedCards: null,

    /**
     * @property {jQuery}
     */
    $differentCard: null,

    /**
     * @inheritdoc
     */
    constructor: function AuthorizedCreditCardComponent(options) {
        AuthorizedCreditCardComponent.__super__.constructor.call(this, options);
    },

    /**
     * @inheritdoc
     */
    initialize: function (options) {
        this.options.saveForLaterUse = false;
        AuthorizedCreditCardComponent.__super__.initialize.call(this, options);

        this.$authorizedCards = this.$el.find(this.authorizedOptions.authorizedCards);
        this.$differentCard = this.$el.find(this.authorizedOptions.differentCard);

        this.showAuthorizedCard = this.showAuthorizedCard.bind(this);
        this.showDifferentCard = this.showDifferentCard.bind(this);

        const formatCreditCard = function (state) {
            const brand = $(state.element).data('brand');

            return $('<span class="credit-card-icon credit-card-icon_' + brand + '" aria-hidden="true"></span><span>' + state.text + '</span>');
        };

        $(this.$el.find(this.authorizedOptions.cardSelector)).select2({
            formatResult: formatCreditCard,
            formatSelection: formatCreditCard
        })

        this.$el
            .on('click', this.authorizedOptions.authorizedCardHandle, this.showAuthorizedCard)
            .on('click', this.authorizedOptions.differentCardHandle, this.showDifferentCard);
    },

    /**
     * @returns {Boolean}
     */
    showDifferentCard: function () {
        this.$differentCard.show();
        this.$authorizedCards.hide();

        this.setGlobalPaymentValidate(true);
        this.updateSaveForLater();

        return false;
    },

    /**
     * @returns {Boolean}
     */
    showAuthorizedCard: function () {
        this.$authorizedCards.show();
        this.$differentCard.hide();

        this.setGlobalPaymentValidate(false);
        this.updateSaveForLater();

        return false;
    },

    onCurrentPaymentMethodSelected: function () {
        this.setGlobalPaymentValidate(this.paymentValidationRequiredComponentState);
        this.updateSaveForLater();
    },

    updateSaveForLater: function () {
        if (this.getGlobalPaymentValidate()) {
            this.setSaveForLaterBasedOnForm();
        } else {
            mediator.trigger('checkout:payment:save-for-later:change', this.options.saveForLaterUse);
        }
    },

    /**
     * @inheritdoc
     */
    beforeTransit: function (eventData) {
        if (eventData.data.paymentMethod !== this.options.paymentMethod) {
            return;
        }

        eventData.stopped = true;

        const cardNumber = $(this.options.selectors.cardNumber).val().replace(/\s/g, '');

        if (cardNumber !== '') {
            return AuthorizedCreditCardComponent.__super__.beforeTransit.call(this, eventData);
        }

        const additionalData = {
            'auth_card': this.$el.find(this.authorizedOptions.cardSelector).val(),
        };

        mediator.trigger('checkout:payment:additional-data:set', JSON.stringify(additionalData));
        eventData.resume();
    },

    /**
     * @inheritdoc
     */
    handleSubmit: function (eventData) {
        if (eventData.responseData.paymentMethod === this.options.paymentMethod
        ) {
            AuthorizedCreditCardComponent.__super__.handleSubmit.call(this, eventData);
        }
    },

    /**
     * @inheritdoc
     */
    dispose: function () {
        if (this.disposed || !this.disposable) {
            return;
        }

        this.$el
            .off('click', this.authorizedOptions.authorizedCardHandle, this.showAuthorizedCard)
            .off('click', this.authorizedOptions.differentCardHandle, this.showDifferentCard);

        AuthorizedCreditCardComponent.__super__.dispose.call(this);
    }
});

export default AuthorizedCreditCardComponent;
