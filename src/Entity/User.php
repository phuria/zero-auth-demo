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
use phpseclib\Math\BigInteger;

/**
 * @author Beniamin Jonatan Šimko <spam@simko.it>
 *
 * @ORM\Entity()
 * @ORM\Table(name="user")
 */
class User
{
    /**
     * @var string
     *
     * @ORM\Id
     * @ORM\Column(name="username", type="string", nullable=false)
     */
    private $username;

    /**
     * @var BigInteger
     *
     * @ORM\Column(name="verifier", type="blob", nullable=false)
     */
    private $verifier;

    /**
     * User constructor.
     *
     * @param string     $username
     * @param BigInteger $verifier
     */
    public function __construct($username, BigInteger $verifier)
    {
        $this->username = $username;
        $this->verifier = $verifier;
    }

    /**
     * @return string
     */
    public function getUsername()
    {
        return $this->username;
    }

    /**
     * @return BigInteger
     */
    public function getVerifier()
    {
        return $this->verifier;
    }
}