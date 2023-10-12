<?php

namespace LearnToWin\GeneralBundle\EventListener;

use Symfony\Component\EventDispatcher\Attribute\AsEventListener;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\ExceptionEvent;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;
use Symfony\Component\HttpKernel\KernelEvents;

#[AsEventListener(event: KernelEvents::EXCEPTION, priority: 10)]
class ExceptionListener
{
    public function __invoke(ExceptionEvent $event): void
    {
        // You get the exception object from the received event
        $exception = $event->getThrowable();

        $body = [
            'message' => $exception->getMessage(),
            'code' => $exception->getCode(),
        ];
        if ('prod' !== getenv('APP_ENV')) {
            $body['traces'] = $exception->getTrace();
            $body['file'] = $exception->getFile();
            $body['line'] = $exception->getLine();
        }

        $response = new JsonResponse($body);

        // HttpExceptionInterface is a special type of exception that
        // holds status code and header details
        if ($exception instanceof HttpExceptionInterface) {
            $response->setStatusCode($exception->getStatusCode());
            $response->headers->replace($exception->getHeaders());
            // Ensure we are always returning JSON
            $response->headers->set('Content-Type', 'application/json');
        } else {
            if (0 === $exception->getCode()) {
                $response->setStatusCode(Response::HTTP_INTERNAL_SERVER_ERROR);
            } else {
                $response->setStatusCode($exception->getCode());
            }
        }

        // sends the modified response object to the event
        $event->setResponse($response);
    }
}
