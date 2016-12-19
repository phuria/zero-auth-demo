<?php
/**
 * This file is part of phuria/zero-auth-demo package.
 *
 * Copyright (c) 2016 Beniamin Jonatan Šimko
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

use Phuria\ZeroAuthDemo\App;
use Phuria\ZeroAuthDemo\Command\AuthCommand;
use Phuria\ZeroAuthDemo\Command\RegisterCommand;
use Symfony\Component\Console\Application;

require 'vendor/autoload.php';

$app = new App(parse_ini_file('config.ini'), true);

$application = new Application();
$application->add(new RegisterCommand($app->getContainer()));
$application->add(new AuthCommand($app->getContainer()));
$application->run();