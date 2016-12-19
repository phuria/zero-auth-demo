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

use Doctrine\ORM\EntityManagerInterface;
use Exceptions\Http\Client\ForbiddenException;
use phpseclib\Math\BigInteger;
use Phuria\ZeroAuthDemo\Entity\Session;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

/**
 * @author Beniamin Jonatan Šimko <spam@simko.it>
 */
class AuthHandler
{
    /**
     * @var EntityManagerInterface
     */
    private $em;

    /**
     * @param EntityManagerInterface $em
     */
    public function __construct(EntityManagerInterface $em)
    {
        $this->em = $em;
    }

    /**
     * @param ServerRequestInterface $request
     * @param ResponseInterface      $response
     * @param callable               $next
     *
     * @return ResponseInterface
     */
    public function __invoke(ServerRequestInterface $request, ResponseInterface $response, callable $next)
    {
        $authLine = $request->getHeader('HTTP_AUTHORIZATION')[0];

        if (false === strpos($authLine, 'Basic ')) {
            return $next($request, $response);
        }

        list($sessionId, $clientProof) = explode(':', str_replace('Basic ', '', $authLine));

        /** @var Session $session */
        $session = $this->em->find(Session::class, $sessionId);

        if (null === $session) {
            throw new ForbiddenException();
        }

        if (false === $session->getClientProof()->equals(new BigInteger($clientProof, 16))) {
            throw new ForbiddenException();
        }

        return $next($request->withAttribute(Session::class, $session), $response);
    }
}