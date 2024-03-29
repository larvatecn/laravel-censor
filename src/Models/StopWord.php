<?php
/**
 * This is NOT a freeware, use is subject to license terms.
 *
 * @copyright Copyright (c) 2010-2099 Jinan Larva Information Technology Co., Ltd.
 */

namespace Larva\Censor\Models;

use DateTimeInterface;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;

/**
 * 敏感词模型
 * @property int $id 敏感词 id
 * @property string $ugc 用户内容处理方式
 * @property string $find 敏感词或查找方式
 * @property string $replacement 替换词或替换规则
 * @property Carbon $created_at 创建时间
 * @property Carbon $updated_at 更新时间
 * @author Tongle Xu <xutongle@gmail.com>
 */
class StopWord extends Model
{
    /**
     * 与模型关联的数据表。
     *
     * @var string
     */
    protected $table = 'stop_words';

    /**
     * @var array
     */
    protected $fillable = ['ugc', 'find', 'replacement'];

    /**
     * 忽略、不处理
     */
    public const IGNORE = '{IGNORE}';

    /**
     * 审核
     */
    public const MOD = '{MOD}';

    /**
     * 禁用
     */
    public const BANNED = '{BANNED}';

    /**
     * 替换
     */
    public const REPLACE = '{REPLACE}';

    /**
     * 支持的操作
     */
    public const ACTIONS = [
        self::IGNORE => '忽略不处理',
        self::MOD => '审核',
        self::BANNED => '禁用',
        self::REPLACE => '替换'
    ];

    /**
     * 为数组 / JSON 序列化准备日期。
     *
     * @param DateTimeInterface $date
     * @return string
     */
    protected function serializeDate(DateTimeInterface $date): string
    {
        return $date->format($this->getDateFormat());
    }

    /**
     * Create a new stop word.
     *
     * @param string $ugc 用户内容处理方式
     * @param string $find 敏感词或查找方式
     * @param string $replacement 替换词或替换规则
     * @return static
     */
    public static function build(string $ugc, string $find, string $replacement): StopWord
    {
        $stopWord = new static();
        $stopWord->ugc = $ugc;
        $stopWord->find = $find;
        $stopWord->replacement = $replacement;
        return $stopWord;
    }
}
