<?php

namespace CollinsHarper\Paysafe\Gateway\Exception;

class PaysafeException extends \Exception
{
    public $fieldErrors;
    public $links;
}
