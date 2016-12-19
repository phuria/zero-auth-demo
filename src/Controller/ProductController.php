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
use Phuria\ZeroAuthDemo\App;
use Phuria\ZeroAuthDemo\Embeddable\Price;
use Phuria\ZeroAuthDemo\Entity\Product;
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
        $app->getWrappedApp()->post('/product/', [$this, 'postAction']);
        $app->getWrappedApp()->get('/product/{id}/', [$this, 'getAction']);
        $app->getWrappedApp()->delete('/product/{id}/', [$this, 'deleteAction']);
        $app->getWrappedApp()->patch('/product/{id}/', [$this, 'patchAction']);
    }

    /**
     * @param ServerRequestInterface $request
     * @param ResponseInterface      $response
     *
     * @return ResponseInterface
     */
    public function listAction(ServerRequestInterface $request, ResponseInterface $response)
    {
        $query = $request->getQueryParams();

        $qb = $this->getEntityManager()->createQueryBuilder();
        $qb->addSelect('p');
        $qb->from(Product::class, 'p');
        $qb->setMaxResults(3);

        if ($query['after']) {
            $qb->andWhere('p.id > :after');
            $qb->setParameter('after', $query['after']);
        }

        if ($query['before']) {
            $qb->andWhere('p.id < :before');
            $qb->setParameter('before', $query['before']);
        }

        $products = array_map(function (Product $product) {
            return [
                'id'    => $product->getId(),
                'title' => $product->getTitle(),
                'price' => [
                    'amount'   => $product->getPrice()->getAmount(),
                    'currency' => $product->getPrice()->getCurrency()
                ]
            ];
        }, $qb->getQuery()->getResult());

        $after = end($products)['id'];
        $before = reset($products)['id'];

        return $this->jsonResponse($response, [
            'list'   => $products,
            'cursor' => [
                'after'   => $after,
                'before'  => $before,
                'prevUri' => "/product/?before={$before}",
                'nextUri' => "/product/?after={$after}"
            ]
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

        if (!$query['title']) {
            throw new UnprocessableEntityException();
        }

        $price = new Price();

        if (array_key_exists('priceAmount', $query)) {
            $price->setAmount($query['priceAmount']);
        }

        if (array_key_exists('priceCurrency', $query)) {
            $price->setCurrency($query['priceCurrency']);
        }

        $product = new Product($query['title'], $price);
        $em->persist($product);
        $em->flush();

        return $this->jsonResponse($response, [
            'id' => $product->getId()
        ]);
    }

    /**
     * @param ServerRequestInterface $request
     * @param ResponseInterface      $response
     *
     * @return ResponseInterface
     */
    public function getAction(ServerRequestInterface $request, ResponseInterface $response)
    {
        $product = $this->findProduct($request);

        return $this->jsonResponse($response, [
            'id'    => $product->getId(),
            'title' => $product->getTitle(),
            'price' => [
                'amount'   => $product->getPrice()->getAmount(),
                'currency' => $product->getPrice()->getCurrency()
            ]
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
        $product = $this->findProduct($request);
        $em->remove($product);
        $em->flush();

        return $this->jsonResponse($response, [
            'id' => $product->getId()
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
        $product = $this->findProduct($request);
        $query = $request->getQueryParams();

        if (array_key_exists('title', $query)) {
            $product->setTitle($query['title']);
        }

        if (array_key_exists('priceAmount', $query)) {
            $product->getPrice()->setAmount($query['priceAmount']);
        }

        if (array_key_exists('priceCurrency', $query)) {
            $product->getPrice()->setCurrency($query['priceCurrency']);
        }

        $this->getEntityManager()->flush();

        return $this->jsonResponse($response, [
            'id' => $product->getId()
        ]);
    }

    /**
     * @param ServerRequestInterface $request
     *
     * @return Product
     * @throes NotFoundException
     */
    private function findProduct(ServerRequestInterface $request)
    {
        $product = $this->getEntityManager()->find(Product::class, $request->getAttribute('id'));

        if ($product instanceof Product) {
            return $product;
        }

        throw new NotFoundException();
    }
}