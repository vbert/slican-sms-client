<?php
declare(strict_types=1);

namespace Vbert\SmsClient\Adapters;

use Socket;
use Vbert\SmsClient\Contracts\Adapter;
use Vbert\SmsClient\Exceptions\AdapterNotConfiguredException;
use Vbert\SmsClient\Exceptions\NoDataToSendMessage;
use Vbert\SmsClient\Exceptions\SocketsExtensionIsNotLoadedException;

class Slican implements Adapter {

    const MSG_EOL = "\r\n";

    /**
     * CTIP Signal Messages
     *
     * @var array
     */
    private $ctipSignalMessages = array(
        'logi' => array(
            'command' => 'LOGI',
            'message' => 'aLOGI G001 %s'.self::MSG_EOL
        ),
        'logo' => array(
            'command' => 'LOGO',
            'message' => 'aLOGO G001'.self::MSG_EOL
        ),
        'smss' => array(
            'command' => 'SMSS',
            'message' => 'aSMSS G001 %s C1 N 167 %s'.self::MSG_EOL
        ),
        'sok' => array(
            'command' => 'SOK',
            'message' => 'aSOK G001 %s'.self::MSG_EOL
        )
    );
    
    /**
     * CTIP Responses and error messages
     *
     * @var array
     */
    private $ctipResponses = array(
        'OK' => 'The message was executed correctly.',
        'ERROR' => 'Query or parameters have invalid syntax or value.',
        'NA' => array(
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
     * @var \Socket
     */
    private Socket $socket;


    public function __construct(array $config=[]) {
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

        try {
            $this->socket = socket_create(AF_INET, SOCK_STREAM, SOL_TCP);
        } catch(\Exception $ex) {
            echo $ex->getMessage();
        }
        return true;
    }


    private function ctipLogIn() {

    }
}