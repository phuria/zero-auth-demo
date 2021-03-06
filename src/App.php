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
use Doctrine\DBAL\Types\Type;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Tools\Setup;
use GuzzleHttp\Client;
use GuzzleHttp\ClientInterface;
use Interop\Container\ContainerInterface;
use Phuria\ZeroAuth\Crypto\CryptoInterface;
use Phuria\ZeroAuth\Crypto\OpenSLLCrypto;
use Phuria\ZeroAuth\HashGenerator\Sha1Generator;
use Phuria\ZeroAuth\Middleware\SessionInterface;
use Phuria\ZeroAuth\Middleware\UserProviderInterface;
use Phuria\ZeroAuth\Protocol\ProtocolFacade;
use Phuria\ZeroAuth\Protocol\ProtocolHelper;
use Phuria\ZeroAuth\RandomGenerator\RandomBytesGenerator;
use Phuria\ZeroAuthDemo\Controller\CartController;
use Phuria\ZeroAuthDemo\Controller\EncryptedController;
use Phuria\ZeroAuthDemo\Controller\ProductController;
use Phuria\ZeroAuthDemo\Controller\SessionController;
use Phuria\ZeroAuthDemo\Controller\UserController;
use Phuria\ZeroAuthDemo\DoctrineType\BigIntegerType;
use Phuria\ZeroAuthDemo\Entity\Session;
use Phuria\ZeroAuthDemo\Middleware\AuthHandler;
use Phuria\ZeroAuthDemo\Middleware\ExceptionHandler;
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
    const PARAM_APP_HOST = 'app.host';
    const PARAM_APP_PORT = 'app.port';

    /**
     * @var SlimApp
     */
    private $wrappedApp;

    /**
     * Application controllers
     */
    private function loadControllers()
    {
        new ProductController($this);
        new UserController($this);
        new SessionController($this);
        new EncryptedController($this);
        new CartController($this);
    }

    /**
     * Application services
     */
    private function loadServices()
    {
        $container = $this->getContainer();

        $container[Connection::class] = function (ContainerInterface $container) {
            Type::addType('BigInteger', BigIntegerType::class);

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
                __DIR__ . '/Entity',
                __DIR__ . '/Embeddable'
            ], $container[static::PARAM_APP_DEBUG], null, null, false);

            return EntityManager::create($container[Connection::class], $config);
        };

        $container[ClientInterface::class] = function (ContainerInterface $container) {
            return new Client([
                'base_uri' => "http://{$container[static::PARAM_APP_HOST]}:{$container[static::PARAM_APP_PORT]}",
                'timeout'  => 1.0
            ]);
        };

        $container[ProtocolHelper::class] = function () {
            $protocolFacade = new ProtocolFacade(
                new Sha1Generator(),
                new RandomBytesGenerator()
            );

            return ProtocolHelper::create1024($protocolFacade);
        };

        $container[CryptoInterface::class] = function () {
            return new OpenSLLCrypto(new RandomBytesGenerator());
        };
    }

    /**
     * Application middleware
     */
    private function loadMiddleware()
    {
        $this->wrappedApp->add(new ExceptionHandler());
        $this->wrappedApp->add(new AuthHandler($this->getContainer()[EntityManagerInterface::class]));
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
        $this->loadMiddleware();
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