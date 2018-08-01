<?php

namespace kriss\bd\payment\models;

use kriss\bd\payment\exceptions\ApiResponseErrorException;
use kriss\bd\payment\exceptions\ApiResponseValidateErrorException;
use kriss\bd\payment\Payment;
use kriss\bd\payment\Support;
use yii\base\Model;
use yii\httpclient\Client;
use yii\httpclient\Exception;
use yii\httpclient\Response;
use yii\web\ForbiddenHttpException;

abstract class AbstractForm extends Model
{
    /**
     * 其他参数，用于在未定义 public 属性时，需要传递的参数
     * @var array
     */
    public $otherParams;
    /**
     * 是否允许调用，在涉及到资金操作时，为了不影响正式的数据，比较有用
     * @var bool
     */
    public $isEnable = true;
    /**
     * @var Payment
     */
    private $_payment;

    public function init()
    {
        parent::init();
        if (!$this->isEnable) {
            throw new ForbiddenHttpException('当前接口不允许调用');
        }
    }

    /**
     * @param Payment $payment
     */
    public function setPayment(Payment $payment)
    {
        $this->_payment = $payment;
    }

    /**
     * @return Payment
     */
    public function getPayment()
    {
        return $this->_payment;
    }

    /**
     * 操作业务
     * @return mixed
     */
    public function doService()
    {
        Support::logger($this->getName() . ' start', $this->attributes);
        $result = $this->service();
        Support::logger($this->getName() . ' over', $result);
        return $result;
    }

    /**
     * 获取操作的接口地址
     * @return string
     */
    public function getEndpoint()
    {
        $payment = $this->getPayment();
        return rtrim($payment->baseUrl, '/') . '/' . ltrim($this->endpoint(), '/');
    }

    /**
     * @return string
     */
    abstract protected function getName();

    /**
     * 操作地址
     * @return mixed
     */
    abstract protected function endpoint();

    /**
     * 实际业务
     * @return mixed
     */
    abstract protected function service();

    /**
     * 获取最终提交的数据
     * @param $postData
     * @return array
     */
    protected function getFinalPostData($postData)
    {
        $payment = $this->getPayment();
        // 公共参数
        $postData['ak'] = $payment->ak;
        $postData['is_test'] = $payment->isTest;
        // 其他参数
        if ($this->otherParams && is_array($this->otherParams)) {
            $postData = array_merge($postData, $this->otherParams);
        }
        // 版本和签名
        if (version_compare($payment->version, '2.0', '>=')) {
            $postData['version'] = $payment->version;
            $postData['sign'] = Support::makeSign($payment->sk, $postData);
        }
        return $postData;
    }

    /**
     * api 接口
     * @param $endpoint
     * @param $postData
     * @return mixed
     * @throws Exception
     */
    protected function api($endpoint, $postData)
    {
        $client = new Client();
        /** @var Response $response */
        $response = $client->createRequest()
            ->setMethod('post')
            ->setUrl($endpoint)
            ->setData($postData)
            ->send();
        if ($response->isOk) {
            $responseData = $response->data;
            if ($responseData['status'] == 200) {
                return $responseData['data'];
            }
            Support::logger($this->getName() . ' response validate error', $responseData, 'warning');
            throw new ApiResponseValidateErrorException($responseData['msg'], $responseData['status'], $responseData);
        }
        Support::logger($this->getName() . ' response error', $response, 'error');
        throw new ApiResponseErrorException('请求失败：错误码' . $response->getStatusCode(), $response);
    }
}
