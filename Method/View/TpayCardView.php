<?php

declare(strict_types=1);

namespace Macopedia\Bundle\TpayBundle\Method\View;

use Carbon\Carbon;
use Macopedia\Bundle\TpayBundle\Form\Type\CreditCardType;
use Macopedia\Bundle\TpayBundle\Method\TpayCardMethod;
use Macopedia\Bundle\TpayBundle\Tpay\SavedCardInterface;
use Macopedia\Bundle\TpayBundle\Tpay\TpayLegalInterface;
use Oro\Bundle\PaymentBundle\Context\PaymentContextInterface;
use Oro\Bundle\PaymentBundle\Entity\PaymentTransaction;
use Symfony\Component\Form\FormFactoryInterface;

use function sprintf;

class TpayCardView extends AbstractTpayView
{
    public function __construct(
        protected TpayLegalInterface $tpayLegal,
        protected FormFactoryInterface $formFactory,
        protected SavedCardInterface $savedCardHandler,
    ) {
    }

    public function getPaymentMethodIdentifier(): string
    {
        return TpayCardMethod::buildIdentifier(parent::getPaymentMethodIdentifier());
    }

    public function getAdminLabel(): string
    {
        return sprintf('[%s] %s', parent::getAdminLabel(), $this->config->getCardsLabel());
    }

    public function getLabel(): string
    {
        return $this->config->getCardsLabel();
    }

    public function getShortLabel(): string
    {
        return $this->config->getCardsLabel();
    }

    /*
     * @return array<string, mixed>
     */
    public function getOptions(PaymentContextInterface $context): array
    {
        $cards = $this->getSavedCards();
        $creditCardComponent = null;

        if ($cards !== []) {
            $creditCardComponent = 'orotpay/js/app/components/authorized-credit-card-component';
        }

        return array_merge(
            parent::getOptions($context),
            [
                'formView' => $this->formFactory->create(CreditCardType::class)->createView(),
                'creditCardComponent' => $creditCardComponent,
                'creditCardComponentOptions' => ['rsaKey' => $this->config->getMerchantRsaKey()] + $cards,
            ],
        );
    }

    public function getBlock(): string
    {
        return '_payment_methods_tpay_card_view_widget';
    }

    private function getSavedCards(): array
    {
        $cards = $this->savedCardHandler->getCards($this->getPaymentMethodIdentifier());

        if ($cards === []) {
            return [];
        }

        $cards = array_filter($cards, static function (PaymentTransaction $card) {
            $transactionOptions = $card->getResponse();
            $expirationDate = trim($transactionOptions['expirationDate'] ?? '');

            if ($expirationDate !== '') {
                $expirationDate = Carbon::createFromFormat('my', $expirationDate)
                    ->endOfMonth()
                    ->startOfDay();

                return !$expirationDate->isPast();
            }

            return true;
        });

        if ($cards === []) {
            return [];
        }

        return [
            'cards' => array_map(static function (PaymentTransaction $cardRecord) {
                $card = $cardRecord->getResponse();
                $transactionOptions = $cardRecord->getTransactionOptions();

                return [
                    'id' => $cardRecord->getId(),
                    'tail' => $card['tail'],
                    'brand' => $card['brand'],
                    'saveForLaterUse' => (int)($transactionOptions['saveForLaterUse'] ?? 0),
                ];
            }, $cards),
        ];
    }
}
