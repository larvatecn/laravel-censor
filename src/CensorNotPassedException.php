<?php
/**
 * This is NOT a freeware, use is subject to license terms
 * @copyright Copyright (c) 2010-2099 Jinan Larva Information Technology Co., Ltd.
 * @link http://www.larva.com.cn/
 */

namespace Larva\Censor;

use Exception;

/**
 * Class CensorNotPassedException
 * @author Tongle Xu <xutongle@gmail.com>
 */
class CensorNotPassedException extends Exception
{
    /**
     * Constructor.
     * @param string $message
     * @param int $code
     * @param Exception|null $previous
     */
    public function __construct(string $message = '', int $code = 500, Exception $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }
}
