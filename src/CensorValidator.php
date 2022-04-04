<?php
/**
 * This is NOT a freeware, use is subject to license terms.
 *
 * @copyright Copyright (c) 2010-2099 Jinan Larva Information Technology Co., Ltd.
 */

namespace Larva\Censor;

/**
 * 检查是否有禁用的违禁词
 * @author Tongle Xu <xutongle@gmail.com>
 */
class CensorValidator
{
    /**
     * @param $attribute
     * @param $value
     * @param $parameters
     * @param $validator
     * @return bool
     */
    public function validate($attribute, $value, $parameters, $validator): bool
    {
        try {
            Censor::make()->localStopWordsCheck($value);
            return true;
        } catch (CensorNotPassedException $e) {
            return false;
        }
    }
}
