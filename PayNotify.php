<?php

namespace kriss\bd\payment;

use Yii;
use yii\base\Exception;
use yii\helpers\Json;

class PayNotify
{
    /**
     * @param callable $callback
     * @return \yii\web\Response
     * @throws Exception
     */
    public static function handle(callable $callback)
    {
        $notify = Yii::$app->request->post();
        Yii::info('notify meta:' . Json::encode($notify), static::getLogCategory());

        // 校验签名
        $verify = self::makeSign($notify) == $notify['sign'];
        if (!$verify) {
            throw new Exception('签名校验失败');
        }

        $handleResult = call_user_func_array($callback, [$notify]);
        Yii::info('notify response:' . Json::encode($handleResult), static::getLogCategory());

        if (is_bool($handleResult) && $handleResult) {
            $response = 'success';
        } else {
            $response = 'fail';
        }
        Yii::$app->response->data = $response;
        return Yii::$app->response;
    }

    /**
     * @param $data
     * @return string
     */
    protected static function makeSign($data)
    {
        // 原所有数据除去 sign
        unset($data['sign']);
        // 原数据中增加 sk
        $data['sk'] = Yii::$app->get(Payment::COMPONENT_NAME)->sk;
        // 按字典排序
        ksort($data);
        // 转化成 appid=123&secret_key=123 的形式
        $queryStr = urldecode(http_build_query($data));
        // md5 加密
        $result = md5($queryStr);
        return $result;
    }

    /**
     * @return string
     */
    protected static function getLogCategory()
    {
        return Yii::$app->get(Payment::COMPONENT_NAME)->logCategory;
    }
}