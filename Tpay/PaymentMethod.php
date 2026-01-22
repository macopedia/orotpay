<?php

declare(strict_types=1);

namespace Macopedia\Bundle\TpayBundle\Tpay;

enum PaymentMethod
{
    case BLIK;
    case CARD;
    case PAY_BY_LINK;
    case REDIRECT;
    case PRAGMA_PAY;
    case VISA_MOBILE;
    case GOOGLE_PAY;
    case APPLE_PAY;
}
