<?php
/**
 * SQLi_AddressVerification extension.
 *
 * @category   SQLi
 * @package    SQLi_AddressVerification
 * @author     SQLi Dev Team
 * @copyright  Copyright (c) SQLI (http://www.sqli.com/)
 */
namespace SQLi\AddressVerification\Controller\Index;

use Magento\Framework\App\Action\Context;
use Magento\Framework\Controller\ResultFactory;
use SQLi\AddressVerification\Helper\Data;
use SQLi\AddressVerification\Helper\TokenChecker;
use Psr\Log\LoggerInterface;

/**
 * Class Index
 * @package SQLi\AddressVerification\Controller\Index
 */
class Index extends \Magento\Framework\App\Action\Action
{
    /**
     * @var Data
     */
    protected $helperData;

    /**
     * @var TokenChecker
     */
    protected $helperTokenChecker;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * Index constructor.
     * @param Context $context
     * @param Data $helperData
     * @param TokenChecker $helperTokenChecker
     * @param LoggerInterface $logger
     */
    public function __construct(
        Context $context,
        Data $helperData,
        TokenChecker $helperTokenChecker,
        LoggerInterface $logger
    ) {
        $this->helperData = $helperData;
        $this->helperTokenChecker = $helperTokenChecker;
        $this->logger = $logger;
        parent::__construct($context);
    }

    public function execute()
    {
        $resultPage = $this->resultFactory->create(ResultFactory::TYPE_PAGE);
        $checkToken = $this->helperTokenChecker->getToken();
        if($checkToken) {
            return $resultPage;
        }

        $configData = $this->helperData->getAllConfig();
        $activatorObj = $this->helperTokenChecker->getActivatorObj($configData);

        $response = $activatorObj->handleAuthorizeCallback();
        if($response){
            $tokenArray = json_decode(json_encode($response['body']),true);
            $accessTokenExpiryTime = time() + 300;
            $this->helperData->setData(Data::ACCESS_TOKEN_FIELD_PATH,$tokenArray['access_token']);
            $this->helperData->setData(Data::TOKEN_EXPIRY_FIELD_PATH,$accessTokenExpiryTime);
            return $resultPage;
        }
    }
}
