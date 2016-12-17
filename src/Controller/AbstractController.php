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

use Interop\Container\ContainerInterface;
use Phuria\ZeroAuthDemo\App;
use Phuria\ZeroAuthDemo\Repository;

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
     * @return Repository\ProductInterface
     */
    public function getProductRepository()
    {
        return $this->getContainer()[Repository\ProductInterface::class];
    }

    /**
     * @return Repository\UserInterface
     */
    public function getUserRepository()
    {
        return $this->getContainer()[Repository\UserInterface::class];
    }
}