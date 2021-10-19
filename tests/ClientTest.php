<?php
declare(strict_types=1);

namespace Vbert\SmsClient\Tests;

use Vbert\SmsClient\SmsClient;
use Vbert\SmsClient\Adapters\Slican;
use PHPUnit\Framework\TestCase;


class ClientTest extends TestCase {

    private $config = array(
        'host' => '94.254.244.164',
        'port' => 5524,
        'pinSimCard' => 1941
    );

    public function testCanGetNameAdapterFromAdapter() {
        $adapter = new Slican($this->config);
        $name = $adapter->getAdapter();

        $this->assertEquals('Slican', $name);
    }


    public function testCanGetNameAdapterFromSmsClient() {
        $adapter = new Slican($this->config);
        $client = new SmsClient($adapter);
        $name = $client->getAdapter();

        $this->assertEquals('Slican', $name);
    }
}
