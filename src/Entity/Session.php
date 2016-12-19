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
use Phuria\ZeroAuth\Protocol\KeyPair;

/**
 * @author Beniamin Jonatan Šimko <spam@simko.it>
 *
 * @ORM\Entity()
 * @ORM\Table(name="session")
 */
class Session
{
    /**
     * @var string
     *
     * @ORM\Id()
     * @ORM\Column(name="id", type="string")
     */
    private $id;

    /**
     * @var User
     *
     * @ORM\ManyToOne(targetEntity=User::class)
     * @ORM\JoinColumn(name="user_id", nullable=false, referencedColumnName="username")
     */
    private $user;

    /**
     * @var BigInteger
     *
     * @ORM\Column(name="client_public_key", type="BigInteger", nullable=false)
     */
    private $clientPublicKey;

    /**
     * @var BigInteger
     *
     * @ORM\Column(name="server_public_key", type="BigInteger", nullable=false)
     */
    private $serverPublicKey;

    /**
     * @var BigInteger
     *
     * @ORM\Column(name="server_private_key", type="BigInteger", nullable=false)
     */
    private $serverPrivateKey;

    /**
     * @var BigInteger
     *
     * @ORM\Column(name="client_proof", type="BigInteger", nullable=true)
     */
    private $clientProof;

    /**
     * @var BigInteger
     *
     * @ORM\Column(name="server_proof", type="BigInteger", nullable=true)
     */
    private $serverProof;

    /**
     * @param User       $user
     * @param BigInteger $clientPublicKey
     * @param KeyPair    $serverKeyPair
     */
    public function __construct(User $user, BigInteger $clientPublicKey, KeyPair $serverKeyPair)
    {
        $this->id = md5($user->getUsername() . $clientPublicKey->toHex());
        $this->user = $user;
        $this->clientPublicKey = $clientPublicKey;
        $this->serverPublicKey = $serverKeyPair->getPublicKey();
        $this->serverPrivateKey = $serverKeyPair->getPrivateKey();
    }

    /**
     * @return string
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @return User
     */
    public function getUser()
    {
        return $this->user;
    }

    /**
     * @return BigInteger
     */
    public function getClientProof()
    {
        return $this->clientProof;
    }

    /**
     * @param BigInteger $clientProof
     *
     * @return Session
     */
    public function setClientProof($clientProof)
    {
        $this->clientProof = $clientProof;

        return $this;
    }

    /**
     * @return BigInteger
     */
    public function getServerProof()
    {
        return $this->serverProof;
    }

    /**
     * @param BigInteger $serverProof
     *
     * @return Session
     */
    public function setServerProof($serverProof)
    {
        $this->serverProof = $serverProof;

        return $this;
    }

    /**
     * @return BigInteger
     */
    public function getClientPublicKey()
    {
        return $this->clientPublicKey;
    }

    /**
     * @return BigInteger
     */
    public function getServerPublicKey()
    {
        return $this->serverPublicKey;
    }

    /**
     * @return BigInteger
     */
    public function getServerPrivateKey()
    {
        return $this->serverPrivateKey;
    }
}