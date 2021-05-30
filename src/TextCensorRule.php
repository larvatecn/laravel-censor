<?php
/**
 * This is NOT a freeware, use is subject to license terms
 * @copyright Copyright (c) 2010-2099 Jinan Larva Information Technology Co., Ltd.
 * @link http://www.larva.com.cn/
 */

namespace Larva\Censor;

/**
 * 验证规则
 * @author Tongle Xu <xutongle@gmail.com>
 */
class TextCensorRule implements \Illuminate\Contracts\Validation\Rule
{
    /**
     * 验证输入是否合法
     * @param string $attribute
     * @param mixed $value
     * @return bool
     */
    public function passes($attribute, $value): bool
    {
        try {
            Censor::make()->localStopWordsCheck($value);
            return true;
        } catch (CensorNotPassedException $e) {
            return false;
        }
    }

    /**
     * 校验错误提示信息
     * @return array|string
     */
    public function message()
    {
        return trans('censor.words_banned');
    }
}
