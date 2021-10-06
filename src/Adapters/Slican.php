<?php

namespace Vbert\SmsClient\Adapters;

use Vbert\SmsClient\Contracts\Adapter;


class Slican implements Adapter {

    /**
     * Endpoint.
     *
     * @var string
     */
    protected $endpoint = '';


    public function __construct() {
        
    }


    public function getAdapter(): string {
        return 'Slican';
    }


    public function getEndpoint(): string {
        return $this->endpoint;
    }


    public function sendRequest(array $message): bool {
        return true;
    }
}