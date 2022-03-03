<?php
/**
 * This is NOT a freeware, use is subject to license terms
 * @copyright Copyright (c) 2010-2099 Jinan Larva Information Technology Co., Ltd.
 * @link http://www.larva.com.cn/
 */

namespace Larva\Censor\Admin\Actions;

use Dcat\Admin\Grid\BatchAction;
use Illuminate\Http\Request;

/**
 * 批量审核通过
 * @author Tongle Xu <xutongle@gmail.com>
 */
class GridBatchAccept extends BatchAction
{
    protected $title = '<i class="feather icon-check"></i> '.'审核通过';

    protected ?string $model;

    /**
     * BatchRestore constructor.
     * @param string|null $model
     */
    public function __construct(string $model = null)
    {
        $this->model = $model;
        parent::__construct($this->title);
    }

    public function handle(Request $request)
    {
        $model = $request->get('model');
        foreach ((array)$this->getKey() as $key) {
            $model::query()->findOrFail($key)->markApproved();
        }
        return $this->response()->success('已通过')->refresh();
    }

    public function confirm(): array
    {
        return ['确定吗？'];
    }

    public function parameters(): array
    {
        return [
            'model' => $this->model,
        ];
    }
}
