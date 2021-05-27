<?php

namespace Larva\Censor\Admin\Actions\Show;

use Dcat\Admin\Actions\Response;
use Dcat\Admin\Show\AbstractTool;
use Illuminate\Http\Request;

class ReviewAccept extends AbstractTool
{
    /**
     * @return string
     */
    protected $title = '<i class="feather icon-check"></i> '.'设为审核通过';

    /**
     * @var string
     */
    protected $style = 'btn btn-sm btn-success';

    /**
     * @var string|null
     */
    protected $model;

    /**
     * Restore constructor.
     * @param string|null $model
     */
    public function __construct(string $model = null)
    {
        $this->model = $model;
        parent::__construct($this->title);
    }

    /**
     * Handle the action request.
     *
     * @param Request $request
     *
     * @return Response
     */
    public function handle(Request $request): Response
    {
        $key = $this->getKey();
        $model = $request->get('model');
        $model::findOrFail($key)->markRejected();
        return $this->response()->success('已审核通过')->refresh();
    }

    public function confirm(): array
    {
        return ['确定审核通过吗？'];
    }

    public function parameters()
    {
        return [
            'model' => $this->model,
        ];
    }

    /**
     * @return string
     */
    protected function html()
    {
        $this->defaultHtmlAttribute('href', 'javascript:void(0)');

        return <<<HTML
<div class="btn-group pull-right btn-mini" style="margin-right: 5px">
<a {$this->formatHtmlAttributes()}>{$this->title()}</a>
</div>
HTML;
    }
}
