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

use Phuria\ZeroAuthDemo\Model as Model;

/**
 * @author Beniamin Jonatan Šimko <spam@simko.it>
 */
class Product extends AbstractPDORepository implements ProductInterface
{
    /**
     * @inheritdoc
     */
    public function getTableName()
    {
        return 'product';
    }

    /**
     * @inheritdoc
     */
    public function getModelClass()
    {
        return Model\Product::class;
    }

    /**
     * @param mixed $identity
     *
     * @return Model\Product
     */
    public function findOne($identity)
    {
        return parent::findOne($identity);
    }

    /**
     * @return Model\Product[]
     */
    public function findAll()
    {
        return parent::findAll();
    }
}