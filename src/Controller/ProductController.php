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
use Phuria\ZeroAuthDemo\Repository;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

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
        $app->getWrappedApp()->get('/product/', [$this, 'listAction']);
        $app->getWrappedApp()->get('/product/{id}', [$this, 'getAction']);
    }

    /**
     * @param ServerRequestInterface $request
     * @param ResponseInterface      $response
     */
    public function listAction(ServerRequestInterface $request, ResponseInterface $response)
    {
        $request->getQueryParams();

        $response = [];

        $cursor = [
            'before' => '',
            'after'  => ''
        ];
    }

    /**
     * @param RequestInterface  $request
     * @param ResponseInterface $response
     */
    public function getAction(RequestInterface $request, ResponseInterface $response)
    {

    }
}