<?php

declare(strict_types=1);

namespace Macopedia\Bundle\TpayBundle\Twig\Extension;

use Twig\Extension\AbstractExtension;
use Twig\TwigFilter;

use function in_array;
use function is_array;
use function is_string;
use function preg_replace;
use function substr;
use function trim;

class LogExtension extends AbstractExtension
{
    public function getFilters(): array
    {
        return [
            new TwigFilter('gdpr_mask', [$this, 'gdprMask']),
        ];
    }

    /**
     * @param array<string,mixed>|string $value
     * @return array<string,mixed>|string
     */
    public function gdprMask(array|string $value): array|string
    {
        if (is_string($value)) {
            if (trim($value) === '') {
                return '';
            }

            return substr($value, 0, 1) . '***';
        }

        foreach ($value as $k => $v) {
            if (is_array($v)) {
                $value[$k] = $this->gdprMask($v);
            } elseif ($k === 'email') {
                $value[$k] = preg_replace('/\B[^@.]/', '*', $v);
            } elseif (in_array($k, ['name', 'token', 'card', 'phone', 'googlePayPaymentData', 'applePayPaymentData','tr_email','card_token'], true)) {
                $value[$k] = trim($v) === '' ? $v : substr($v, 0, 1) . '***';
            }
        }

        return $value;
    }
}
