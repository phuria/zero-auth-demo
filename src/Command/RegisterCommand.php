<?php

/**
 * This file is part of phuria/zero-auth-demo package.
 *
 * Copyright (c) 2016 Beniamin Jonatan Šimko
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Phuria\ZeroAuthDemo\Command;

use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * @author Beniamin Jonatan Šimko <spam@simko.it>
 */
class RegisterCommand extends AbstractCommand
{
    /**
     * @inheritdoc
     */
    protected function configure()
    {
        $this
            ->setName('register')
            ->addArgument('username', InputArgument::REQUIRED)
            ->addArgument('password', InputArgument::REQUIRED);
    }

    /**
     * @inheritdoc
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $protocolHelper = $this->getProtocolHelper();
        $client = $this->getClient();

        $username = $input->getArgument('username');
        $password = $input->getArgument('password');
        $salt = $protocolHelper->generateSalt();
        $hash = $protocolHelper->computeCredentialsHash($salt, $username, $password);
        $verifier = $protocolHelper->computeVerifier($hash);

        $client->request('POST', "/user/?username={$username}&salt={$salt->toHex()}&verifier={$verifier->toHex()}");

        $output->writeln("User <info>{$username}</info> successful registered.");
        $output->writeln("Salt: <info>{$salt->toHex()}</info>");
        $output->writeln("Verifier: <info>{$verifier->toHex()}</info>");
    }
}