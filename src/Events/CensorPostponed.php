<?php
/**
 * This is NOT a freeware, use is subject to license terms
 * @copyright Copyright (c) 2010-2099 Jinan Larva Information Technology Co., Ltd.
 * @link http://www.larva.com.cn/
 */

namespace Larva\Censor\Events;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Queue\SerializesModels;

class CensorPostponed
{
    use SerializesModels;

    /**
     * @var Model
     */
    public $source;

    /**
     * Create a new event instance.
     *
     * @param Model $source
     */
    public function __construct(Model $source)
    {
        $this->source = $source;
    }
}