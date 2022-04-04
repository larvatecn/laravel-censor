<?php
/**
 * This is NOT a freeware, use is subject to license terms.
 *
 * @copyright Copyright (c) 2010-2099 Jinan Larva Information Technology Co., Ltd.
 */

namespace Larva\Censor\Admin\Actions;

use Dcat\Admin\Show\AbstractTool;
use Illuminate\Http\Request;

class ShowReviewReject extends AbstractTool
{
    /**
     * @var string
     */
    protected $title = '<i class="feather icon-slash"></i> '.'设为拒绝通过';

    /**
     * @var string
     */
    protected $style = 'btn btn-sm btn-danger';

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

        $model::findOrFail($key)->markRejected();

        return $this->response()->success('已拒绝通过')->refresh();
    }

    public function confirm(): array
    {
        return ['确定拒绝通过吗？'];
    }

    public function parameters(): array
    {
        return [
            'model' => $this->model,
        ];
    }

    /**
     * @return string
     */
    protected function html(): string
    {
        $this->defaultHtmlAttribute('href', 'javascript:void(0)');

        return <<<HTML
<div class="btn-group pull-right btn-mini" style="margin-right: 5px">
<a {$this->formatHtmlAttributes()}>{$this->title()}</a>
</div>
HTML;
    }
}
