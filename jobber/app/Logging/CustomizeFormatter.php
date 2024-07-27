<?php

namespace App\Logging;

use Illuminate\Log\Logger;
use Monolog\Formatter\LineFormatter;

class CustomizeFormatter
{
    /**
     * Customize the given logger instance.
     */
    public function __invoke(Logger $logger): void
    {
        foreach ($logger->getHandlers() as $handler) {
            $formatter = new LineFormatter(
                "[%datetime%] %level_name% %message% %context% %extra%\n",
                'Y/m/d H:i:s',
                true,
                true
            );
//            $formatter = new LineFormatter(
//                "time=\"%datetime%\" level=%level_name% msg=\"%message%\" context=\"%context%\" %extra%\n",
//                'Y/m/d H:i:s',
//                true,
//                true
//            );
            $formatter->includeStacktraces();

            $handler->setFormatter($formatter);
        }
    }
}
