<?php

/**
 * This file is part of phuria/zero-auth-demo package.
 *
 * Copyright (c) 2016 Beniamin Jonatan Šimko
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Phuria\ZeroAuthDemo\Repository;

/**
 * @author Beniamin Jonatan Šimko <spam@simko.it>
 */
abstract class AbstractPDORepository implements RepositoryInterface
{
    /**
     * @var \PDO
     */
    private $pdo;

    /**
     * @param \PDO $pdo
     */
    public function __construct(\PDO $pdo)
    {
        $this->pdo = $pdo;
    }

    /**
     * @return \PDO
     */
    public function getPdo()
    {
        return $this->pdo;
    }

    /**
     * @return string
     */
    abstract public function getTableName();

    /**
     * @return string
     */
    abstract public function getModelClass();

    /**
     * @inheritdoc
     */
    public function findOne($identity)
    {
        $stmt = $this->getPdo()->prepare("SELECT * FROM {$this->getTableName()} WHERE id = :id");
        $stmt->bindValue('id', $identity);
        $stmt->execute();
        $result = $stmt->fetchAll(\PDO::FETCH_CLASS, $this->getModelClass());

        switch (count($result)) {
            case 0:
                throw new \Exception('Not found');
            case 1:
                return $result[0];
            default:
                throw new \Exception('Too many');
        }
    }

    /**
     * @inheritdoc
     */
    public function findAll()
    {
       $stmt = $this->getPdo()->prepare("SELECT * FROM {$this->getTableName()}");
       $stmt->execute();

       return $stmt->fetchAll(\PDO::FETCH_CLASS, $this->getModelClass());
    }
}