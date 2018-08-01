<?php

namespace kriss\bd\payment;

use Yii;
use yii\helpers\Json;

class Support
{
    /**
     * 签名
     * @param $sk
     * @param $data
     * @return string
     */
    public static function makeSign($sk, $data)
    {
        // 原所有数据除去 sign
        unset($data['sign']);
        // 原数据中增加 sk
        $data['sk'] = $sk;
        // 按字典排序
        ksort($data);
        // 转化成 appid=123&secret_key=123 的形式
        $queryStr = urldecode(http_build_query($data));
        // md5 加密
        $result = md5($queryStr);
        return $result;
    }

    /**
     * @param $title
     * @param $data
     * @param string $type
     */
    public static function logger($title, $data, $type = 'info')
    {
        $logCategory = Yii::$app->get(Payment::COMPONENT_NAME)->logCategory;
        Yii::$type($title . ':' . Json::encode($data), $logCategory);
    }
}
