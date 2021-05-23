<?php
/**
 * This is NOT a freeware, use is subject to license terms
 * @copyright Copyright (c) 2010-2099 Jinan Larva Information Technology Co., Ltd.
 * @link http://www.larva.com.cn/
 * @license http://www.larva.com.cn/license/
 */

namespace Larva\Censor;

use Illuminate\Support\ServiceProvider;

/**
 * Class CensorServiceProvider
 * @author Tongle Xu <xutongle@gmail.com>
 */
class CensorServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap the application services.
     *
     * @return void
     */
    public function boot()
    {
        if ($this->app->runningInConsole()) {
            $this->loadMigrationsFrom(__DIR__ . '/../database/migrations');
            $this->publishes([
                __DIR__ . '/../resources/lang' => resource_path('lang'),
            ], 'censor-lang');
        }
        $this->loadTranslationsFrom(__DIR__ . '/../resources/lang', 'censor');

        //注册验证规则
        \Illuminate\Support\Facades\Validator::extend('text_censor', "\Larva\Censor\CensorValidator@validate");
    }

    /**
     * 获取提供器提供的服务。
     *
     * @return array
     */
    public function provides(): array
    {
        return [Censor::class];
    }

    /**
     * Register the service provider.
     *
     * @return void
     */
    public function register()
    {
        $this->app->singleton(Censor::class, function ($app) {
            return new Censor();
        });
    }
}
