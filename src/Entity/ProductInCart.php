<?php

/**
 * This file is part of phuria/zero-auth-demo package.
 *
 * Copyright (c) 2016 Beniamin Jonatan Šimko
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Phuria\ZeroAuthDemo\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @author Beniamin Jonatan Šimko <spam@simko.it>
 *
 * @ORM\Entity()
 * @ORM\Table(name="product_in_cart")
 */
class ProductInCart
{
    /**
     * @var int
     *
     * @ORM\Id
     * @ORM\Column(name="id", type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @var Product
     *
     * @ORM\ManyToOne(targetEntity=Product::class)
     * @ORM\JoinColumn(name="product_id", nullable=false)
     */
    private $product;

    /**
     * @var Cart
     *
     * @ORM\ManyToOne(targetEntity=Cart::class, inversedBy="products")
     * @ORM\JoinColumn(name="cart_id", nullable=false)
     */
    private $cart;

    /**
     * @param Product $product
     * @param Cart    $cart
     */
    public function __construct(Product $product, Cart $cart)
    {
        $this->product = $product;
        $this->cart = $cart;
    }

    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @return Product
     */
    public function getProduct()
    {
        return $this->product;
    }

    /**
     * @return Cart
     */
    public function getCart()
    {
        return $this->cart;
    }
}