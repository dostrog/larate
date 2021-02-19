<?php
declare(strict_types=1);

namespace Dostrog\Larate\Exceptions;

use Illuminate\Log\Logger;

class InvalidArgumentException extends \InvalidArgumentException
{
    /**
     * Report the exception within Laravel Exception\Handler
     *
     * @return bool|void
     */
    public function report(Logger $logger)
    {
        $logger->error('logger : ' . $this->getMessage());
    }
}
