<?php

namespace kriss\bd\payment\models;

use Yii;
use yii\base\Exception;
use yii\base\InvalidConfigException;
use yii\helpers\Html;

class PayForm extends AbstractForm
{
    // 微信支付
    const PAY_TYPE_WECHAT_MP = 10;
    const PAY_TYPE_WECHAT_WAP = 11;
    const PAY_TYPE_WECHAT_APP = 12;
    const PAY_TYPE_WECHAT_SCAN = 13;
    const PAY_TYPE_WECHAT_MINI_APP = 14;
    const PAY_TYPE_WECHAT_TRANSFER = 15;
    // 支付宝支付
    const PAY_TYPE_ALIPAY_WEB = 20;
    const PAY_TYPE_ALIPAY_WAP = 21;
    const PAY_TYPE_ALIPAY_APP = 22;
    const PAY_TYPE_ALIPAY_SCAN = 23;
    // 通联支付
    const PAY_TYPE_ALLIN_PAY_H5 = 100;

    /**
     * 支付方式
     * @var integer
     */
    public $pay_type;
    /**
     * 订单号
     * @var string
     */
    public $out_trade_no;
    /**
     * 支付标题信息
     * 支付宝：subject
     * @var string
     */
    public $body;
    /**
     * 金额，单位（分）
     * 支付宝：total_amount（元）
     * @var int
     */
    public $total_fee;
    /**
     * 附带数据，最终会原样返回
     * 支付宝：passback_params
     * @var string
     */
    public $attach;
    /**
     * 客户端ip，可以为空，默认为当前请求的ip
     * 为 false 固定不传（在 cli 下可能没有 request 组件时有用）
     * @var string
     */
    public $spbill_create_ip;
    /**
     * 小程序的 code，为了获取 openid
     * @link https://mp.weixin.qq.com/debug/wxadoc/dev/api/api-login.html?t=20161122
     * @var string
     */
    public $mini_app_code;
    /**
     * 产品id，微信扫码支付必须
     * @var string
     */
    public $product_id;
    /**
     * 通联支付用户id，通联H5支付必须
     * @var string
     */
    public $allin_pay_user_id;
    /**
     * 转账收款人的openid
     * @var string
     */
    public $transfer_openid;
    /**
     * 转账类型，如果需要通过 APP/小程序 的商户账号appid进行转账，传入 app/miniapp
     * @var string
     */
    public $transfer_type;
    /**
     * 微信转账
     * 是否校验真实姓名， 0 不校验，1 强制校验
     * @var int
     */
    public $transfer_check_name = 0;
    /**
     * 微信转账
     * 校验真实姓名时的用户真实姓名
     * @var string
     */
    public $transfer_user_name;
    /**
     * 支付宝H5支付 回调地址
     * @var string
     */
    public $return_url;

    /**
     * @return string
     */
    public function getName()
    {
        return 'pay';
    }

    /**
     * 操作地址
     * @return mixed
     */
    public function endpoint()
    {
        return 'payment/pay';
    }

    /**
     * 实际业务
     * @return mixed
     */
    public function service()
    {
        $payment = $this->getPayment();

        if ($payment->isTest && $payment->testPay > 0) {
            $this->total_fee = $payment->testPay;
        }
        $payConfigMap = [
            static::PAY_TYPE_WECHAT_MP => ['type' => 'form', 'params' => ['driver' => 'wechat', 'gateway' => 'mp']],
            static::PAY_TYPE_WECHAT_WAP => ['type' => 'api', 'params' => ['driver' => 'wechat', 'gateway' => 'wap']],
            static::PAY_TYPE_WECHAT_APP => ['type' => 'api', 'params' => ['driver' => 'wechat', 'gateway' => 'app']],
            static::PAY_TYPE_WECHAT_SCAN => ['type' => 'api', 'params' => ['driver' => 'wechat', 'gateway' => 'scan', 'product_id' => $this->product_id]],
            static::PAY_TYPE_WECHAT_MINI_APP => ['type' => 'api', 'params' => ['driver' => 'wechat', 'gateway' => 'miniapp', 'mini_app_code' => $this->mini_app_code]],
            static::PAY_TYPE_WECHAT_TRANSFER => ['type' => 'api', 'params' => ['driver' => 'wechat', 'gateway' => 'transfer', 'transfer_openid' => $this->transfer_openid, 'transfer_type' => $this->transfer_type, 'transfer_check_name' => $this->transfer_check_name, 'transfer_user_name' => $this->transfer_user_name]],

            static::PAY_TYPE_ALIPAY_WEB => ['type' => 'form', 'params' => ['driver' => 'alipay', 'gateway' => 'web']],
            static::PAY_TYPE_ALIPAY_WAP => ['type' => 'form', 'params' => ['driver' => 'alipay', 'gateway' => 'wap', 'return_url' => $this->return_url]],
            static::PAY_TYPE_ALIPAY_APP => ['type' => 'api', 'params' => ['driver' => 'alipay', 'gateway' => 'app']],
            static::PAY_TYPE_ALIPAY_SCAN => ['type' => 'api', 'params' => ['driver' => 'alipay', 'gateway' => 'scan', 'product_id' => $this->product_id]],

            static::PAY_TYPE_ALLIN_PAY_H5 => ['type' => 'form', 'params' => ['driver' => 'allinPay', 'gateway' => 'wap', 'allin_pay_user_id' => $this->allin_pay_user_id, 'return_url' => $this->return_url]],
        ];
        if (!isset($payConfigMap[$this->pay_type])) {
            throw new InvalidConfigException('未知的 pay_type');
        }
        $payConfig = $payConfigMap[$this->pay_type];
        if ($payConfig['type'] == 'api') {
            $result = $this->payApi($payConfig['params']);
        } elseif ($payConfig['type'] == 'form') {
            $result = $this->generateForm($payConfig['params']);
        } else {
            throw new InvalidConfigException('未知的 $payConfig[\'type\']');
        }

        return $result;
    }

    /**
     * 发起下单
     * @param $postData
     * @return mixed
     * @throws Exception
     */
    protected function payApi($postData)
    {
        $postData = $this->getFinalPostData($postData);
        return $this->api($this->getEndpoint(), $postData);
    }

    /**
     * 构造成form
     * @param $postData
     * @return string
     */
    protected function generateForm($postData)
    {
        $html[] = Html::beginTag('form', [
            'id' => 'bd-pay-submit',
            'action' => $this->getEndpoint(),
            'method' => 'POST',
        ]);
        $postData = $this->getFinalPostData($postData);
        foreach ($postData as $name => $value) {
            $html[] = Html::hiddenInput($name, $value);
        }
        $html[] = Html::endTag('form');
        $html[] = Html::script("document.forms['bd-pay-submit'].submit();");
        return implode('', $html);
    }

    /**
     * 获取最终提交的数据
     * @param $postData
     * @return array
     */
    protected function getFinalPostData($postData)
    {
        $postData = array_merge([
            'out_trade_no' => $this->out_trade_no,
            'body' => $this->body,
            'spbill_create_ip' => $this->getUserIp(),
            'total_fee' => $this->total_fee,
            'attach' => $this->attach,
        ], $postData);

        return parent::getFinalPostData($postData);
    }

    /**
     * 获取用户ip
     * @return mixed|null|string
     */
    protected function getUserIp()
    {
        if ($this->spbill_create_ip) {
            return $this->spbill_create_ip;
        }
        if ($this->spbill_create_ip === false) {
            return '';
        }
        return Yii::$app->request->userIP;
    }
}
