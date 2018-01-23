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
        ]
    ]
]
```

### use

```php
use kriss\bd\payment\Payment;

$payment = Yii::$app->get(Payment::COMPONENT_NAME);
$payParams = [
    'pay_type' => Payment::PAY_TYPE_WECHAT_MP,
    'out_trade_no' => $order->id,
    'total_fee' => $order->pay_price * 100,
    'body' => Yii::$app->name . '订单支付',
    'product_id' => $order->goods_id,
];
if ($this->return_url) {
    $payParams['return_url'] = $this->return_url;
}
$payment->setAttributes($payParams);
return $payment->pay();
```