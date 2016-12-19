<?php

/**
 * This file is part of phuria/zero-auth-demo package.
 *
 * Copyright (c) 2016 Beniamin Jonatan Šimko
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Phuria\ZeroAuthDemo\Command;

use GuzzleHttp\ClientInterface;
use Interop\Container\ContainerInterface;
use Phuria\ZeroAuth\Protocol\ProtocolHelper;
use Symfony\Component\Console\Command\Command;

/**
 * @author Beniamin Jonatan Šimko <spam@simko.it>
 */
class AbstractCommand extends Command
{
    /**
     * @var ContainerInterface
     */
    private $container;

    /**
     * @param ContainerInterface $container
     */
    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
        parent::__construct();
    }

    /**
     * @return ClientInterface
     */
    public function getClient()
    {
        return $this->container->get(ClientInterface::class);
    }

    /**
     * @return ProtocolHelper
     */
    public function getProtocolHelper()
    {
        return $this->container->get(ProtocolHelper::class);
    }
}