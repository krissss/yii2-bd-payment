<?php

namespace kriss\bd\payment;

use Yii;
use yii\base\Exception;

class PayNotify
{
    /**
     * @param callable $callback
     * @param null $paymentClass
     * @return \yii\web\Response
     * @throws Exception
     * @throws \yii\base\InvalidConfigException
     */
    public static function handle(callable $callback, $paymentClass = null)
    {
        $notify = Yii::$app->request->post();
        Support::logger('notify meta', $notify, $paymentClass);

        // 校验签名
        $sk = Payment::getInstance($paymentClass)->sk;
        $verify = Support::makeSign($sk, $notify) == $notify['sign'];
        if (!$verify) {
            throw new Exception('签名校验失败');
        }

        $handleResult = call_user_func_array($callback, [$notify]);
        Support::logger('notify response', $handleResult, $paymentClass);

        if (is_bool($handleResult) && $handleResult) {
            $response = 'success';
        } else {
            $response = 'fail';
        }
        Yii::$app->response->data = $response;
        return Yii::$app->response;
    }
}
