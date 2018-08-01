<?php

namespace kriss\bd\payment\models;

class CloseForm extends AbstractForm
{
    /**
     * 订单号
     * @var string
     */
    public $out_trade_no;

    /**
     * @return string
     */
    protected function getName()
    {
        return 'close';
    }

    /**
     * 操作地址
     * @return mixed
     */
    protected function endpoint()
    {
        return 'payment/close';
    }

    /**
     * 实际业务
     * @return mixed
     */
    protected function service()
    {
        return $this->api($this->getEndpoint(), $this->getFinalPostData([
            'out_trade_no' => $this->out_trade_no,
        ]));
    }
}
