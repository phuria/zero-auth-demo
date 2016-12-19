<?php

/**
 * This file is part of phuria/zero-auth-demo package.
 *
 * Copyright (c) 2016 Beniamin Jonatan Šimko
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Phuria\ZeroAuthDemo\Controller;

use Doctrine\ORM\EntityManagerInterface;
use Exceptions\Http\Client\ForbiddenException;
use Interop\Container\ContainerInterface;
use Phuria\ZeroAuth\Protocol\ProtocolHelper;
use Phuria\ZeroAuthDemo\App;
use Phuria\ZeroAuthDemo\Entity\Session;
use Phuria\ZeroAuthDemo\Entity\User;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

/**
 * @author Beniamin Jonatan Šimko <spam@simko.it>
 */
abstract class AbstractController
{
    /**
     * @var ContainerInterface
     */
    private $container;

    /**
     * @param App $app
     */
    public function __construct(App $app)
    {
        $this->container = $app->getContainer();
        $this->loadRouting($app);
    }

    /**
     * @param App $app
     */
    abstract public function loadRouting(App $app);

    /**
     * @return ContainerInterface
     */
    public function getContainer()
    {
        return $this->container;
    }

    /**
     * @return EntityManagerInterface
     */
    public function getEntityManager()
    {
        return $this->getContainer()[EntityManagerInterface::class];
    }

    /**
     * @return ProtocolHelper
     */
    public function getProtocolHelper()
    {
        return $this->getContainer()[ProtocolHelper::class];
    }

    /**
     * @param ResponseInterface $response
     * @param mixed             $data
     *
     * @return ResponseInterface
     */
    public function jsonResponse(ResponseInterface $response, $data)
    {
        $response->getBody()->write(json_encode($data));

        return $response;
    }

    /**
     * @param ServerRequestInterface $request
     * @param bool                   $required
     *
     * @return Session|null
     * @throws ForbiddenException
     */
    public function getSession(ServerRequestInterface $request, $required = false)
    {
        $session = $request->getAttribute(Session::class);

        if ($session instanceof Session) {
            return $session;
        }

        if ($required) {
            throw new ForbiddenException();
        }

        return null;
    }
}