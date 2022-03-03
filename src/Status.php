<?php

declare(strict_types=1);
/**
 * This is NOT a freeware, use is subject to license terms
 */

namespace Larva\Censor;

class Status
{
    const PENDING = 0;
    const APPROVED = 1;
    const REJECTED = 2;
    const POSTPONED = 3;//推迟，需要二次确认

    const MAPS = [
        self::POSTPONED => '待复审',
        self::PENDING => '待审核',
        self::APPROVED => '已审核',
        self::REJECTED => '拒绝',
    ];
}