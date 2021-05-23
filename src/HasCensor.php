<?php
/**
 * This is NOT a freeware, use is subject to license terms
 * @copyright Copyright (c) 2010-2099 Jinan Larva Information Technology Co., Ltd.
 * @link http://www.larva.com.cn/
 */

namespace Larva\Censor;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

/**
 * 内容审查
 * @property boolean $status 状态：0 待审核 1 审核通过 2 拒绝 3 推迟审核
 * @method static Builder approve() 审核通过的
 * @method static Builder pending() 待审核的
 * @method static Builder postponed() 被推迟的
 * @method static Builder rejected() 审核拒绝的
 * @mixin  Model
 * @author Tongle Xu <xutongle@gmail.com>
 */
trait HasCensor
{
    /**
     * 查询待审核的
     * @param Builder $query
     * @return Builder
     */
    public function scopePending(Builder $query): Builder
    {
        return $query->whereIn('status', [Status::PENDING,Status::POSTPONED]);
    }

    /**
     * 查询被推迟的
     * @param Builder $query
     * @return Builder
     */
    public function scopePostponed(Builder $query): Builder
    {
        return $query->whereIn('status', [Status::PENDING,Status::POSTPONED]);
    }

    /**
     * 查询审核通过的
     * @param Builder $query
     * @return Builder
     */
    public function scopeApprove(Builder $query): Builder
    {
        return $query->where('status', Status::APPROVED);
    }

    /**
     * 查询审核不通过
     * @param Builder $query
     * @return Builder
     */
    public function scopeRejected(Builder $query): Builder
    {
        return $query->where('status', Status::REJECTED);
    }

    /**
     * 标记已审核
     * @return bool
     */
    public function markApproved(): bool
    {
        $status = $this->forceFill([
            'status' => Status::APPROVED,
        ])->save();
        event(new Events\Approved($this));
        return $status;
    }

    /**
     * 标记延迟审核
     * @return bool
     */
    public function markPostponed(): bool
    {
        $status = $this->forceFill([
            'status' => Status::POSTPONED,
        ])->save();
        event(new Events\Postponed($this));
        return $status;
    }

    /**
     * 标记待审核
     * @return bool
     */
    public function markPending(): bool
    {
        $status = $this->forceFill([
            'status' => Status::PENDING,
        ])->save();
        event(new Events\Pending($this));
        return $status;
    }

    /**
     * 标记审核拒绝
     * @return bool
     */
    public function markRejected(): bool
    {
        $status = $this->forceFill([
            'status' => Status::REJECTED,
        ])->save();
        event(new Events\Rejected($this));
        return $status;
    }
}