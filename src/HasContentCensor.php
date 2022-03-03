<?php
/**
 * This is NOT a freeware, use is subject to license terms
 * @copyright Copyright (c) 2010-2099 Jinan Larva Information Technology Co., Ltd.
 */

namespace Larva\Censor;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphOne;
use Illuminate\Support\Facades\Event;
use Larva\Censor\Jobs\CensorJob;
use Larva\Censor\Models\ContentCensor;

/**
 * 内容审查
 * @property array $censorColumns
 * @property bool $status 状态：0 待审核 1 审核通过 2 拒绝 3 人工复审
 * @property ContentCensor $stopWords 触发的审核词
 * @property-read bool $isApproved 已审核
 * @property-read bool $isPending 待审核
 * @property-read bool $isPostponed 待人工审核
 * @property-read bool $isRejected 已拒绝
 *
 * @method static Builder approved() 审核通过的
 * @method static Builder pending() 待审核的
 * @method static Builder postponed() 需要人工复审的
 * @method static Builder rejected() 审核拒绝的
 *
 * @mixin  Model
 * @see Model
 * @author Tongle Xu <xutongle@gmail.com>
 */
trait HasContentCensor
{
    /**
     * Boot the trait.
     *
     * Listen for the deleting event of a model, then remove the relation between it and tags
     */
    protected static function bootHasContentCensor(): void
    {
        self::created(function ($model) {
            $model->stopWords()->create();
        });
        self::saved(function ($model) {
            $model->markPending();
        });
    }

    /**
     * 内容审查
     */
    public function contentCensor()
    {
        if (empty($this->censorColumns ?? [])) {
            return;
        }
        //检查标记的字段
        $stopWords = [];
        $censor = Censor::make();
        foreach ($this->censorColumns as $field) {
            try {
                $this->attributes[$field] = $censor->textCensor($this->attributes[$field]);
                if ($censor->isMod) {//如果标题命中了关键词就放入人工审核
                    $stopWords = array_unique($censor->wordMod);
                    $this->markPostponed();
                    continue;
                }
            } catch (CensorNotPassedException $e) {
                $stopWords = array_unique($censor->wordBanned);
                $this->markRejected();
                continue;
            }
        }
        // 记录命中的词
        if ($stopWords) {
            $this->stopWords->stop_word = implode(',', $stopWords);
            $this->stopWords->saveQuietly();
        } else {
            $this->markApproved();
        }
    }

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
        return $query->whereIn('status', [Status::PENDING, Status::POSTPONED]);
    }

    /**
     * 查询审核通过的
     * @param Builder $query
     * @return Builder
     */
    public function scopeApproved(Builder $query): Builder
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
     * 是否已审核
     * @return bool
     */
    public function getIsApprovedAttribute(): bool
    {
        return $this->attributes['status'] == Status::APPROVED;
    }

    /**
     * 是否待审核
     * @return bool
     */
    public function getIsPendingAttribute(): bool
    {
        return $this->attributes['status'] == Status::PENDING;
    }

    /**
     * 是否需要人工审核
     * @return bool
     */
    public function getIsPostponedAttribute(): bool
    {
        return $this->attributes['status'] == Status::POSTPONED;
    }

    /**
     * 是否已拒绝
     * @return bool
     */
    public function getIsRejectedAttribute(): bool
    {
        return $this->attributes['status'] == Status::REJECTED;
    }

    /**
     * 获取审核状态文本标识
     * @return string
     */
    public function getStatusTextAttribute(): string
    {
        return Status::MAPS[$this->status] ?? '';
    }

    /**
     * 标记已审核
     * @return bool
     */
    public function markApproved(): bool
    {
        $this->attributes['status'] = Status::APPROVED;
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
        $this->attributes['status'] = Status::POSTPONED;
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
        $this->attributes['status'] = Status::PENDING;
        $status = $this->saveQuietly();
        CensorJob::dispatch($this);//委派审核队列
        Event::dispatch(new Events\CensorPending($this));
        return $status;
    }

    /**
     * 标记审核拒绝
     * @return bool
     */
    public function markRejected(): bool
    {
        $this->attributes['status'] = Status::REJECTED;
        $status = $this->saveQuietly();
        Event::dispatch(new Events\CensorRejected($this));
        return $status;
    }

    /**
     * 获取状态Dot
     * @return string[]
     */
    public static function getStatusDots(): array
    {
        return [
            Status::POSTPONED => 'warning',
            Status::PENDING => 'info',
            Status::APPROVED => 'success',
            Status::REJECTED => 'error',
        ];
    }
}