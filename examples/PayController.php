<?php

namespace kriss\bd\payment\examples;

use common\models\Order;
use frontend\components\AuthRestController;
use kriss\behaviors\rest\PostVerbFilter;
use Yii;
use yii\base\Exception;

class PayController extends AuthRestController
{
    public function behaviors()
    {
        return array_merge(parent::behaviors(), [
            'postVerbFilter' => [
                'class' => PostVerbFilter::className(),
                'actions' => ['order', 'order-check'],
            ]
        ]);
    }

    // 订单支付
    public function actionOrder()
    {
        $model = new OrderPayForm([
            'userId' => Yii::$app->user->getId()
        ]);

        if ($model->load(Yii::$app->request->post(), '') && $model->validate()) {
            $result = $model->pay();
            if ($result) {
                return $result;
            }
        }

        return $model;
    }

    // 订单检查是否支付成功
    public function actionOrderCheck()
    {
        $orderId = Yii::$app->request->post('order_id');
        if (!$orderId) {
            throw new Exception('order_id 必须');
        }

        $order = Order::findOne(['user_id' => Yii::$app->user->id, 'id' => $orderId]);
        if (!$order) {
            return $this->validateError('订单不存在');
        }
        if (in_array($order->status, [Order::STATUS_WAIT_PIN, Order::STATUS_WAIT_DELIVER])) {
            return 1;
        } elseif ($order->status == Order::STATUS_WAIT_PAY) {
            return 0;
        } else {
            return $this->validateError('订单状态不正确');
        }
    }

}