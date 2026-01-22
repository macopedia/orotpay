<?php

declare(strict_types=1);

namespace Macopedia\Bundle\TpayBundle\Tpay;

enum PayGroup: int
{
    case BLIK = 150;
    case CARD = 103;
    case VISA_MOBILE = 171;
    case GOOGLE_PAY = 166;
}
