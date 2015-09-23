<?php namespace Ordercloud\Monolog\Processors;

use Exception;
use ReflectionClass;

abstract class AbstractExceptionProcessor
{
    /**
     * @param $record
     *
     * @return array
     */
    public function __invoke($record)
    {
        $context = $record['context'];

        if (isset($context['exception']) && $this->handlesException($context['exception'])) {
            $record['message'] = (string) $context['exception'];
            $record['context'] = $this->processException($context['exception']);
        }

        return $record;
    }

    /**
     * @param Exception $exception
     *
     * @return bool
     */
    private function handlesException(Exception $exception)
    {
        return method_exists($this, $this->getExceptionHandlerMethod($exception));
    }

    /**
     * @param Exception $exception
     *
     * @return array
     */
    private function processException(Exception $exception)
    {
        $context = [
            'exception' => get_class($exception),
        ];

        return $this->addStackContext($exception, $context);
    }

    /**
     * Add the context of each exception in the stack.
     *
     * @param Exception $exceptionRoot
     * @param array     $context
     *
     * @return array
     */
    private function addStackContext(Exception $exceptionRoot, array $context)
    {
        $exception = $exceptionRoot;

        do {
            $context = $this->processExceptionContext($exception, $context);
        }
        while ($exception = $exception->getPrevious());

        return $context;
    }

    /**
     * @param Exception $exception
     * @param array     $context
     *
     * @return array
     */
    private function processExceptionContext(Exception $exception, array $context)
    {
        if ( ! $this->handlesException($exception)) {
            return $context;
        }

        $exceptionHandlerMethod = $this->getExceptionHandlerMethod($exception);

        return $this->$exceptionHandlerMethod($exception, $context);
    }

    /**
     * @param Exception $exception
     *
     * @return string
     */
    private function getExceptionHandlerMethod(Exception $exception)
    {
        $exceptionClassName = (new ReflectionClass($exception))->getShortName();

        return "handle{$exceptionClassName}";
    }
}
