Yii2 必答支付的组件
============
> Yii2 必答支付的组件，仅适用于`必答支付`

Installation
------------

```
php composer.phar require --prefer-dist kriss/yii2-bd-payment -vvv
```

Basic Usage
------------

### Config

```php
use kriss\bd\payment\Payment;

$config = [
    'components' => [
        Payment::COMPONENT_NAME => [
            'class' => Payment::class,
            'ak' => 'ak_20180110171926539048',
            'sk' => 'sk_609706e2f87affb77eefe5abd799057x',
            'logCategory' => 'bd-pay',
            'isTest' => true,
            'version' => '2.0',
        ]
    ]
]
```

### Get Component

```php
$payment = Payment::getInstance();
```

### Start Pay

see [PayController.php](https://github.com/krissss/yii2-bd-payment/blob/master/examples/PayController.php)

see [OrderPayForm.php](https://github.com/krissss/yii2-bd-payment/blob/master/examples/OrderPayForm.php)

### Handle Notify

see [PayNotifyController.php](https://github.com/krissss/yii2-bd-payment/blob/master/examples/PayNotifyController.php)


Use More Than One Component
------------

### Create another class extend `Payment`

```php
namespace common\components;

use kriss\bd\payment\Payment;

class AnotherPayment extends Payment
{
    const COMPONENT_NAME = 'another-db-payment';
}
```

### Config

```php
use kriss\bd\payment\Payment;

$config = [
    'components' => [
        Payment::COMPONENT_NAME => [
            'class' => Payment::class,
            'ak' => 'ak_20180110171926539048',
            'sk' => 'sk_609706e2f87affb77eefe5abd799057x',
            'logCategory' => 'bd-pay',
            'isTest' => true,
            'version' => '2.0',
        ],
        AnotherPayment::COMPONENT_NAME => [
            'class' => AnotherPayment::class,
            'ak' => 'ak_20180110171926539049',
            'sk' => 'sk_609706e2f87affb77eefe5abd799057y',
            'logCategory' => 'another-bd-pay',
            'isTest' => true,
            'version' => '2.0',
        ],
    ]
]
```

### Get Component

```php
$payment = Payment::getInstance();
$anotherPayment = AnotherPayment::getInstance();
```
