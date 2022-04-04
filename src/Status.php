<?php
/**
 * This is NOT a freeware, use is subject to license terms.
 *
 * @copyright Copyright (c) 2010-2099 Jinan Larva Information Technology Co., Ltd.
 */

declare(strict_types=1);
/**
 * This is NOT a freeware, use is subject to license terms
 */

namespace Larva\Censor;

class Status
{
    public const PENDING = 0;
    public const APPROVED = 1;
    public const REJECTED = 2;
    public const POSTPONED = 3;//推迟，需要二次确认

    public const MAPS = [
        self::POSTPONED => '待复审',
        self::PENDING => '待审核',
        self::APPROVED => '已审核',
        self::REJECTED => '拒绝',
    ];

    /**
     * 获取状态Dot
     */
    public const DOTS = [
        Status::POSTPONED => 'warning',
        Status::PENDING => 'info',
        Status::APPROVED => 'success',
        Status::REJECTED => 'error',
    ];
}
