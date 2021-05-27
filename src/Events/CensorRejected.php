<?php
/**
 * This is NOT a freeware, use is subject to license terms
 * @copyright Copyright (c) 2010-2099 Jinan Larva Information Technology Co., Ltd.
 * @link http://www.larva.com.cn/
 */

namespace Larva\Censor\Events;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Queue\SerializesModels;

/**
 * 内容被拒绝
 * @author Tongle Xu <xutongle@gmail.com>
 */
class CensorRejected
{
    use SerializesModels;

    /**
     * @var mixed
     */
    public $model;

    /**
     * Create a new event instance.
     *
     * @param mixed $model
     */
    public function __construct($model)
    {
        $this->model = $model;
    }
}