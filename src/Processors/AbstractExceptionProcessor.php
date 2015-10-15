<?php namespace Ordercloud\Monolog\Processors;

use Exception;
use ReflectionClass;
use ReflectionMethod;

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

        if (isset($context['exception'])) {
            $record['context'] = array_merge($context, $this->processException($context['exception']));
        }

        return $record;
    }

    /**
     * @param Exception $exception
     *
     * @return array
     */
    private function processException(Exception $exception)
    {
        $context = [
            'exception_class' => get_class($exception),
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
        foreach($this->getExceptionHandlerMethods($exception) as $method) {
            $context = $this->{$method->getName()}($exception, $context);
        }

        return $context;
    }

    /**
     * @param Exception $exception
     *
     * @return array|ReflectionMethod[]
     */
    private function getExceptionHandlerMethods(Exception $exception)
    {
        $class = new ReflectionClass($this);

        $handlerMethods = [];
        foreach ($class->getMethods(ReflectionMethod::IS_PROTECTED) as $method) {
            $param = current($method->getParameters());
            if ($param && $param->getClass() && $param->getClass()->isInstance($exception)) {
                $handlerMethods[] = $method;
            }
        }

        return $handlerMethods;
    }
}
