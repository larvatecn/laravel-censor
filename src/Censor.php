<?php
/**
 * This is NOT a freeware, use is subject to license terms.
 *
 * @copyright Copyright (c) 2010-2099 Jinan Larva Information Technology Co., Ltd.
 */

namespace Larva\Censor;

use Larva\Baidu\Cloud\BaiduCloudHelper;
use Larva\Censor\Models\StopWord;
use Larva\TencentCloud\TencentCloud;
use Larva\TencentCloud\TencentCloudHelper;

/**
 * 内容审核
 * @author Tongle Xu <xutongle@gmail.com>
 */
class Censor
{
    /**
     * 是否合法（放入待审核）
     *
     * @var bool
     */
    public bool $isMod = false;

    /**
     * 触发的替换词
     *
     * @var array
     */
    public array $wordReplace = [];

    /**
     * 触发的审核词
     *
     * @var array
     */
    public array $wordMod = [];

    /**
     * 触发的禁用词
     *
     * @var array
     */
    public array $wordBanned = [];

    /**
     * 获取自身实例
     * @return Censor
     */
    public static function make(): Censor
    {
        return new Censor();
    }

    /**
     * Check text information.
     *
     * @param $content
     * @return string
     */
    public function textCensor($content): string
    {
        if (blank($content)) {
            return $content;
        }
        if (settings('system.local_censor', true)) {// 本地敏感词校验
            $content = $this->localStopWordsCheck($content);
        }
        //云验证
        if (settings('system.cloud_censor') == 'tencent') {
            $content = $this->tencentCloudTextCensor($content);
        } elseif (settings('system.cloud_censor') == 'baidu') {
            $content = $this->baiduCloudTextCensor($content);
        }

        // Delete repeated words
        $this->wordMod = array_unique($this->wordMod);
        return $content;
    }

    /**
     * 图片内容审查
     * @param string $path
     * @param false $isRemote
     */
    public function imageCensor(string $path, bool $isRemote = false)
    {
        if (settings('system.tencent_censor', true) && class_exists('\Larva\TencentCloud\TencentCloud')) {
            $this->tencentCloudImageCensor($path, $isRemote);
        } elseif (settings('system.baidu_censor', true) && class_exists('\Larva\Baidu\Cloud\BaiduCloud')) {
            $this->baiduCloudImageCensor($path, $isRemote);
        }
    }

    /**
     * 本地文本检查
     * @param string $content
     * @param bool $throw
     * @return string
     * @throws CensorNotPassedException
     */
    public function localStopWordsCheck(string $content, bool $throw = true): string
    {
        // 处理指定类型非忽略的敏感词
        StopWord::query()->where('ugc', '<>', StopWord::IGNORE)
            ->cursor()
            ->tapEach(function ($word) use (&$content) {
                // 转义元字符并生成正则
                $find = '/' . addcslashes($word->find, '\/^$()[]{}|+?.*') . '/i';
                if ($word->ugc == StopWord::REPLACE) {
                    $content = preg_replace($find, $word->replacement, $content);
                    // 记录触发的替换词
                    array_push($this->wordReplace, $word->find);
                } else {
                    if ($word->ugc == StopWord::MOD) {
                        if (preg_match($find, $content, $matches)) {
                            // 记录触发的审核词
                            array_push($this->wordMod, $word->find);
                            $this->isMod = true;
                        }
                    } elseif ($word->ugc == StopWord::BANNED) {
                        if (preg_match($find, $content, $matches)) {
                            // 记录触发的禁用词
                            array_push($this->wordBanned, $word->find);
                        }
                    }
                }
            })->each(function ($word) {
                // tapEach 尚未真正开始处理，在此处触发 tapEach
            });
        if ($throw && $this->wordBanned) {
            throw new CensorNotPassedException(trans('censor.content_banned'));
        }
        return $content;
    }

    /**
     * 腾讯云审核
     * @param string $content
     * @return string
     */
    public function tencentCloudTextCensor(string $content): string
    {
        $keyWords = TencentCloudHelper::textModeration($content);
        if (!blank($keyWords)) {
            // 记录触发的审核词
            $this->wordMod = array_merge($this->wordMod, $keyWords);
            $this->isMod = true;
        }
        return $content;
    }

    /**
     * 腾讯云图片审核
     * @param string $path
     * @param false $isRemote
     */
    public function tencentCloudImageCensor(string $path, bool $isRemote = false)
    {
        $result = TencentCloudHelper::ImageModeration($path, $isRemote);
        if (!$result) {
            $this->isMod = true;
        }
    }

    /**
     * 百度云审核
     * @param string $content
     * @return string
     */
    public function baiduCloudTextCensor(string $content): string
    {
        $keyWords = BaiduCloudHelper::keywordsExtraction($content);
        if (!blank($keyWords)) {
            // 记录触发的审核词
            $this->wordMod = array_merge($this->wordMod, $keyWords);
            $this->isMod = true;
        }
        return $content;
    }

    /**
     * 百度云图片审核
     * @param string $path
     * @param false $isRemote
     */
    public function baiduCloudImageCensor(string $path, bool $isRemote = false)
    {
        $status = BaiduCloudHelper::keywordsExtraction($path, $isRemote);
        if (!$status) {
            $this->isMod = true;
        }
    }

    /**
     * 腾讯云检验身份证号码和姓名是否真实
     *
     * @param string $identity 身份证号码
     * @param string $realName 姓名
     * @return array|\TencentCloud\Faceid\V20180301\Models\IdCardVerificationResponse
     */
    public function realCensor(string $identity, string $realName)
    {
        return TencentCloud::faceid()->idCardVerification($identity, $realName);
    }
}
