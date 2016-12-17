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

use Phuria\ZeroAuthDemo\App;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;

/**
 * @author Beniamin Jonatan Šimko <spam@simko.it>
 */
class ProductController extends AbstractController
{
    /**
     * @inheritdoc
     */
    public function loadRouting(App $app)
    {
        $app->getWrappedApp()->get('/product/', [$this, ['listAction']]);
    }

    /**
     * @param RequestInterface  $request
     * @param ResponseInterface $response
     */
    public function listAction(RequestInterface $request, ResponseInterface $response)
    {

    }
}