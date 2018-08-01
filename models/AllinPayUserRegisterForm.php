<?php

namespace kriss\bd\payment\models;

class AllinPayUserRegisterForm extends AbstractForm
{
    /**
     * @var string
     */
    public $user_identity;

    /**
     * @return string
     */
    protected function getName()
    {
        return 'allinpay user register';
    }

    /**
     * 操作地址
     * @return mixed
     */
    protected function endpoint()
    {
        return 'allin-pay/register-user';
    }

    /**
     * 实际业务
     * @return mixed
     */
    protected function service()
    {
        return $this->api($this->getEndpoint(), $this->getFinalPostData([
            'user_identity' => $this->user_identity,
        ]));
    }
}
