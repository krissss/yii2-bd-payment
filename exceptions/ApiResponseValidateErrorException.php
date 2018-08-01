<?php

namespace kriss\bd\payment\exceptions;

use yii\base\Exception;

class ApiResponseValidateErrorException extends Exception
{
    /**
     * @var string|integer
     */
    public $status;
    /**
     * @var mixed
     */
    public $raw;

    /**
     * ApiResponseErrorException constructor.
     * @param $msg
     * @param $status
     * @param $raw
     * @param int $code
     * @param \Exception|null $previous
     */
    public function __construct($msg, $status, $raw, $code = 422, \Exception $previous = null)
    {
        parent::__construct($msg, intval($code), $previous);

        $this->status = $status;
        $this->raw = $raw;
    }
}
