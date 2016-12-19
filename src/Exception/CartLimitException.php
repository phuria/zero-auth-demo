<?php

/**
 * This file is part of phuria/zero-auth-demo package.
 *
 * Copyright (c) 2016 Beniamin Jonatan Šimko
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Phuria\ZeroAuthDemo\Exception;

/**
 * @author Beniamin Jonatan Šimko <spam@simko.it>
 */
class CartLimitException extends \DomainException
{
    /**
     * @inheritdoc
     */
    public function __construct()
    {
        parent::__construct('Products in cart limit reached.', 4000);
    }
}