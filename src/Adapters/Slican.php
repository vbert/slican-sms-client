<?php
declare(strict_types=1);

namespace Vbert\SmsClient\Adapters;

use Socket;
use Vbert\SmsClient\Contracts\Adapter;
use Vbert\SmsClient\Exceptions\NoDataToSendMessage;
use Vbert\SmsClient\Exceptions\AdapterNotConfiguredException;
use Vbert\SmsClient\Exceptions\SocketsExtensionIsNotLoadedException;

class Slican implements Adapter {

    const MSG_EOL = "\r\n";

    /**
     * Messages sent to the PBX
     *
     * @var array
     */
    private $ctipMessagesSentToPBX = array(
        // Logging in the GSM module operation
        'aLOGI' => array(
            'command' => 'LOGI',
            'content' => 'aLOGI G001 %s'.self::MSG_EOL
        ),
        // Logging out of GSM module operation
        'aLOGO' => array(
            'command' => 'LOGO',
            'content' => 'aLOGO G001'.self::MSG_EOL
        ),
        // Sending an SMS
        'aSMSS' => array(
            'command' => 'SMSS',
            'content' => 'aSMSS G001 %s C1 N 167 %s'.self::MSG_EOL
        ),
        // Acknowledgment of message receipt
        'aSOK' => array(
            'command' => 'SOK',
            'content' => 'aSOK G001 %s'.self::MSG_EOL
        )
    );

    /**
     * Messages received from the PBX
     *
     * @var array
     */
    private $ctipMessagesReceivedFromPBX = array(
        // Confirmation / rejection of sending SMS messages
        'aSMSA' => array(
            'command' => 'SMSA',
            // Type of confirmation
            'type' => array(
                // SMS acceptance => Identifier (order number) of the sent message, 
                //                   assigned by the operator center, enabling association 
                //                   with SMSR commands. 
                'C' => 0,
                // SMS rejection => Error code returned by the operator. For example, 
                //                  the value of 70 means that the SMS has exceeded its 
                //                  expiry date. CTIP does not define these values because 
                //                  they are operator dependent.  
                'R' => 0
            )
        ),
        // Receiving an SMS
        'aSMSG' => array(
            'command' => 'SMSG',
            // Index of the report in the GSM module memory, counted from the control panel reset 
            'raportId' => 0,
            // Date and time of sending the message
            'dateTime' => 'YYYY-MM-DD HH:MM:SS'
        )
    );
    
    const RESPONSE_OK = 'OK';
    const RESPONSE_ERROR = 'ERROR';
    const RESPONSE_NA = 'NA';

    /**
     * CTIP Responses and error messages
     *
     * @var array
     */
    private $ctipResponses = array(
        self::RESPONSE_OK => 'The message was executed correctly.',
        self::RESPONSE_ERROR => 'Query or parameters have invalid syntax or value.',
        self::RESPONSE_NA => array(
            0 => 'There is no additional information on the cause of the error.',
            1 => 'No login, log in to the control panel again (in the case of TCP / IP connections disconnect the old TCP / IP connection and establish a new one).',
            2 => 'The handset is hung up.',
            3 => 'No call.',
            4 => 'The transferred number is busy or unavailable.',
            5 => 'The subscriber is called by the group.',
            6 => 'Cannot be transferred to an external number.',
            7 => 'There is no such number.',
            8 => 'No delegation authority.',
            9 => 'Service parameters not available in the control panel.',
            10 => 'No possibility to register a malicious connection.',
            11 => 'Wrong access key.',
            12 => 'No access key.',
            13 => 'No such account.',
            14 => 'The number does not have a system telephone.',
            15 => 'The number is not a subscriber.',
            16 => 'Digits cannot be dialed.',
            17 => 'Subscriber disabled or broken.',
            18 => 'The subscriber has a blocked phone.',
            19 => 'No permission for the CTI Telefon application (in the PBX configuration - the CTI field = 0).',
            20 => 'Too many attempts with a wrong key.',
            21 => 'GSM module busy, the command has not been accepted.',
            22 => 'Message content too long. (160 for the GSM alphabet, and 70 for IBM852).',
            23 => 'Translation damaged.',
            101 => 'No permission for the CTI Telefon application (in the CTI Server computer application).',
            102 => 'CTI Server application not registered.',
            103 => 'No connection between the Server CTI and the control panel.',
            106 => 'Incorrect version of the software in the control panel, preventing the operation of the CTI Server.',
            107 => 'Disconnection due to someone else\'s logging into this GSM module.'
        )
    );

