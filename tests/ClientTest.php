<?php

namespace Vbert\SmsClient\Tests;

use Vbert\SmsClient\SmsClient;
use Vbert\SmsClient\Adapters\Slican;
use PHPUnit\Framework\TestCase;


class ClientTest extends TestCase {

    public function testCanGetNameAdapterFromAdapter() {
        $adapter = new Slican();
        $name = $adapter->getAdapter();

        $this->assertEquals('Slican', $name);
    }


    public function testCanGetNameAdapterFromSmsClient() {
        $adapter = new Slican();
        $client = new SmsClient($adapter);
        $name = $client->getAdapter();

        $this->assertEquals('Slican', $name);
    }
}
