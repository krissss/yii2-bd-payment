<?php

namespace kriss\bd\payment\exceptions;

use yii\base\Exception;

class ApiResponseErrorException extends Exception
{
    /**
     * @var mixed
     */
    public $raw;

    /**
     * ApiResponseErrorException constructor.
     * @param null $message
     * @param $raw
     * @param int $code
     * @param \Exception|null $previous
     */
    public function __construct($message = null, $raw, $code = 400, \Exception $previous = null)
    {
        parent::__construct($message, intval($code), $previous);

        $this->raw = $raw;
    }
}
