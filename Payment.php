<?php

namespace kriss\bd\payment;

use Yii;
use yii\base\Exception;
use yii\base\InvalidConfigException;
use yii\base\Model;
use yii\helpers\Html;
use yii\helpers\Json;
use yii\httpclient\Client;

class Payment extends Model
{
    // component 配置的名字必须是 self::COMPONENT_NAME
    const COMPONENT_NAME = 'bd-payment';

    // 微信支付
    const PAY_TYPE_WECHAT_MP = 10;
    const PAY_TYPE_WECHAT_WAP = 11;
    const PAY_TYPE_WECHAT_APP = 12;
    const PAY_TYPE_WECHAT_SCAN = 13;
    // 支付宝支付
    const PAY_TYPE_ALIPAY_WEB = 20;
    const PAY_TYPE_ALIPAY_WAP = 21;
    const PAY_TYPE_ALIPAY_APP = 22;
    // 通联支付
    const PAY_TYPE_ALLIN_PAY_H5 = 100;

    // component 配置信息
    /**
     * @var string
     */
    public $ak;
    /**
     * @var string
     */
    public $sk;
    /**
     * 是否是测试
     * @var bool
     */
    public $isTest = true;
    /**
     * 测试时支付金额，默认1分，0表示用原价
     * @var int
     */
    public $testPay = 1;
    /**
     * @var string
     */
    public $logCategory = 'app';
    /**
     * 支付地址
     * @var string
     */
    public $payUrl = 'http://pay.bidanet.com/payment/pay';

