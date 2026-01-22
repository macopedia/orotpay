<?php

declare(strict_types=1);

namespace Macopedia\Bundle\TpayBundle\Tpay;

use Oro\Bundle\LocaleBundle\Provider\LocalizationProviderInterface;

use function substr;

class TpayLegal implements TpayLegalInterface
{
    private string $locale = 'en';

    public function __construct(protected LocalizationProviderInterface $localizationProvider)
    {
        $this->locale = $this->localizationProvider->getCurrentLocalization()?->getLanguageCode() ?? 'en';
    }

    public function setLocale(string $locale): static
    {
        $this->locale = $locale;

        return $this;
    }

    public function getRegulations(string $locale = ''): string
    {
        if ($locale === '') {
            $locale = $this->locale;
        }

        return match (substr($locale, 0, 2)) {
            'en' => 'https://tpay.com/user/assets/files_for_download/payment-terms-and-conditions.pdf',
            'pl' => 'https://tpay.com/user/assets/files_for_download/regulamin.pdf',
            default => 'https://tpay.com/user/assets/files_for_download/payment-terms-and-conditions.pdf',
        };
    }

    public function getPolicy(string $locale = ''): string
    {
        if ($locale === '') {
            $locale = $this->locale;
        }

        return match (substr($locale, 0, 2)) {
            'en' => 'https://tpay.com/user/assets/files_for_download/information-clause-payer.pdf',
            'pl' => 'https://tpay.com/user/assets/files_for_download/klauzula-informacyjna-platnik.pdf',
            default => 'https://tpay.com/user/assets/files_for_download/information-clause-payer.pdf',
        };
    }

    /**
     * @return array{policyUrl:string, regulationsUrl:string}
     */
    public function getData(): array
    {
        return [
            'policyUrl' => $this->getPolicy(),
            'regulationsUrl' => $this->getRegulations(),
        ];
    }
}
