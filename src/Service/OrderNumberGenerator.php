<?php

declare(strict_types=1);

namespace App\Service;

use DateTime;

use function bin2hex;
use function random_bytes;
use function sprintf;
use function strtoupper;
use function substr;

final class OrderNumberGenerator
{
    public function generate(): string
    {
        $date = (new DateTime())->format('Ymd');
        $random = strtoupper(substr(bin2hex(random_bytes(4)), 0, 6));

        return sprintf('ORD-%s-%s', $date, $random);
    }
}
