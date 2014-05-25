<?php
/**
 * Send mail using the Amazon SES service
 * PHP 5
 * Licensed under The MIT License
 * Redistributions of files must retain the above copyright notice.
 *
 * @copyright     Copyright 2012-2012, Jelle Henkens.
 * @package       CakeEmailTransports.Network.Email
 * @license       MIT License (http://www.opensource.org/licenses/mit-license.php)
 */

App::uses('AbstractTransport', 'Network/Email');
use Aws\Common\Aws;
use Aws\Ses\SesClient;
use Aws\Ses\Exception\SesException;
use Aws\Common\Enum\Region;

class AmazonTransport extends AbstractTransport
{

    /**
     * AmazonSES instance
     *
     * @var SesClient
     */
    protected $_amazonSes;

    /**
     * CakeEmail
     *
     * @var CakeEmail
     */
    protected $_cakeEmail;

    /**
     * Holds the data to be sent to the API
     *
     * @var array
     */
    protected $_data = array();

    /**
     * Content of email to return
     *
     * @var array
     */
    protected $_content = array();

    /**
     * Set the configuration
     *
     * @param array $config
     *
     * @return void
     */
    public function config($config = array())
    {
        $default = array(
            'apiKey' => false,
            'secure' => false,
            'tag'    => false
        );

        $this->_config = $config + $default;
    }

    /**
     * Send mail
     *
     * @param CakeEmail $email CakeEmail
     *
     * @return array
     * @throws CakeException
     */
    public function send(CakeEmail $email)
    {
        $this->_cakeEmail = $email;

        $this->_prepareData();
        $this->_amazonSend();

        return $this->_content;
    }

    /**
     * Prepares the data array.
     *
     * @return void
     */
    protected function _prepareData()
    {
        $headers = $this->_cakeEmail->getHeaders(array('from', 'sender', 'replyTo', 'readReceipt', 'returnPath', 'to', 'cc', 'subject'));

        if ($headers['Sender'] == '') {
            $headers['Sender'] = $headers['From'];
        }
        $headers = $this->_headersToString($headers);
        $message = implode("\r\n", $this->_cakeEmail->message());

        $this->_data = array();
        $destinations = [];
        $destinations += array_keys($this->_cakeEmail->to());
        $destinations += array_keys($this->_cakeEmail->cc());
        $destinations += array_keys($this->_cakeEmail->bcc());

        $this->_data = [
            'Source'       => key($this->_cakeEmail->from()),
            'Destinations' => $destinations,
            'RawMessage'   => ['Data' => base64_encode($headers . "\r\n\r\n" . $message . "\r\n\r\n\r\n.")],
        ];

        $this->_content = array('headers' => $headers, 'message' => $message);
    }

    /**
     * Posts the data to the postmark API endpoint
     *
     * @return array
     * @throws CakeException
     */
    protected function _amazonSend()
    {
        $this->_generateAmazonSes();

        /**
         * @var Guzzle\Service\Resource\Model $response
         */
        $response = $this->_amazonSes->sendRawEmail($this->_data);
        /** @noinspection PhpUndefinedMethodInspection */
        //TODO 例外処理をまだ書いていない。ここに書かれたコードは古いもの。
//        if (!$response->isOK()) {
//            /** @noinspection PhpUndefinedFieldInspection */
//            throw new CakeException((string)$response->body->Error->Message);
//        }
    }

    /**
     * Helper method to generate the Amazon API lib
     *
     * @return void
     */
    protected function _generateAmazonSes()
    {
        $this->_amazonSes = SesClient::factory(
                                     [
                                         'key'    => $this->_config['key'],
                                         'secret' => $this->_config['secret'],
                                         'region' => Region::US_EAST_1
                                     ]
        );
    }
}
