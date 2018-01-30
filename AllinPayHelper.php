<?php

namespace kriss\bd\payment;

use Yii;
use yii\base\Exception;
use yii\httpclient\Client;

class AllinPayHelper
{
    const REGISTER_USER_URL = 'http://pay.bidanet.com/allin-pay/register-user';

    /**
     * 获取用户id
     * @param $userIdentity
     * @return mixed
     */
    public static function getUserId($userIdentity)
    {
        /** @var Payment $payment */
        $payment = Yii::$app->get(Payment::COMPONENT_NAME);
        $client = new Client();
        $response = $client->createRequest()
            ->setMethod('post')
            ->setUrl(static::REGISTER_USER_URL)
            ->setData([
                'ak' => $payment->ak,
                'user_identity' => $userIdentity,
            ])
            ->send();
        Yii::info('allinPay register user response:' . $response->content, static::getLogCategory());
        if ($response->isOk) {
            $responseJson = json_decode($response->content, true);
            if ($responseJson['status'] == 200) {
                return $responseJson['data'];
            }
            throw new Exception($responseJson['msg']);
        }
        throw new Exception('请求失败');
    }

    /**
     * @return string
     */
    protected static function getLogCategory()
    {
        return Yii::$app->get(Payment::COMPONENT_NAME)->logCategory;
    }
}