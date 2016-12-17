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

use Doctrine\DBAL\Connection;
use Doctrine\DBAL\DriverManager;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Tools\Setup;
use Interop\Container\ContainerInterface;
use Phuria\ZeroAuthDemo\Controller\ProductController;
use Phuria\ZeroAuthDemo\Repository;
use Slim\App as SlimApp;

/**
 * @author Beniamin Jonatan Šimko <spam@simko.it>
 */
class App
{
    const PARAM_DB_HOST = 'db.host';
    const PARAM_DB_USER = 'db.user';
    const PARAM_DB_DRIVER = 'db.driver';
    const PARAM_DB_PASSWORD = 'db.password';
    const PARAM_DB_DATABASE = 'db.database';
    const PARAM_APP_DEBUG = 'app.debug';

    /**
     * @var SlimApp
     */
    private $wrappedApp;



    private function loadControllers()
    {
        new ProductController($this);
    }

    private function loadServices()
    {
        $container = $this->getContainer();

        $container[Connection::class] = function (ContainerInterface $container) {
            return DriverManager::getConnection([
                'dbname'   => $container[static::PARAM_DB_DATABASE],
                'user'     => $container[static::PARAM_DB_USER],
                'password' => $container[static::PARAM_DB_PASSWORD],
                'host'     => $container[static::PARAM_DB_HOST],
                'driver'   => $container[static::PARAM_DB_DRIVER]
            ]);
        };

        $container[EntityManagerInterface::class] = function (ContainerInterface $container) {
            $config = Setup::createAnnotationMetadataConfiguration([
                __DIR__ . '/Entity'
            ], $container[static::PARAM_APP_DEBUG], null, null, false);

            return EntityManager::create($container[Connection::class], $config);
        };

        $container['pdo'] = function (ContainerInterface $container) {
            $dsn = "mysql:host={$container[static::PARAM_DB_HOST]};dbname={$container[static::PARAM_DB_DATABASE]}";

            $pdo = new \PDO($dsn, $container[static::PARAM_DB_USER], $container[static::PARAM_DB_PASSWORD]);
            $pdo->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);

            return $pdo;
        };

        $container[Repository\ProductInterface::class] = function (ContainerInterface $container) {
            return new Repository\Product($container['pdo']);
        };

        $container[Repository\UserInterface::class] = function (ContainerInterface $container) {
            return new Repository\User($container['pdo']);
        };
    }

    /**
     * @param array $parameters
     * @param bool  $debug
     */
    public function __construct(array $parameters, $debug = false)
    {
        $this->wrappedApp = new SlimApp([
            'settings' => ['displayErrorDetails' => $debug]
        ]);
        $container = $this->getContainer();

        foreach ($parameters as $parameter => $value) {
            $container[$parameter] = $value;
        }

        $container[static::PARAM_APP_DEBUG] = $debug;

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