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
 * @property boolean $status 状态：0 待审核 1 审核通过 2 拒绝 3 人工复审
 * @property ContentCensor $stopWords 触发的审核词
 * @property-read boolean $isApproved
 * @property-read boolean $isPending
 * @property-read boolean $isPostponed
 * @property-read boolean $isRejected
 *
 * @method static Builder approve() 审核通过的
 * @method static Builder pending() 待审核的
 * @method static Builder postponed() 需要人工复审的
 * @method static Builder rejected() 审核拒绝的
 *
 * @mixin  Model
 * @author Tongle Xu <xutongle@gmail.com>
 */
trait HasContentCensor
{
    /**
     * @var string 内容审查的列
     */
    protected $censorColumns = [];

    /**
     * @var string 状态字段
     */
    protected $statusColumn = 'status';

    /**
     * Boot the trait.
     *
     * Listen for the deleting event of a model, then remove the relation between it and tags
     */
    protected static function bootHasContentCensor(): void
    {
        static::saved(function ($model) {
            if ($model->isPending) {
                $model->contentCensor();
            }
        });
    }

    /**
     * 内容审查
     */
    public function contentCensor()
    {
        //检查标记的字段
        foreach ($this->censorColumns as $field) {
            $stopWords = [];
            $censor = Censor::make();
            try {
                $this->attributes[$field] = $censor->textCensor($this->attributes[$field]);
                if ($censor->isMod) {//如果标题命中了关键词就放入待审核
                    $stopWords = array_unique($censor->wordMod);
                    $this->markPending();
                }
            } catch (CensorNotPassedException $e) {
                $stopWords = array_unique($censor->wordBanned);
                $this->markRejected();
            }

            // 记录触发的审核词
            if (($this->attributes[$this->statusColumn] != CensorStatus::APPROVED) && $stopWords) {
                $this->stopWords->stop_word = implode(',', $stopWords);
                $this->stopWords->saveQuietly();
            }
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
        return $query->whereIn($this->statusColumn, [CensorStatus::PENDING, CensorStatus::POSTPONED]);
    }

    /**
     * 查询被推迟的
     * @param Builder $query
     * @return Builder
     */
    public function scopePostponed(Builder $query): Builder
    {
        return $query->whereIn($this->statusColumn, [CensorStatus::PENDING, CensorStatus::POSTPONED]);
    }

    /**
     * 查询审核通过的
     * @param Builder $query
     * @return Builder
     */
    public function scopeApprove(Builder $query): Builder
    {
        return $query->where($this->statusColumn, CensorStatus::APPROVED);
    }

    /**
     * 查询审核不通过
     * @param Builder $query
     * @return Builder
     */
    public function scopeRejected(Builder $query): Builder
    {
        return $query->where($this->statusColumn, CensorStatus::REJECTED);
    }

    /**
     * 是否已审核
     * @return bool
     */
    public function getIsApprovedAttribute(): bool
    {
        return $this->attributes[$this->statusColumn] == CensorStatus::APPROVED;
    }

    /**
     * 是否待审核
     * @return bool
     */
    public function getIsPendingAttribute(): bool
    {
        return $this->attributes[$this->statusColumn] == CensorStatus::PENDING;
    }

    /**
     * 是否需要人工审核
     * @return bool
     */
    public function getIsPostponedAttribute(): bool
    {
        return $this->attributes[$this->statusColumn] == CensorStatus::POSTPONED;
    }

    /**
     * 是否已拒绝
     * @return bool
     */
    public function getIsRejectedAttribute(): bool
    {
        return $this->attributes[$this->statusColumn] == CensorStatus::REJECTED;
    }

    /**
     * 标记已审核
     * @return bool
     */
    public function markApproved(): bool
    {
        $this->attributes[$this->statusColumn] = CensorStatus::APPROVED;
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
        $this->attributes[$this->statusColumn] = CensorStatus::POSTPONED;
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
        $this->attributes[$this->statusColumn] = CensorStatus::PENDING;
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
        $this->attributes[$this->statusColumn] = CensorStatus::REJECTED;
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
            CensorStatus::PENDING => '待审核',
            CensorStatus::APPROVED => '已审核',
            CensorStatus::REJECTED => '拒绝',
        ];
    }

    /**
     * 获取状态Dot
     * @return string[]
     */
    public static function getStatusDots(): array
    {
        return [
            CensorStatus::PENDING => 'info',
            CensorStatus::APPROVED => 'success',
            CensorStatus::REJECTED => 'error',
        ];
    }
}