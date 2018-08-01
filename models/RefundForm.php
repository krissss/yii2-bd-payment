<?php

namespace kriss\bd\payment\models;

class RefundForm extends AbstractForm
{
    /**
     * @var string
     */
    public $out_trade_no;
    /**
     * @var string
     */
    public $out_refund_no;
    /**
     * @var integer
     */
    public $refund_fee;
    /**
     * @var string
     */
    public $refund_desc;

    /**
     * @var bool
     */
    public $isEnable = !YII_DEBUG;

    /**
     * @return string
     */
    protected function getName()
    {
        return 'refund';
    }

    /**
     * 操作地址
     * @return mixed
     */
    protected function endpoint()
    {
        return 'payment/refund';
    }

    /**
     * 实际业务
     * @return array
     */
    protected function service()
    {
        return $this->api($this->getEndpoint(), $this->getFinalPostData([
            'out_trade_no' => $this->out_trade_no,
            'out_refund_no' => $this->out_refund_no,
            'refund_fee' => $this->refund_fee,
            'refund_desc' => $this->refund_desc,
        ]));
    }
}
