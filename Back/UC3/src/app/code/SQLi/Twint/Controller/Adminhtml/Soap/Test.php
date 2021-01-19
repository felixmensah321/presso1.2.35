<?php
/**
 * SQLi_Twint extension.
 *
 * @category   SQLi
 * @package    SQLi_Twint
 * @author     SQLi Dev Team
 * @copyright  Copyright (c) SQLI (http://www.sqli.com/)
 */

namespace SQLi\Twint\Controller\Adminhtml\Soap;

use Magento\Backend\App\Action;
use Magento\Framework\App\ResponseInterface;
use Magento\Framework\Xml\Parser;
use SQLi\Twint\Model\Soap\Client;

/**
 * Class Test
 * @package SQLi\Twint\Controller\Adminhtml\Soap
 */
class Test extends \Magento\Backend\App\Action
{

    const LOCAL_WSDL = 'TWINTMerchantService_v2_1.wsdl';
    const SOAP_HEADERS_ELEM = 'RequestHeaderElement';

    /**
     * @var \Magento\Framework\Controller\Result\JsonFactory
     */
    protected $resultJsonFactory;
    /**
     * @var \Magento\Framework\Filesystem\DirectoryList
     */
    protected $dir;
    /**
     * @var Parser
     */
    protected $xmlParser;
    /**
     * @var Client
     */
    protected $client;

    /**
     * Test constructor.
     * @param Action\Context $context
     * @param \Magento\Framework\Filesystem\DirectoryList $dir
     * @param \Magento\Framework\Controller\Result\JsonFactory $resultJsonFactory
     * @param Client $client
     * @param Parser $xmlParser
     */
    public function __construct(
        Action\Context $context,
        \Magento\Framework\Filesystem\DirectoryList $dir,
        \Magento\Framework\Controller\Result\JsonFactory $resultJsonFactory,
        Client $client,
        Parser $xmlParser
    ) {
        parent::__construct($context);
        $this->resultJsonFactory = $resultJsonFactory;
        $this->dir = $dir;
        $this->xmlParser = $xmlParser;
        $this->client = $client;
    }

    /**
     * Send a test request with SOAP
     * @return ResponseInterface|\Magento\Framework\Controller\ResultInterface|void
     */
    public function execute()
    {
        $resultJson = $this->resultJsonFactory->create();

        $merchantUuid = $this->getRequest()->getParam('merchant_uuid');
        $cashRegisterId = $this->getRequest()->getParam('cash_register_id');

        try {
            /** @var \Zend\Soap\Client $client */
            $client = $this->client->getSoapClient();

            $request = [
                'MerchantInformation' => [
                    'MerchantUuid' => $merchantUuid,
                    'CashRegisterId' => $cashRegisterId
                ]];

            $header = $this->client->createSoapHeader();
            $client->addSoapInputHeader($header);

            $response = $client->CheckSystemStatus($request);

            $resultJson->setData(['response' => $response]);
        } catch (\Exception $e) {
            $resultJson->setData([
                'error' => $e->getMessage(),
                'request' => $client->getLastRequest(),
                'response' => $client->getLastResponse()
            ]);
        }


        return $resultJson;
    }


}
