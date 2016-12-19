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

use Exceptions\Http\Client\BadRequestException;
use GuzzleHttp\ClientInterface;
use Phuria\ZeroAuth\Crypto\CryptoInterface;
use Phuria\ZeroAuthDemo\App;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

/**
 * @author Beniamin Jonatan Šimko <spam@simko.it>
 */
class EncryptedController extends AbstractController
{
    /**
     * @inheritdoc
     */
    public function loadRouting(App $app)
    {
        $app->getWrappedApp()->post('/encrypted/', [$this, 'postAction']);
    }

    /**
     * @param ServerRequestInterface $request
     * @param ResponseInterface      $response
     *
     * @return ResponseInterface
     */
    public function postAction(ServerRequestInterface $request, ResponseInterface $response)
    {
        $query = $request->getQueryParams();
        $session = $this->getSession($request, true);

        if (!$query['cipher'] || !$query['iv']) {
            //throw new BadRequestException();
        }

        if (false === $this->getCrypto()->supports($query['cipher'])) {
            //throw new BadRequestException();
        }

        $query['cipher'] = 'aes-128-cbc';
        $query['iv'] = $this->getCrypto()->generateIv($query['cipher']);

        $data = $this->getCrypto()->decrypt($query['data'], $query['cipher'], $session->getSessionKey(), $query['iv']);

        if (!$data['method'] || !$data['uri']) {
            //throw new BadRequestException();
        }

        //$internalResponse = $this->getClient()->request($data['method'], $data['uri']);

        return $this->jsonResponse($response, [
            'cipher' => $query['cipher'],
            'iv'     => $query['iv'],
            'data'   => $this->getCrypto()->encrypt(
                'test',
                $query['cipher'],
                $session->getSessionKey(),
                $query['iv']
            )
        ]);
    }

    /**
     * @return CryptoInterface
     */
    private function getCrypto()
    {
        return $this->getContainer()[CryptoInterface::class];
    }

    /**
     * @return ClientInterface
     */
    private function getClient()
    {
        return $this->getContainer()[ClientInterface::class];
    }
}