<?php

namespace Vbert\SmsClient\Contracts;


/**
 * SMS Client interface.
 */
interface Client {


    /**
     * Get the adapter name.
     *
     * @return string
     */
    public function getAdapter(): string;


    /**
     * Send the message.
     *
     * @param array $message
     *
     * @return boolean
     */
    public function send(array $message): bool;
}