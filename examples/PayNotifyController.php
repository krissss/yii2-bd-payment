<?php

namespace frontend\controllers;

use common\models\Order;
use kriss\bd\payment\Payment;
use kriss\bd\payment\PayNotify;
use Yii;
use yii\base\Controller;
use yii\helpers\Json;

class PayNotifyController extends Controller
{
    public function actionIndex()
    {
        $response = PayNotify::handle(function ($notify) {
            $orderId = $notify['out_trade_no'];

            $order = Order::findOne(['id' => $orderId]);
            if (!$order) {
                Yii::error('回调订单不存在:' . $orderId, $this->getLogCategory());
                return true;
            }
            if ($order->status != Order::STATUS_WAIT_PAY) {
                Yii::error('订单已支付:' . Json::encode($order), $this->getLogCategory());
                return true;
            }

            // TODO 修改订单状态等操作

            return true;
        });
        $response->send();
    }

    /**
     * @return string
     */
    protected function getLogCategory()
    {
        return Yii::$app->get(Payment::COMPONENT_NAME)->logCategory;
    }
}