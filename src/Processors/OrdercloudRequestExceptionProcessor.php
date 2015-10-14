<?php namespace Ordercloud\Monolog\Processors;

use Ordercloud\Requests\Exceptions\OrdercloudRequestException;

class OrdercloudRequestExceptionProcessor extends AbstractExceptionProcessor
{
    protected function handleOrdercloudRequestException(OrdercloudRequestException $exception, array $context)
    {
        $context['request'] = $exception->getHttpException()->getRequest()->getRawRequest();
        $context['response'] = $exception->getHttpException()->getResponse()->getRawResponse();

        return $context;
    }
}
