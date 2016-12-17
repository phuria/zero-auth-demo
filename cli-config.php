<?php
/**
 * This file is part of phuria/zero-auth-demo package.
 *
 * Copyright (c) 2016 Beniamin Jonatan Šimko
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Tools\Console\ConsoleRunner;
use Phuria\ZeroAuthDemo\App;

require './vendor/autoload.php';

$app = new App(parse_ini_file('config.ini'), true);

return ConsoleRunner::createHelperSet($app->getContainer()[EntityManagerInterface::class]);