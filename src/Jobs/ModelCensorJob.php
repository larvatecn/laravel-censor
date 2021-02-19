<?php
/**
 * This is NOT a freeware, use is subject to license terms
 * @copyright Copyright (c) 2010-2099 Jinan Larva Information Technology Co., Ltd.
 * @link http://www.larva.com.cn/
 * @license http://www.larva.com.cn/license/
 */

namespace Larva\Censor\Jobs;

use Illuminate\Database\Eloquent\Model;

/**
 * Class ModelCensorJob
 * @author Tongle Xu <xutongle@gmail.com>
 */
class ModelCensorJob extends CensorJob
{
    /** @var Model */
    protected $model;

    /**
     * Create a new job instance.
     *
     * @param Model $model
     */
    public function __construct(Model $model)
    {
        $this->model = $model;
    }

    /**
     * Execute the job.
     * @reurn void
     */
    public function handle()
    {
        $this->model->censor();
    }
}