    /**
     * Slican PBX host
     *
     * @var string
     */
    private $host = '';

    /**
     * Slican PBX port
     *
     * @var integer
     */
    private $port = 0;

    /**
     * Pin for sim card
     *
     * @var integer
     */
    private $pinSimCard = 0;
    
    /**
     * Endpoint.
     *
     * @var string
     */
    private $endpoint = '';

    /**
     * Socket instance
     *
     * @var Socket
     */
    private $socket;


    public function __construct(array $config=[]) {
        set_time_limit(0);

        if (!extension_loaded('sockets')) {
            throw new SocketsExtensionIsNotLoadedException();
        }

        if (!array_key_exists('host', $config) || !array_key_exists('port', $config) || !array_key_exists('pinSimCard', $config)) {
            throw new AdapterNotConfiguredException();
        }
        $this->host = $config['host'];
        $this->port = $config['port'];
        $this->pinSimCard = $config['pinSimCard'];

    }


    public function getAdapter(): string {
        return 'Slican';
    }


    public function getEndpoint(): string {
        return $this->endpoint;
    }


    public function sendRequest(array $message): bool {
        if (!array_key_exists('to', $message) || !array_key_exists('content', $message)) {
            throw new NoDataToSendMessage();
        }

        $this->ctipCreate();
        if ($this->ctipLogi() === TRUE) {
            # code...
        }

        return TRUE;
    }


    private function ctipCreate(): void {
        if (($this->socket = socket_create(AF_INET, SOCK_STREAM, SOL_TCP)) === FALSE) {
            throw new \Exception($this->getSocketError());
        }

        if (socket_connect($this->socket, $this->host, $this->port) === FALSE) {
            throw new \Exception($this->getSocketError());
        }
    }


    private function ctipLogi(): bool {
        $msg = (object) $this->ctipMessagesSentToPBX['aLOGI'];
        $content = sprintf($msg->content, $this->pinSimCard);
        if (socket_send($this->socket, $content, strlen($content), 0) === FALSE) {
            throw new \Exception($this->getSocketError());
        }
        $response = $this->getServerResponse();
        return $this->isOK($response);
    }


    private function ctipSmss(array $message): bool {
        $msg = (object) $this->ctipMessagesSentToPBX['aSMSS'];
        $content = sprintf($msg->content, $message['to'], $message['content']);
        if (socket_write($this->socket, $content, strlen($content)) === FALSE) {
            throw new \Exception($this->getSocketError());
        }

        return FALSE;
    }


    private function getServerResponse(): array {
        if (($response = socket_read($this->socket, 2048)) === FALSE) {
            throw new \Exception($this->getSocketError());
        }
        if ($response !== '') {
            return explode(' ', $response);
        }
        return array();
    }


    private function isOK(array $response): bool {
        if (substr($response[0], 1) === self::RESPONSE_OK) {
            return TRUE;
        } elseif (substr($response[0], 1) === self::RESPONSE_ERROR) {
            throw new \Exception($this->getSocketError($this->ctipResponses[self::RESPONSE_ERROR]));
        } elseif (substr($response[0], 1) === self::RESPONSE_NA) {
            if (isset($response[1]) && array_key_exists((int) $response[1], $this->ctipResponses[self::RESPONSE_NA])) {
                throw new \Exception($this->getSocketError($this->ctipResponses[self::RESPONSE_NA][(int) $response[1]]));
            } else {
                throw new \Exception($this->getSocketError('No additional information on the cause of the error occurred.'));
            }
        }
        return FALSE;
    }


    private function getSocketError(string $error=''): string {
        if ($error === '') {
            return '['. socket_last_error() .'] '. socket_strerror(socket_last_error());
        } else {
            return $error;
        }
    }
}
