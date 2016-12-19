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

use phpseclib\Math\BigInteger;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * @author Beniamin Jonatan Šimko <spam@simko.it>
 */
class AuthCommand extends AbstractCommand
{
    /**
     * @inheritdoc
     */
    protected function configure()
    {
        $this
            ->setName('auth')
            ->addArgument('username', InputArgument::REQUIRED)
            ->addArgument('password', InputArgument::REQUIRED);
    }

    /**
     * @inheritdoc
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $client = $this->getClient();
        $protocolHelper = $this->getProtocolHelper();
        $username = $input->getArgument('username');
        $password = $input->getArgument('password');

        $clientKeyPair = $protocolHelper->generateClientKeyPair();

        $output->writeln("Public key generated: <info>{$clientKeyPair->getPublicKey()->toHex()}</info>");
        $output->writeln("Private key generated: <info>{$clientKeyPair->getPrivateKey()->toHex()}</info>");

        $output->writeln('<question>Sending public key to server.</question>');

        $response = $client->request('POST', "/user/{$username}/session/{$clientKeyPair->getPublicKey()->toHex()}/");
        $exchangeData = json_decode($response->getBody(), true);

        $output->writeln("Server sent following salt: <info>{$exchangeData['salt']}</info>");
        $output->writeln("Server sent following verifier: <info>{$exchangeData['verifier']}</info>");
        $output->writeln("Server creates following session: <info>{$exchangeData['session']['id']}</info>");

        $salt = new BigInteger($exchangeData['salt'], 16);
        $credentialHash = $protocolHelper->computeCredentialsHash($salt, $username, $password);
        $verifier = $protocolHelper->computeVerifier($credentialHash);

        $output->writeln("Client computes following verifier: <info>{$exchangeData['verifier']}</info>");

        if ($verifier->equals(new BigInteger($exchangeData['verifier'], 16))) {
            $output->writeln('<question>Server and client have same verifiers.</question>');
        } else {
            return;
        }

        $output->writeln("Server sent following public key: <info>{$exchangeData['serverPublicKey']}</info>");

        $serverPublicKey = new BigInteger($exchangeData['serverPublicKey'], 16);
        $scrambling = $protocolHelper->computeScrambling($clientKeyPair->getPublicKey(), $serverPublicKey);

        $output->writeln("Computed server and client key scrambling: <info>{$scrambling->toHex()}</info>");

        $sessionKey = $protocolHelper->computeClientSessionKey(
            $credentialHash, $serverPublicKey, $clientKeyPair->getPrivateKey(), $scrambling
        );

        $output->writeln("Computed session key: <info>{$sessionKey->toHex()}</info>");

        $clientProof = $protocolHelper->computeClientProof(
            $username, $salt, $clientKeyPair->getPublicKey(), $serverPublicKey, $sessionKey
        );

        $output->writeln("Computed client proof: <info>{$clientProof->toHex()}</info>");
        $output->writeln("<question>Sending proof to server.</question>");

        $response = $client->request('POST', $exchangeData['session']['uri'] . "auth/{$clientProof->toHex()}/");
        $exchangeData = json_decode($response->getBody(), true);

        $output->writeln("Server sent following proof: <info>{$exchangeData['serverProof']}</info>");
        $output->writeln("<question>Client has been authenticated by server.</question>");

        $serverProof = $protocolHelper->computeServerProof(
            $clientKeyPair->getPublicKey(),
            $clientProof,
            $sessionKey
        );

        $output->writeln("Computed server proof: <info>{$serverProof->toHex()}</info>");

        if ($serverProof->equals(new BigInteger($exchangeData['serverProof'], 16))) {
            $output->writeln('<question>Server proof is valid. Server has been authenticated by client.</question>');
        } else {
            return;
        }

        $output->writeln('Session is fully authorized.');
        $output->writeln("You can now use following header: <info>{$exchangeData['header']}</info>");
    }
}