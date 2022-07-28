<?php
/**
 * This is NOT a freeware, use is subject to license terms.
 *
 * @copyright Copyright (c) 2010-2099 Jinan Larva Information Technology Co., Ltd.
 */

namespace Larva\Censor\Events;

use Illuminate\Queue\SerializesModels;

/**
 * 内容需要人工复审
 * @author Tongle Xu <xutongle@gmail.com>
 */
class CensorPostponed
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
