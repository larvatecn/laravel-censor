<?php
/**
 * This is NOT a freeware, use is subject to license terms
 * @copyright Copyright (c) 2010-2099 Jinan Larva Information Technology Co., Ltd.
 * @link http://www.larva.com.cn/
 */

namespace Larva\Censor\Admin\Actions;

use Dcat\Admin\Grid\RowAction;
use Illuminate\Http\Request;

/**
 * 审核通过
 * @author Tongle Xu <xutongle@gmail.com>
 */
class GridReviewAccept extends RowAction
{
    /**
     * @var string
     */
    protected $title = '<i class="feather icon-check"></i> '.'审核通过';

    /**
     * @var string|null
     */
    protected ?string $model;

    /**
     * Restore constructor.
     * @param string|null $model
     */
    public function __construct(string $model = null)
    {
        $this->model = $model;
        parent::__construct($this->title);
    }

    public function handle(Request $request)
    {
        $key = $this->getKey();
        $model = $request->get('model');

        $model::findOrFail($key)->markApproved();

        return $this->response()->success('已审核通过')->refresh();
    }

    public function confirm(): array
    {
        return ['确定审核通过吗？'];
    }

    public function parameters(): array
    {
        return [
            'model' => $this->model,
        ];
    }
}
