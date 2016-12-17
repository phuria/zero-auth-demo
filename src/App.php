<?php

/**
 * This file is part of phuria/zero-auth-demo package.
 *
 * Copyright (c) 2016 Beniamin Jonatan Šimko
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Phuria\ZeroAuthDemo;

use Interop\Container\ContainerInterface;
use Slim\App as SlimApp;

/**
 * @author Beniamin Jonatan Šimko <spam@simko.it>
 */
class App
{
    const PARAM_DB_HOST = 'db.host';
    const PARAM_DB_PORT = 'db.port';
    const PARAM_DB_USER = 'db.user';
    const PARAM_DB_PASSWORD = 'db.password';
    const PARAM_DB_DATABASE = 'db.database';

    /**
     * @var SlimApp
     */
    private $wrappedApp;

    /**
     * @return array
     */
    private function loadControllers()
    {
        new ProductController($this);
    }

    private function loadServices()
    {
        $container = $this->getContainer();
    }

    /**
     * @param array $parameters
     */
    public function __construct(array $parameters)
    {
        $this->wrappedApp = new SlimApp();
        $container = $this->getContainer();

        foreach ($parameters as $parameter => $value) {
            $container[$parameter] = $value;
        }

        $this->loadServices();
        $this->loadControllers();
    }

    /**
     * @return ContainerInterface
     */
    public function getContainer()
    {
        return $this->wrappedApp->getContainer();
    }

    /**
     * @return SlimApp
     */
    public function getWrappedApp()
    {
        return $this->wrappedApp;
    }
}