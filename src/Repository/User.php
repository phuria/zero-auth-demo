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

use Phuria\ZeroAuthDemo\Model;

/**
 * @author Beniamin Jonatan Šimko <spam@simko.it>
 */
class User extends AbstractPDORepository implements UserInterface
{
    /**
     * @inheritdoc
     */
    public function getTableName()
    {
        return 'user';
    }

    /**
     * @inheritdoc
     */
    public function getModelClass()
    {
        return Model\User::class;
    }
}