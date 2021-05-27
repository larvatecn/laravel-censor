<?php
/**
 * This is NOT a freeware, use is subject to license terms
 * @copyright Copyright (c) 2010-2099 Jinan Larva Information Technology Co., Ltd.
 * @link http://www.larva.com.cn/
 */

namespace Larva\Censor\Events;

use Illuminate\Queue\SerializesModels;

/**
 * 内容审核通过
 * @author Tongle Xu <xutongle@gmail.com>
 */
class CensorApproved
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