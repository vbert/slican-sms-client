<?php
declare(strict_types=1);

namespace Vbert\SmsClient;

use Vbert\SmsClient\Contracts\Client;
use Vbert\SmsClient\Contracts\Adapter;


class SmsClient implements Client {

    /**
     * Adapter to use.
     *
     * @var Adapter
     */
    private $adapter;


    /**
     * Constructor.
     *
     * @param Adapter $adapter
     * 
     * @return void
     */
    public function __construct(Adapter $adapter) {
        $this->adapter = $adapter;
    }


    /**
     * Get the adapter name.
     *
     * @return string
     */
    public function getAdapter(): string {
        return $this->adapter->getAdapter();
    }


    /**
     * Send the message.
     *
     * @param array $message
     *
     * @return boolean
     */
    public function send(array $message): bool {
        return $this->adapter->sendRequest($message);
    }
}
