<?php

namespace Vbert\SmsClient\Contracts;


interface Adapter {


    /**
     * Get the adapter name.
     *
     * @return string
     */
    public function getAdapter(): string;


    /**
     * Get the endpoint URL.
     *
     * @return string
     */
    public function getEndpoint(): string;


    /**
     * Send the SMS.
     *
     * @param array $message An array containing the message.
     *
     * @return boolean
     */
    public function sendRequest(array $message): bool;
}