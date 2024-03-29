<?php
/**
 * This is NOT a freeware, use is subject to license terms.
 *
 * @copyright Copyright (c) 2010-2099 Jinan Larva Information Technology Co., Ltd.
 */

namespace Larva\Censor\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;

/**
 * 内容安全表
 * @property int $id
 * @property int $source_id
 * @property string $source_type
 * @property string $stop_word
 *
 * @author Tongle Xu <xutongle@gmail.com>
 */
class ContentCensor extends Model
{
    /**
     * 与模型关联的数据表。
     *
     * @var string
     */
    protected $table = 'content_censor';

    /**
     * 可以批量赋值的属性
     *
     * @var array
     */
    protected $fillable = [
        'source_id', 'source_type', 'stop_word'
    ];

    /**
     * 多态关联
     * @return MorphTo
     */
    public function source(): MorphTo
    {
        return $this->morphTo();
    }
}
