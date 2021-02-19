<?php
/**
 * This is NOT a freeware,
 * @copyright Copyright (c) 2010-2099 Jinan Larva Information Technology Co., Ltd.
 * @license http://www.larva.com.cn/license/
 * @link http://www.larva.com.cn/
 */

namespace Larva\Censor\Models;

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
    const IGNORE = '{IGNORE}';

    /**
     * 审核
     */
    const MOD = '{MOD}';

    /**
     * 禁用
     */
    const BANNED = '{BANNED}';

    /**
     * 替换
     */
    const REPLACE = '{REPLACE}';

    /**
     * 为数组 / JSON 序列化准备日期。
     *
     * @param \DateTimeInterface $date
     * @return string
     */
    protected function serializeDate(\DateTimeInterface $date)
    {
        return $date->format($this->getDateFormat());
    }

    /**
     * UGC 处理方式
     * @return string[]
     */
    public static function getUGCActions()
    {
        return [
            static::IGNORE => '忽略不处理',
            static::MOD => '审核',
            static::BANNED => '禁用',
            static::REPLACE => '替换'
        ];
    }

    /**
     * Create a new stop word.
     *
     * @param string $ugc 用户内容处理方式
     * @param string $find 敏感词或查找方式
     * @param string $replacement 替换词或替换规则
     * @return static
     */
    public static function build($ugc, $find, $replacement)
    {
        $stopWord = new static;
        $stopWord->ugc = $ugc;
        $stopWord->find = $find;
        $stopWord->replacement = $replacement;
        return $stopWord;
    }
}
