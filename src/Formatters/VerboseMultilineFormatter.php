<?php namespace Ordercloud\Monolog\Formatters;

use Monolog\Formatter\LineFormatter;

/**
 * Intended for use with local development.
 */
class VerboseMultilineFormatter extends LineFormatter
{
    const VERBOSE_MULTILINE_FORMAT = "[%datetime%] %channel%.%level_name%: %message%\n%context% %extra%\n=======================/\n\n";

    public function __construct()
    {
        parent::__construct(static::VERBOSE_MULTILINE_FORMAT, null, true, true);
    }

    public function format(array $record)
    {
        if (isset($record['context']['exception'])) {
            $record['message'] = (string) $record['context']['exception'];
        }

        return parent::format($record);
    }

    protected function convertToString($data)
    {
        if (is_array($data)) {
            $jsonData = json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
            $newlinesReplaced = str_replace('\r\n', "\n", $jsonData);
            return stripslashes($newlinesReplaced);
        }

        return parent::convertToString($data);
    }
}
