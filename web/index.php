<?php
/**
 * This file is part of phuria/zero-auth-demo package.
 *
 * Copyright (c) 2016 Beniamin Jonatan Šimko
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

require '../vendor/autoload.php';

$app = new \Phuria\ZeroAuthDemo\App(parse_ini_file('../config.ini'));
$app->getWrappedApp()->run();