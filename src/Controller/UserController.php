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

use Exceptions\Http\Client\NotFoundException;
use Exceptions\Http\Client\UnprocessableEntityException;
use phpseclib\Math\BigInteger;
use Phuria\ZeroAuthDemo\App;
use Phuria\ZeroAuthDemo\Entity\User;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

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
        $app->getWrappedApp()->get('/user/{username}', [$this, 'getAction']);
        $app->getWrappedApp()->delete('/user/{username}', [$this, 'deleteAction']);
        $app->getWrappedApp()->patch('/user/{username}', [$this, 'patchAction']);
    }

    /**
     * @param ServerRequestInterface $request
     * @param ResponseInterface      $response
     *
     * @return ResponseInterface
     */
    public function listAction(ServerRequestInterface $request, ResponseInterface $response)
    {
        $em = $this->getEntityManager();

        $result = $em->getRepository(User::class)->findAll();
        $response->getBody()->write(json_encode($result));

        return $response;
    }

    /**
     * @param ServerRequestInterface $request
     * @param ResponseInterface      $response
     *
     * @return ResponseInterface
     */
    public function getAction(ServerRequestInterface $request, ResponseInterface $response)
    {
        $user = $this->findUser($request);

        return $this->jsonResponse($response, [
            'username' => $user->getUsername(),
            'verifier' => $user->getVerifier()->toHex()
        ]);
    }

    /**
     * @param ServerRequestInterface $request
     * @param ResponseInterface      $response
     *
     * @return ResponseInterface
     */
    public function postAction(ServerRequestInterface $request, ResponseInterface $response)
    {
        $em = $this->getEntityManager();
        $query = $request->getQueryParams();

        if (!$query['username'] || !$query['verifier']) {
            throw new UnprocessableEntityException();
        }

        $user = new User($query['username'], new BigInteger($query['verifier'], 16));
        $em->persist($user);
        $em->flush();

        return $this->jsonResponse($response, [
            'username' => $user->getUsername()
        ]);
    }

    /**
     * @param ServerRequestInterface $request
     * @param ResponseInterface      $response
     *
     * @return ResponseInterface
     */
    public function deleteAction(ServerRequestInterface $request, ResponseInterface $response)
    {
        $em = $this->getEntityManager();
        $user = $this->findUser($request);
        $em->remove($user);
        $em->flush();

        return $this->jsonResponse($response, [
            'username' => $user->getUsername()
        ]);
    }

    /**
     * @param ServerRequestInterface $request
     * @param ResponseInterface      $response
     *
     * @return ResponseInterface
     */
    public function patchAction(ServerRequestInterface $request, ResponseInterface $response)
    {
        $query = $request->getQueryParams();
        $user = $this->findUser($request);

        if (array_key_exists('verifier', $query)) {
            $user->setVerifier(new BigInteger($query['verifier'], 16));
        }

        $this->getEntityManager()->flush();

        return $this->jsonResponse($response, [
            'username' => $user->getUsername()
        ]);
    }

    /**
     * @param ServerRequestInterface $request
     *
     * @return User
     * @throes NotFoundException
     */
    private function findUser(ServerRequestInterface $request)
    {
        $user = $this->getEntityManager()->find(User::class, $request->getAttribute('username'));

        if ($user instanceof User) {
            return $user;
        }

        throw new NotFoundException();
    }
}