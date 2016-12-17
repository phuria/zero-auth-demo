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
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Phuria\ZeroAuthDemo\Model;

/**
 * @author Beniamin Jonatan Šimko <spam@simko.it>
 */
class UserController extends AbstractController
{
    /**
     * @inheritdoc
     */
    public function loadRouting(App $app)
    {
        $app->getWrappedApp()->get('/user/', [$this, 'listAction']);
        $app->getWrappedApp()->post('/user/', [$this, 'postAction']);
    }

    /**
     * @param ServerRequestInterface $request
     * @param ResponseInterface      $response
     */
    public function listAction(ServerRequestInterface $request, ResponseInterface $response)
    {
        $result = $this->getUserRepository()->findAll();
    }

    /**
     * @param ServerRequestInterface $request
     * @param ResponseInterface      $response
     */
    public function postAction(ServerRequestInterface $request, ResponseInterface $response)
    {
        $query = $request->getQueryParams();

        $user = new Model\User();
        $user->id = (string) $query['id'];
        $user->verifier = (string) $query['verifier'];

        $this->getUserRepository()->save($user);


    }
}