<?php namespace Ordercloud\Monolog\Processors;

use Illuminate\Session\Store;
use Monolog\Logger;

class IlluminateSessionProcessor
{
    /**
     * @var Store
     */
    private $session;
    /**
     * @var int
     */
    private $logLevel;

    /**
     * @param Store $session
     * @param int   $logLevel
     */
    public function __construct(Store $session, $logLevel = null)
    {
        $this->session = $session;
        $this->logLevel = $logLevel ?: Logger::DEBUG;
    }

    function __invoke($record)
    {
        if ($record['level'] >= $this->logLevel) {
            $record['context']['session'] = $this->session->all();
        }

        return $record;
    }

}
