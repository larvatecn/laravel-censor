<?php
/**
 * This is NOT a freeware, use is subject to license terms
 * @copyright Copyright (c) 2010-2099 Jinan Larva Information Technology Co., Ltd.
 * @link http://www.larva.com.cn/
 * @license http://www.larva.com.cn/license/
 */

namespace Larva\Censor;

use Illuminate\Support\Arr;
use Larva\Censor\Models\StopWord;

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
    public $isMod = false;

    /**
     * 触发的替换词
     *
     * @var array
     */
    public $wordReplace = [];

    /**
     * 触发的审核词
     *
     * @var array
     */
    public $wordMod = [];

    /**
     * 触发的禁用词
     *
     * @var array
     */
    public $wordBanned = [];

    /**
     * 获取自身实例
     * @return Censor
     */
    public static function make()
    {
        return new Censor();
    }

    /**
     * Check text information.
     *
     * @param $content
     * @return string
     * @throws CensorNotPassedException
     */
    public function textCensor($content)
    {
        if (blank($content)) {
            return $content;
        }
        if (settings('system.local_censor', true)) {// 本地敏感词校验
            $content = $this->localStopWordsCheck($content);
        }
        //云验证只检查一个平台
        if (settings('system.tencent_censor', true) && class_exists('\Larva\TencentCloud\TencentCloud')) {
            $content = $this->tencentCloudTextCensor($content);
        } else if (settings('system.baidu_censor', true) && class_exists('\Larva\Baidu\Cloud\Bce')) {
            $content = $this->baiduCloudTextCensor($content);
        }

        // Delete repeated words
        $this->wordMod = array_unique($this->wordMod);
        return $content;
    }

    /**
     * @param string $path
     * @param false $isRemote
     */
    public function imageCensor(string $path, $isRemote = false)
    {
        if (settings('system.tencent_censor', true) && class_exists('\Larva\TencentCloud\TencentCloud')) {
            $this->tencentCloudImageCensor($path, $isRemote);
        }
    }

    /**
     * 本地文本检查
     * @param string $content
     * @return string
     */
    public function localStopWordsCheck(string $content)
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
                            throw new CensorNotPassedException(trans('censor.content_banned'));
                        }
                    }
                }
            })->each(function ($word) {
                // tapEach 尚未真正开始处理，在此处触发 tapEach
            });
        return $content;
    }

    /**
     * 腾讯云审核
     * @param string $content
     * @return string
     */
    public function tencentCloudTextCensor(string $content)
    {
        $result = \Larva\TencentCloud\TencentCloudHelper::textModeration($content);
        $keyWords = Arr::get($result, 'Data.Keywords', []);

        if (isset($result['Data']['DetailResult'])) {
            /**
             * filter 筛选腾讯云敏感词类型范围
             * Normal：正常，Polity：涉政，Porn：色情，Illegal：违法，Abuse：谩骂，Terror：暴恐，Ad：广告，Custom：自定义关键词
             */
            $filter = ['Normal', 'Ad']; // Tag Setting 可以放入配置
            $filtered = collect($result['Data']['DetailResult'])->filter(function ($item) use ($filter) {
                if (in_array($item['EvilLabel'], $filter)) {
                    $item = [];
                }
                return $item;
            });
            $detailResult = $filtered->pluck('Keywords');
            $detailResult = Arr::collapse($detailResult);
            $keyWords = array_merge($keyWords, $detailResult);
        }
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
    public function tencentCloudImageCensor($path, $isRemote = false)
    {
        $params = [];
        if ($isRemote) {
            $params['FileUrl'] = $path;
        } else {
            $params['FileContent'] = base64_encode(file_get_contents($path));
        }
        $result = \Larva\TencentCloud\TencentCloudHelper::ImageModeration($params);
        if (Arr::get($result, 'Data.EvilType') != 100) {
            $this->isMod = true;
        }
    }

    /**
     * 百度云审核
     * @param string $content
     * @return string
     */
    public function baiduCloudTextCensor(string $content)
    {
        $response = \Larva\Baidu\Cloud\BaiduCloud::get('nlp')->textCensor($content);
        $keyWords = [];
        if ($response['conclusionType'] != 1 && isset($response['data'])) {//不合规
            foreach ($response['data'] as $res) {
                $hits = array_shift($res['hits']);
                $keyWords = array_merge($keyWords, $hits['words']);
            }
        }
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
    public function baiduCloudImageCensor($path, $isRemote = false)
    {
        if ($isRemote) {
            $path = base64_encode(file_get_contents($path));
        }
        $response = \Larva\Baidu\Cloud\BaiduCloud::get('nlp')->imageCensor($path);
        if ($response['conclusionType'] != 1 && isset($response['data'])) {//不合规
            $this->isMod = true;
        }
    }

    /**
     * 腾讯云检验身份证号码和姓名是否真实
     *
     * @param string $identity 身份证号码
     * @param string $realName 姓名
     * @return array
     */
    public function realCensor($identity, $realName)
    {
        return \Larva\TencentCloud\TencentCloud::faceid()->idCardVerification($identity, $realName);
    }
}
