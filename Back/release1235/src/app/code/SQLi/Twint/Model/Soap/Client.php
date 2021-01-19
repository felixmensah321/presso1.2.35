<?php
/**
 * SQLi_Twint extension.
 *
 * @category   SQLi
 * @package    SQLi_Twint
 * @author     SQLi Dev Team
 * @copyright  Copyright (c) SQLI (http://www.sqli.com/)
 */
namespace SQLi\Twint\Model\Soap;

use Magento\Framework\Xml\Parser;
use SQLi\Twint\Logger\TwintLogger;
use SQLi\Twint\Model\Config;

/**
 * Class Client.
 *
 * @package SQLi\Twint\Model\Soap
 */
class Client
{
    /**
     * Header namespace.
     */
    const HEADER_NAMESPACE = 'http://service.twint.ch/header/types/v2';

    /**
     * Client software name used in SOAP header.
     */
    const CLIENT_SOFTWARE_NAME = 'sqli';

    /**
     * Client software version used in SOAP header.
     */
    const CLIENT_SOFTWARE_VERSION = 1;

    /**
     * @var Config
     */
    protected $config;

    /**
     * @var TwintLogger
     */
    protected $logger;

    /**
     * @var Parser
     */
    protected $xmlParser;

    /**
     * @var \Magento\Framework\Filesystem\DirectoryList
     */
    protected $dir;

    /**
     * Client constructor.
     *
     * @param Config $config
     * @param TwintLogger $logger
     * @param \Magento\Framework\Filesystem\DirectoryList $dir
     * @param Parser $xmlParser
     */
    public function __construct(
        Config $config,
        TwintLogger $logger,
        \Magento\Framework\Filesystem\DirectoryList $dir,
        Parser $xmlParser
    ) {
        $this->config = $config;
        $this->logger = $logger;
        $this->xmlParser = $xmlParser;
        $this->dir = $dir;
    }

    /**
     * Create the SOAP client.
     *
     * @return \Zend\Soap\Client|bool
     *
     */
    public function getSoapClient()
    {
        try {
            $rootDir = $this->dir->getRoot();
            $localWsdl = $rootDir.DIRECTORY_SEPARATOR."TWINTMerchantService_v2_1.wsdl";
            $opt = [
                'cache_wsdl' => WSDL_CACHE_NONE,
                'soap_version' => SOAP_1_1
            ];

            $context = [];

            // Bypass cert in mock mode
            if (!$this->config->getMockMode()) {
                $opt['local_cert'] = $this->config->getHttpsCertificateUrl();
                $opt['passphrase'] = $this->config->getHttpsCertificatePassphrase();
            }

            if ($this->config->getWsdlUseFallBack() == 0) {
                $opt['location'] = $this->config->getWsdlUrl()."?wsdl";
                $opt['uri'] = $this->config->getWsdlUrl();
            }

            if (!empty($context)) {
                $opt['stream_context'] = $context;
            }

            return new \Zend\Soap\Client($localWsdl, $opt);

        } catch (\Exception $e) {
            $this->logger->debug('Error during the soap client creation');
            $this->logger->debug($e->getMessage());

            return false;
        }
    }

    /**
     * Return SOAP Header related to Twint Nespresso project.
     *
     * @return \SoapHeader
     */
    public function createSoapHeader()
    {
        $headerObj = new \StdClass();
        $headerObj->MessageId = $this->generateRandomMessageId();
        $headerObj->ClientSoftwareName = self::CLIENT_SOFTWARE_NAME;
        $headerObj->ClientSoftwareVersion = self::CLIENT_SOFTWARE_VERSION;

        return new \SoapHeader(
            self::HEADER_NAMESPACE,
            'RequestHeaderElement',
            $headerObj,
            true
        );
    }

    /**
     * Parse response given by webservice.
     *
     * @param string $response
     *
     * @return array
     *
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function parseResponse($response)
    {
        $this->xmlParser->loadXML($response);
        return $this->xmlParser->xmlToArray();
    }

    /**
     * Generate random messageId by specific pattern.
     *
     * @return string
     */
    public function generateRandomMessageId()
    {
        $length = 32;
        $validChars = 'abcdefABCDEF0123456789';

        $randomString = '';

        $numberValidChars = strlen($validChars);

        for ($i = 0; $i < $length; $i++) {
            $randomPick = mt_rand(1, $numberValidChars);
            $randomChar = $validChars[$randomPick - 1];
            $randomString .= $randomChar;
        }

        return $randomString;
    }
}

