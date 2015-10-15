<?php namespace Ordercloud\Monolog\Formatters;

use Monolog\Formatter\NormalizerFormatter;

class LogstashFormatter extends NormalizerFormatter
{
    /**
     * @var string
     */
    private $applicationName;

    public function __construct($applicationName)
    {
        parent::__construct('Y-m-d\TH:i:s.uP');

        $this->applicationName = $applicationName;
    }

    public function format(array $record)
    {
        $record = parent::format($record);

        $log['timestamp'] = $record['datetime'];
        $log['message'] = $record['message'];
        $log['app_name'] = $this->applicationName;
        $log['environment'] = $record['channel'];
        $log['host_container_id'] = gethostname();
        $log['level'] = [
            'code' => $record['level'],
            'name' => $record['level_name'],
        ];
        $log = $this->addRequest($log);
        $log = $this->addException($record, $log);
        $log = $this->addContext($record, $log);

        return $this->toJson($log) . "\n";
    }

    /**
     * @param array $log
     *
     * @return array
     */
    protected function addRequest(array $log)
    {
        if (isset($_SERVER['HTTP_HOST'])) {
            $log['request']['host'] = $_SERVER['HTTP_HOST'];
        }
        if (isset($_SERVER['REQUEST_URI'])) {
            $log['request']['uri'] = $_SERVER['REQUEST_URI'];
        }
        if (isset($_SERVER['REQUEST_METHOD'])) {
            $log['request']['method'] = $_SERVER['REQUEST_METHOD'];
        }
        if (isset($_POST) && ! empty($_POST)) {
            $log['request']['body'] = $_POST;

            return $log;
        }

        return $log;
    }

    /**
     * @param array $record
     * @param array $log
     *
     * @return array
     */
    protected function addException(array $record, array $log)
    {
        if (isset($record['context']['exception'])) {
            $log['exception'] = $record['context']['exception'];
        }

        return $log;
    }

    /**
     * @param array $record
     * @param array $log
     *
     * @return mixed
     */
    protected function addContext(array $record, array $log)
    {
        unset($record['context']['exception']);

        $log['context'] = $record['context'];

        return $log;
    }
}
