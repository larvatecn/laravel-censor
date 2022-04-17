# laravel-censor

适用于 Laravel 的内容安全审查扩展。

<p align="center">
    <a href="https://packagist.org/packages/larva/laravel-censor"><img src="https://poser.pugx.org/larva/laravel-censor/v/stable" alt="Stable Version"></a>
    <a href="https://packagist.org/packages/larva/laravel-censor"><img src="https://poser.pugx.org/larva/laravel-censor/downloads" alt="Total Downloads"></a>
    <a href="https://packagist.org/packages/larva/laravel-censor"><img src="https://poser.pugx.org/larva/laravel-censor/license" alt="License"></a>
</p>

[![License](https://poser.pugx.org/larva/laravel-censor/license.svg)](https://packagist.org/packages/larva/laravel-censor)
[![Latest Stable Version](https://poser.pugx.org/larva/laravel-censor/v/stable.png)](https://packagist.org/packages/larva/laravel-censor)
[![Total Downloads](https://poser.pugx.org/larva/laravel-censor/downloads.png)](https://packagist.org/packages/larva/laravel-censor)

## 环境需求

- PHP >= 7.4

## 安装

```bash
composer require larva/laravel-censor -vv
```

## 使用

### 自动审核
```php
use Larva\Censor\Censor;
$censor = Censor::make();
$content= '赚钱啦';
try {
  $content = $censor->textCensor($content);
  if($censor->isMod){
    //需要审核
  }
} catch (CensorNotPassedException $e) {
    //有违禁词
}
```

### 表单验证

表单验证只检查是否有违禁词，需要审核的直接是放行的，使用本扩展，再用户发布内容后，建议使用队列异步审核一遍。

```php
$request->validate([
    'name' => ['required', 'string', new TextCensorRule],
]);

$request->validate([
    'name' => ['required', 'string', 'text_censor'],
]);
```
