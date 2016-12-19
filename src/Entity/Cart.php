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

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;

/**
 * @author Beniamin Jonatan Šimko <spam@simko.it>
 *
 * @ORM\Entity()
 * @ORM\Table(name="cart")
 */
class Cart
{
    /**
     * @var int
     *
     * @ORM\Id()
     * @ORM\Column(name="id", type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @var User
     *
     * @ORM\ManyToOne(targetEntity=User::class)
     * @ORM\JoinColumn(name="created_by_id", nullable=true, referencedColumnName="username")
     */
    private $createdBy;

    /**
     * @var ProductInCart[]|ArrayCollection
     *
     * @ORM\OneToMany(targetEntity=ProductInCart::class, mappedBy="cart")
     */
    private $products;

    /**
     * @param User|null $createdBy
     */
    public function __construct(User $createdBy = null)
    {
        $this->createdBy = $createdBy;
        $this->products = new ArrayCollection();
    }

    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @return ArrayCollection|ProductInCart[]
     */
    public function getProducts()
    {
        return $this->products;
    }
}