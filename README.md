yii2-thrift
===========
yii2 extension for thrift
基于yii2.0的thrift扩展

安装
------------

The preferred way to install this extension is through [composer](http://getcomposer.org/download/).

Either run

```
composer require --prefer-dist tengpao/yii2-thrift "*"
```

or add

```
"tengpao/yii2-thrift": "*"
```

to the require section of your `composer.json` file.


使用
-----

在配置文件中components中增加配置:

```php
'thrift' => [
    'class' => 'tengpao\thrift\ThriftClient'
    'idService' => [
        'defineName' => 'shared',
        'clientName' => '\tutorial\CalculatorClient'
    ],
]
```
调用:
```php
\Yii::$app->thrift->idService->method();
```
如果不想在配置文件中全局配置,可以使用
```
$thrift = \Yii::createObject([
    'class' => 'tengpao\thrift\ThriftClient'
    'idService' => [
        'defineName' => 'shared',
        'clientName' => '\tutorial\CalculatorClient'
    ],
]);

$thrift->idService->method();
```

注,如果不是通过composer安装的需要添加相关别名指向

QQ交流群: 325914002
