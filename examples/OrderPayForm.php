<?php

namespace frontend\models\form;

use common\models\Order;
use kriss\bd\payment\Payment;
use Yii;
use yii\base\InvalidConfigException;
use yii\base\Model;

class OrderPayForm extends Model
{
    public $userId;

    public $order_id;

    public $pay_type;

    public $return_url;

    public function init()
    {
        parent::init();
        if (!$this->userId) {
            throw new InvalidConfigException('必须配置 user_id');
        }
    }

    public function rules()
    {
        return [
            [['order_id', 'pay_type'], 'required'],
            [['order_id', 'pay_type'], 'integer'],
            [['return_url'], 'url'],
        ];
    }

    public function attributeLabels()
    {
        return [
            'order_id' => '订单编号',
            'pay_type' => '支付方式',
            'return_url' => '返回地址',
        ];
    }

    /**
     * 支付
     * @return array|string
     */
    public function pay()
    {
        $order = Order::find()->where(['id' => $this->order_id, 'user_id' => $this->userId])->one();
        if (!$order) {
            $this->addError('order_id', '订单未找到');
            return false;
        }
        if ($order->status != Order::STATUS_WAIT_PAY) {
            $this->addError('order_id', '订单当前不可支付');
            return false;
        }
        if ($order->pin_over_at > time()) {
            $this->addError('order_id', '订单已超拼单结束时间，不能发起支付');
            return false;
        }
        if ($order->pay_price <= 0) {
            $this->addError('order_id', '订单需要支付的金额小于0，不需要发起支付');
            return false;
        }

        $payment = Yii::$app->get(Payment::COMPONENT_NAME);
        $payParams = [
            'pay_type' => $this->pay_type,
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
    }

}