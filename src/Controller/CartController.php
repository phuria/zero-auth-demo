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
use Exceptions\Http\Client\NotFoundException;
use Phuria\ZeroAuthDemo\App;
use Phuria\ZeroAuthDemo\Entity\Cart;
use Phuria\ZeroAuthDemo\Entity\Product;
use Phuria\ZeroAuthDemo\Entity\ProductInCart;
use Phuria\ZeroAuthDemo\Exception\CartLimitException;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

/**
 * @author Beniamin Jonatan Šimko <spam@simko.it>
 */
class CartController extends AbstractController
{
    /**
     * @inheritdoc
     */
    public function loadRouting(App $app)
    {
        $app->getWrappedApp()->post('/cart/', [$this, 'postAction']);
        $app->getWrappedApp()->get('/cart/{id}/', [$this, 'getAction']);
        $app->getWrappedApp()->post('/cart/{id}/product/{product}', [$this, 'postProductAction']);
        $app->getWrappedApp()->delete('/cart/{id}/product/{productInCart}', [$this, 'deleteProductAction']);
    }

    /**
     * @param ServerRequestInterface $request
     * @param ResponseInterface      $response
     *
     * @return ResponseInterface
     */
    public function postAction(ServerRequestInterface $request, ResponseInterface $response)
    {
        $session = $this->getSession($request);
        $user = $session ? $session->getUser() : '';
        $cart = new Cart($user);

        $em = $this->getEntityManager();
        $em->persist($cart);
        $em->flush();

        return $this->jsonResponse($response, [
            'id' => $cart->getId()
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
        $cart = $this->findCart($request);

        $products = array_map(function (ProductInCart $productInCart) {
           return [
               'id' => $productInCart->getId(),
               'product' => [
                   'id'   => $productInCart->getProduct()->getId(),
                   'uri'  => "/product/{$productInCart->getProduct()->getId()}/"
               ]
           ];
        }, $cart->getProducts()->toArray());

        $wallet = array_reduce($cart->getProducts()->toArray(), function ($wallet, ProductInCart $productInCart) {
            $price = $productInCart->getProduct()->getPrice();
            $wallet[$price->getCurrency()] += $price->getAmount();

            return $wallet;
        }, []);

        return $this->jsonResponse($response, [
            'id'         => $cart->getId(),
            'createdBy'  => $cart->getCreatedBy()->getUsername(),
            'productsIn' => $products,
            'wallet'     => $wallet
        ]);
    }

    /**
     * @param ServerRequestInterface $request
     * @param ResponseInterface      $response
     *
     * @return ResponseInterface
     */
    public function postProductAction(ServerRequestInterface $request, ResponseInterface $response)
    {
        $em = $this->getEntityManager();
        $cart = $this->findCart($request);

        if (3 === $cart->getProducts()->count()) {
            throw new CartLimitException();
        }

        /** @var Product $product */
        $product = $em->find(Product::class, $request->getAttribute('product'));

        if (null === $product) {
            throw new NotFoundException();
        }

        $productInCart = new ProductInCart($product, $cart);
        $em->persist($productInCart);
        $em->flush();

        return $this->jsonResponse($response, [
            'id' => $productInCart->getId()
        ]);
    }

    /**
     * @param ServerRequestInterface $request
     * @param ResponseInterface      $response
     *
     * @return ResponseInterface
     */
    public function deleteProductAction(ServerRequestInterface $request, ResponseInterface $response)
    {
        $em = $this->getEntityManager();
        $cart = $this->findCart($request);

        /** @var ProductInCart $productInCart */
        $productInCart = $em->find(ProductInCart::class, $request->getAttribute('productInCart'));

        if (null === $productInCart) {
            throw new NotFoundException();
        }

        if ($cart !== $productInCart->getCart()) {
            throw new BadRequestException();
        }

        $em->remove($productInCart);
        $em->flush();

        return $this->jsonResponse($response, [
            'id' => $productInCart->getId()
        ]);
    }

    /**
     * @param ServerRequestInterface $request
     *
     * @return Cart
     * @throws NotFoundException
     */
    private function findCart(ServerRequestInterface $request)
    {
        $cart = $this->getEntityManager()->find(Cart::class, $request->getAttribute('id'));

        if ($cart instanceof Cart) {
            return $cart;
        }

        throw new NotFoundException();
    }
}