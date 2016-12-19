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

use Exceptions\Http\Client\ForbiddenException;
use Exceptions\Http\Client\NotFoundException;
use phpseclib\Math\BigInteger;
use Phuria\ZeroAuthDemo\App;
use Phuria\ZeroAuthDemo\Entity\Session;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

/**
 * @author Beniamin Jonatan Šimko <spam@simko.it>
 */
class SessionController extends AbstractController
{
    /**
     * @inheritdoc
     */
    public function loadRouting(App $app)
    {
        $app->getWrappedApp()->get('/session/{id}', [$this, 'getAction']);
        $app->getWrappedApp()->delete('/session/{id}', [$this, 'deleteAction']);
        $app->getWrappedApp()->post('/session/{id}/auth/{clientProof}', [$this, 'authAction']);
    }

    /**
     * @param ServerRequestInterface $request
     * @param ResponseInterface      $response
     *
     * @return ResponseInterface
     */
    public function getAction(ServerRequestInterface $request, ResponseInterface $response)
    {
        $session = $this->findSession($request);

        return $this->jsonResponse($response, [
            'id'              => $session->getId(),
            'user'            => $session->getUser()->getUsername(),
            'serverPublicKey' => $session->getServerPublicKey()->toHex(),
            'clientPublicKey' => $session->getClientPublicKey(),
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
        $session = $this->findSession($request);
        $em = $this->getEntityManager();
        $em->remove($session);
        $em->flush();

        return $this->jsonResponse($response, [
            'id' => $session->getId()
        ]);
    }

    /**
     * @param ServerRequestInterface $request
     * @param ResponseInterface      $response
     *
     * @return ResponseInterface
     */
    public function authAction(ServerRequestInterface $request, ResponseInterface $response)
    {
        $session = $this->findSession($request);

        $scrambling = $this->getProtocolHelper()->computeScrambling(
            $session->getClientPublicKey(),
            $session->getServerPublicKey()
        );

        $sessionKey = $this->getProtocolHelper()->computeServerSessionKey(
            $session->getClientPublicKey(),
            $session->getUser()->getVerifier(),
            $scrambling,
            $session->getServerPrivateKey()
        );

        $clientProof = new BigInteger($request->getAttribute('clientProof'), 16);

        $expectedClientProof = $this->getProtocolHelper()->computeClientProof(
            $session->getUser()->getUsername(),
            $session->getUser()->getSalt(),
            $session->getClientPublicKey(),
            $session->getServerPublicKey(),
            $sessionKey
        );

        if (false === $clientProof->equals($expectedClientProof)) {
            throw new ForbiddenException();
        }

        $serverProof = $this->getProtocolHelper()->computeServerProof(
            $session->getClientPublicKey(),
            $clientProof,
            $sessionKey
        );

        $session->setClientProof($clientProof);
        $session->setServerProof($serverProof);
        $session->setSessionKey($sessionKey);

        $this->getEntityManager()->flush();

        return $this->jsonResponse($response, [
            'id'          => $session->getId(),
            'serverProof' => $serverProof->toHex(),
            'header'      => "Authorization: Basic {$session->getId()}:{$session->getClientProof()->toHex()}"
        ]);
    }

    /**
     * @param ServerRequestInterface $request
     *
     * @return Session
     */
    private function findSession(ServerRequestInterface $request)
    {
        $user = $this->getEntityManager()->find(Session::class, $request->getAttribute('id'));

        if ($user instanceof Session) {
            return $user;
        }

        throw new NotFoundException();
    }
}