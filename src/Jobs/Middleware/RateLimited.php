<?php
/**
 * This is NOT a freeware, use is subject to license terms
 * @copyright Copyright (c) 2010-2099 Jinan Larva Information Technology Co., Ltd.
 * @link http://www.larva.com.cn/
 * @license http://www.larva.com.cn/license/
 */

namespace Larva\Censor\Jobs\Middleware;

use Illuminate\Support\Facades\Redis;

/**
 * 内容安全限速
 * @author Tongle Xu <xutongle@gmail.com>
 */
class RateLimited
{
    /** @var int QPS */
    protected $qps = 3;

    /**
     * 处理队列任务
     *
     * @param mixed $job
     * @param callable $next
     * @throws \Illuminate\Contracts\Redis\LimiterTimeoutException
     */
    public function handle($job, $next)
    {
        Redis::throttle('cms_censor')->allow($this->qps)->every(1)
            ->then(function () use ($job, $next) {
                // 获得锁，执行任务
                $next($job);
            }, function () use ($job) {
                // 无法获得锁
                $job->release(50);
            });
    }
}
