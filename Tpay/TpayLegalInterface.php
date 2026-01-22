<?php

declare(strict_types=1);

namespace Macopedia\Bundle\TpayBundle\Tpay;

interface TpayLegalInterface
{
    public function setLocale(string $locale): static;
    public function getRegulations(string $locale = 'en'): string;
    public function getPolicy(string $locale = 'en'): string;
}
