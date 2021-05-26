<?php
/**
 * This is NOT a freeware, use is subject to license terms
 * @copyright Copyright (c) 2010-2099 Jinan Larva Information Technology Co., Ltd.
 * @link http://www.larva.com.cn/
 */

namespace Larva\Censor;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphOne;
use Illuminate\Support\Facades\Event;
use Larva\Censor\Models\ContentCensor;

/**
 * 内容审查
 * @property boolean $status 状态：0 待审核 1 审核通过 2 拒绝 3 推迟审核
 * @property ContentCensor $stopWords 触发的审核词
 *
 * @method static Builder approve() 审核通过的
 * @method static Builder pending() 待审核的
 * @method static Builder postponed() 被推迟的
 * @method static Builder rejected() 审核拒绝的
 *
 * @mixin  Model
 * @author Tongle Xu <xutongle@gmail.com>
 */
trait HasCensor
{
    /**
     * @var string
     */
    protected $statusField = 'status';

    /**
     * Get the entity's stopWords.
     *
     * @return MorphOne
     */
    public function stopWords(): MorphOne
    {
        return $this->morphOne(ContentCensor::class, 'source');
    }

    /**
     * 查询待审核的
     * @param Builder $query
     * @return Builder
     */
    public function scopePending(Builder $query): Builder
    {
        return $query->whereIn($this->statusField, [Status::PENDING, Status::POSTPONED]);
    }

    /**
     * 查询被推迟的
     * @param Builder $query
     * @return Builder
     */
    public function scopePostponed(Builder $query): Builder
    {
        return $query->whereIn($this->statusField, [Status::PENDING, Status::POSTPONED]);
    }

    /**
     * 查询审核通过的
     * @param Builder $query
     * @return Builder
     */
    public function scopeApprove(Builder $query): Builder
    {
        return $query->where($this->statusField, Status::APPROVED);
    }

    /**
     * 查询审核不通过
     * @param Builder $query
     * @return Builder
     */
    public function scopeRejected(Builder $query): Builder
    {
        return $query->where($this->statusField, Status::REJECTED);
    }

    /**
     * 标记已审核
     * @return bool
     */
    public function markApproved(): bool
    {
        $this->status = Status::APPROVED;
        $status = $this->saveQuietly();
        Event::dispatch(new Events\CensorApproved($this));
        return $status;
    }

    /**
     * 标记延迟审核
     * @return bool
     */
    public function markPostponed(): bool
    {
        $this->status = Status::POSTPONED;
        $status = $this->saveQuietly();
        Event::dispatch(new Events\CensorPostponed($this));
        return $status;
    }

    /**
     * 标记待审核
     * @return bool
     */
    public function markPending(): bool
    {
        $this->status = Status::PENDING;
        $status = $this->saveQuietly();
        Event::dispatch(new Events\CensorPending($this));
        return $status;
    }

    /**
     * 标记审核拒绝
     * @return bool
     */
    public function markRejected(): bool
    {
        $this->status = Status::REJECTED;
        $status = $this->saveQuietly();
        Event::dispatch(new Events\CensorRejected($this));
        return $status;
    }

    /**
     * 获取状态Label
     * @return string[]
     */
    public static function getStatusLabels(): array
    {
        return [
            Status::PENDING => '待审核',
            Status::APPROVED => '已审核',
            Status::REJECTED => '拒绝',
        ];
    }

    /**
     * 获取状态Dot
     * @return string[]
     */
    public static function getStatusDots(): array
    {
        return [
            Status::PENDING => 'info',
            Status::APPROVED => 'success',
            Status::REJECTED => 'error',
        ];
    }
}