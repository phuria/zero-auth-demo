<?php

/**
 * This file is part of phuria/zero-auth-demo package.
 *
 * Copyright (c) 2016 Beniamin Jonatan Šimko
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Phuria\ZeroAuthDemo\Middleware;

use Doctrine\DBAL\Exception\ConnectionException;
use Doctrine\DBAL\Exception\UniqueConstraintViolationException;
use Exceptions\Http\HttpException;
use Exceptions\Http\Server\InternalServerErrorException;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Zend\Diactoros\Stream;

/**
 * @author Beniamin Jonatan Šimko <spam@simko.it>
 */
class ExceptionHandler
{
    /**
     * @param ServerRequestInterface $request
     * @param ResponseInterface      $response
     * @param callable               $next
     *
     * @return ResponseInterface
     */
    public function __invoke(ServerRequestInterface $request, ResponseInterface $response, callable $next)
    {
        try {
            return $next($request, $response);
        } catch (\Exception $exception) {
            return $this->onException($response, $exception);
        }
    }

    /**
     * @param ResponseInterface $response
     * @param \Exception        $exception
     *
     * @return ResponseInterface
     * @throws \Exception
     */
    private function onException(ResponseInterface $response, \Exception $exception)
    {
        if ($exception instanceof ConnectionException) {
            return $this->onException($response, new InternalServerErrorException());
        }

        if ($exception instanceof HttpException) {
            return $this->createErrorResponse(
                $response,
                $exception->getCode(),
                $exception->getCode(),
                $exception->getMessage()
            );
        }

        if ($exception instanceof UniqueConstraintViolationException) {
            return $this->createErrorResponse(
                $response,
                500,
                $exception->getErrorCode(),
                'Unique constraint violation.'
            );
        }

        throw $exception;
    }

    /**
     * @param ResponseInterface $response
     * @param int               $httpCode
     * @param int               $appCode
     * @param string            $errorMessage
     *
     * @return ResponseInterface
     */
    private function createErrorResponse(
        ResponseInterface $response,
        $httpCode = 500,
        $appCode = 500,
        $errorMessage = 'Internal Server Error'
    ) {
        $stream = new Stream('php://memory', 'rw');
        $stream->write(json_encode(['error' => ['code' => $appCode, 'message' => $errorMessage]]));

        return $response->withBody($stream)->withStatus($httpCode);
    }
}