<?php
/**
 * SQLi_AddressVerification extension.
 *
 * @category   SQLi
 * @package    SQLi_AddressVerification
 * @author     SQLi Dev Team
 * @copyright  Copyright (c) SQLI (http://www.sqli.com/)
 */
namespace SQLi\AddressVerification\Model\Api;

use GuzzleHttp\Exception\GuzzleException;
use Magento\Framework\Controller\Result\JsonFactory;
use Magento\Framework\Exception\AuthorizationException;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use SQLi\AddressVerification\Api\AddressVerificationInterface;
use SQLi\AddressVerification\Helper\TokenChecker;
use Magento\Framework\App\Response\Http as ResponseHttp;
use Magento\Framework\Serialize\Serializer\Json;

/**
 * Class AddressVerification
 * @package SQLi\AddressVerification\Model\Api
 */
class AddressVerification implements AddressVerificationInterface
{
    /**
     * @var TokenChecker
     */
    protected $helperTokenChecker;

    /**
     * @var ResponseHttp
     */
    protected $response;

    /** @var JsonFactory */
    protected $jsonResultFactory;

    /**
     * @var Json
     */
    protected $json;

    /**
     * AddressVerification constructor.
     * @param TokenChecker $helperTokenChecker
     * @param ResponseHttp $response
     * @param JsonFactory $jsonResultFactory
     * @param Json $json
     */
    public function __construct(
        TokenChecker $helperTokenChecker,
        ResponseHttp $response,
        JsonFactory $jsonResultFactory,
        Json $json
    ) {
        $this->helperTokenChecker = $helperTokenChecker;
        $this->response = $response;
        $this->jsonResultFactory = $jsonResultFactory;
        $this->json = $json;
    }

   public function getAccessToken()
   {
       $checkToken = $this->helperTokenChecker->getToken();
       if($checkToken == 401 || $checkToken == 403) {
           //throws a 401 04 403 Unauthorized
           throw new AuthorizationException(__('status:0, error_code:TOKEN_ERROR'));
       }
       if(!$checkToken) {
           //throws a 404 not found if API is unreacheable
           throw new NoSuchEntityException(__('status:0, error_code:API_UNREACHABLE'));
       }
       if(is_array($checkToken)) {
           $response = [
               'success' => 1,
               'accessToken' => $checkToken['accessToken']
           ];
           return json_encode($response);
       }
   }
}
