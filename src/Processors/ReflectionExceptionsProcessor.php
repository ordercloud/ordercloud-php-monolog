<?php namespace Ordercloud\Monolog\Processors;

use Ordercloud\Requests\Exceptions\ResponseParseException;
use Ordercloud\Support\Reflection\Exceptions\ArgumentNotProvidedException;
use Ordercloud\Support\Reflection\Exceptions\EntityParseException;
use Ordercloud\Support\Reflection\Exceptions\NullRequiredArgumentException;

class ReflectionExceptionsProcessor extends AbstractExceptionProcessor
{
    protected function handleResponseParseException(ResponseParseException $exception, array $context)
    {
        $context['request'] = $exception->getRequest()->getRawRequest();
        $context['response'] = $exception->getResponse()->getRawResponse();

        return $context;
    }

    protected function handleEntityParseException(EntityParseException $exception, array $context)
    {
        $context['entity_parse'] = [
            'entity'    => $exception->getClassName(),
            'arguments' => $exception->getArguments(),
        ];

        return $context;
    }

    protected function handleArgumentNotProvidedException(ArgumentNotProvidedException $exception, array $context)
    {
        $context['argument_not_provided'] = [
            'entity'          => $exception->getClassName(),
            'parameter'       => $exception->getParameterName(),
            'parameter_alias' => $exception->getParameterAlias(),
            'arguments'       => $exception->getArguments(),
        ];

        return $context;
    }

    protected function handleNullRequiredArgumentException(NullRequiredArgumentException $exception, array $context)
    {
        $context['null_required_argument'] = [
            'entity'    => $exception->getClassName(),
            'parameter' => $exception->getParameterName(),
        ];

        return $context;
    }
}
