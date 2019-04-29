<?php

namespace App\Exceptions;

use Exception;

class RedditException extends Exception
{
    /**
     * Construtor da exception.
     *
     * @param string $message mesagem da exception
     * @param int    $code    código da exception (default = 0)
     *
     * @return Exception
     */
    public function __construct($message, $code = 0, Exception $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }
}