    // model 可传递的参数信息
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
     * 支付宝H5支付 回调地址
     * @var string
     */
    public $return_url;

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['pay_type', 'out_trade_no', 'body', 'total_fee', 'attach', 'mini_app_code', 'product_id', 'allin_pay_user_id', 'return_url'], 'safe']
        ];
    }

    /**
     * @inheritdoc
     */
    public function init()
    {
        if (!$this->ak) {
            throw new InvalidConfigException('必须配置 ak');
        }
        if (!$this->sk) {
            throw new InvalidConfigException('必须配置 sk');
        }
        $this->isTest = (int)(!!$this->isTest);
        parent::init();
    }

    /**
     * 发起支付
     * @return array|string
     * @throws InvalidConfigException
     */
    public function pay()
    {
        Yii::info('pay start:' . Json::encode($this), $this->logCategory);

        if ($this->isTest && $this->testPay > 0) {
            $this->total_fee = $this->testPay;
        }
        switch ($this->pay_type) {
            case static::PAY_TYPE_WECHAT_MP:
                $result = $this->wechatMp();
                break;
            case static::PAY_TYPE_WECHAT_WAP:
                $result = $this->wechatWap();
                break;
            case static::PAY_TYPE_WECHAT_APP:
                $result = $this->wechatApp();
                break;
            case static::PAY_TYPE_WECHAT_SCAN:
                $result = $this->wechatScan();
                break;
            case static::PAY_TYPE_ALIPAY_WEB:
                $result = $this->alipayWeb();
                break;
            case static::PAY_TYPE_ALIPAY_WAP:
                $result = $this->alipayWap();
                break;
            case static::PAY_TYPE_ALIPAY_APP:
                $result = $this->alipayApp();
                break;
            case static::PAY_TYPE_ALLIN_PAY_H5:
                $result = $this->allinPayWap();
                break;
            default:
                throw new InvalidConfigException('未知的 pay_type');
        }
        Yii::info('pay start config:' . Json::encode($result), $this->logCategory);

        return $result;
    }

    /**
     * 公众号支付
     * @return string
     */
    protected function wechatMp()
    {
        return $this->generateForm([
            'ak' => $this->ak,
            'is_test' => $this->isTest,
            'driver' => 'wechat',
            'gateway' => 'mp',
            'out_trade_no' => $this->out_trade_no,
            'body' => $this->body,
            'spbill_create_ip' => $this->getUserIp(),
            'total_fee' => $this->total_fee,
            'attach' => $this->attach,
        ]);
    }

    /**
     * 小程序支付
     * @return mixed
     */
    protected function wechatMiniApp()
    {
        return $this->payApi([
            'ak' => $this->ak,
            'is_test' => $this->isTest,
            'driver' => 'wechat',
            'gateway' => 'miniapp',
            'out_trade_no' => $this->out_trade_no,
            'body' => $this->body,
            'spbill_create_ip' => $this->getUserIp(),
            'total_fee' => $this->total_fee,
            'attach' => $this->attach,
            'mini_app_code' => $this->mini_app_code
        ]);
    }

    /**
     * 微信手机移动支付
     * @return mixed
     */
    protected function wechatWap()
    {
        return $this->payApi([
            'ak' => $this->ak,
            'is_test' => $this->isTest,
            'driver' => 'wechat',
            'gateway' => 'wap',
            'out_trade_no' => $this->out_trade_no,
            'body' => $this->body,
            'spbill_create_ip' => $this->getUserIp(),
            'total_fee' => $this->total_fee,
            'attach' => $this->attach,
        ]);
    }

    /**
     * 微信APP支付
     * @return mixed
     */
    protected function wechatApp()
    {
        return $this->payApi([
            'ak' => $this->ak,
            'is_test' => $this->isTest,
            'driver' => 'wechat',
            'gateway' => 'app',
            'out_trade_no' => $this->out_trade_no,
            'body' => $this->body,
            'spbill_create_ip' => $this->getUserIp(),
            'total_fee' => $this->total_fee,
            'attach' => $this->attach,
        ]);
    }

    /**
     * 微信扫码支付
     * @return mixed
     */
    protected function wechatScan()
    {
        return $this->payApi([
            'ak' => $this->ak,
            'is_test' => $this->isTest,
            'driver' => 'wechat',
            'gateway' => 'scan',
            'out_trade_no' => $this->out_trade_no,
            'body' => $this->body,
            'spbill_create_ip' => $this->getUserIp(),
            'total_fee' => $this->total_fee,
            'attach' => $this->attach,
            'product_id' => $this->product_id
        ]);
    }

    /**
     * 支付宝电脑支付
     * @return string
     */
    protected function alipayWeb()
    {
        return $this->generateForm([
            'ak' => $this->ak,
            'is_test' => $this->isTest,
            'driver' => 'alipay',
            'gateway' => 'web',
            'out_trade_no' => $this->out_trade_no,
            'body' => $this->body,
            'spbill_create_ip' => $this->getUserIp(),
            'total_fee' => $this->total_fee,
            'attach' => $this->attach,
        ]);
    }

    /**
     * 支付宝手机移动支付
     * @return string
     */
    protected function alipayWap()
    {
        return $this->generateForm([
            'ak' => $this->ak,
            'is_test' => $this->isTest,
            'driver' => 'alipay',
            'gateway' => 'wap',
            'out_trade_no' => $this->out_trade_no,
            'body' => $this->body,
            'spbill_create_ip' => $this->getUserIp(),
            'total_fee' => $this->total_fee,
            'attach' => $this->attach,
            'return_url' => $this->return_url
        ]);
    }

    /**
     * 支付宝 APP 支付
     * @return mixed
     */
    protected function alipayApp()
    {
        return $this->payApi([
            'ak' => $this->ak,
            'is_test' => $this->isTest,
            'driver' => 'alipay',
            'gateway' => 'app',
            'out_trade_no' => $this->out_trade_no,
            'body' => $this->body,
            'spbill_create_ip' => $this->getUserIp(),
            'total_fee' => $this->total_fee,
            'attach' => $this->attach,
        ]);
    }

    /**
     * 支付宝扫码支付
     * @return mixed
     */
    protected function alipayScan()
    {
        return $this->payApi([
            'ak' => $this->ak,
            'is_test' => $this->isTest,
            'driver' => 'alipay',
            'gateway' => 'scan',
            'out_trade_no' => $this->out_trade_no,
            'body' => $this->body,
            'spbill_create_ip' => $this->getUserIp(),
            'total_fee' => $this->total_fee,
            'attach' => $this->attach,
            'product_id' => $this->product_id
        ]);
    }

    /**
     * 支付宝扫码支付
     * @return mixed
     */
    protected function allinPayWap()
    {
        return $this->generateForm([
            'ak' => $this->ak,
            'is_test' => $this->isTest,
            'driver' => 'allinPay',
            'gateway' => 'wap',
            'out_trade_no' => $this->out_trade_no,
            'body' => $this->body,
            'spbill_create_ip' => $this->getUserIp(),
            'total_fee' => $this->total_fee,
            'attach' => $this->attach,
            'allin_pay_user_id' => $this->allin_pay_user_id,
            'return_url' => $this->return_url,
        ]);
    }

    /**
     * 发起下单
     * @param $postData
     * @return mixed
     * @throws Exception
     */
    protected function payApi($postData)
    {
        $client = new Client();
        $response = $client->createRequest()
            ->setMethod('post')
            ->setUrl($this->payUrl)
            ->setData($postData)
            ->send();
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
     * 构造成form
     * @param $postData
     * @return string
     */
    protected function generateForm($postData)
    {
        $html[] = Html::beginTag('form', [
            'id' => 'bd-pay-submit',
            'action' => $this->payUrl,
            'method' => 'POST'
        ]);
        foreach ($postData as $name => $value) {
            $html[] = Html::hiddenInput($name, $value);
        }
        $html[] = Html::endTag('form');
        $html[] = Html::script("document.forms['bd-pay-submit'].submit();");
        return implode('', $html);
    }

    /**
     * 获取用户ip
     * @return mixed|null|string
     */
    protected function getUserIp()
    {
        return Yii::$app->request->userIP;
    }
}