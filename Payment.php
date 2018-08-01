<?php

namespace kriss\bd\payment;

use kriss\bd\payment\models\AbstractForm;
use yii\base\BaseObject;
use yii\base\InvalidConfigException;

class Payment extends BaseObject
{
    // component 配置的名字必须是 self::COMPONENT_NAME
    const COMPONENT_NAME = 'bd-payment';

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
     * 基础支付地址
     * @var string
     */
    public $baseUrl = 'http://pay.bidanet.com';
    /**
     * 版本
     * @var string
     */
    public $version = '2.0';

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
     * 执行对应的操作
     * @param AbstractForm $model
     * @return mixed
     * @throws InvalidConfigException
     */
    public function invoke(AbstractForm $model)
    {
        if (!$model instanceof AbstractForm) {
            throw new InvalidConfigException('$model 必须是 AbstractForm 的实例');
        }
        $model->setPayment($this);
        return $model->doService();
    }
}
