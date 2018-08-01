Yii2 必答支付的组件
============
> Yii2 必答支付的组件，仅适用于`必答支付`

Installation
------------

```
php composer.phar require --prefer-dist kriss/yii2-bd-payment -vvv
```

Usage
------------

### config

```php
use kriss\bd\payment\Payment;

$config = [
    'components' => [
        Payment::COMPONENT_NAME => [
            'class' => Payment::className(),
            'ak' => 'ak_20180110171926539048',
            'sk' => 'sk_609706e2f87affb77eefe5abd799057x',
            'logCategory' => 'bd-pay',
            'isTest' => true,
            'version' => '2.0',
        ]
    ]
]
```

### Start Pay

see [PayController.php](https://github.com/krissss/yii2-bd-payment/blob/master/examples/PayController.php)

see [OrderPayForm.php](https://github.com/krissss/yii2-bd-payment/blob/master/examples/OrderPayForm.php)

### Handle Notify

see [PayNotifyController.php](https://github.com/krissss/yii2-bd-payment/blob/master/examples/PayNotifyController.php)
